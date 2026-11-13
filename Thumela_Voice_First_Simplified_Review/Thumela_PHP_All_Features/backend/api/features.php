<?php
require_once __DIR__ . '/../config/bootstrap.php';
$userId = require_auth();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
  $pdo=db();
  $s=$pdo->prepare("SELECT id,title,target_amount,saved_amount,target_date FROM savings_goals WHERE user_id=? ORDER BY id DESC"); $s->execute([$userId]);
  $goals=$s->fetchAll();
  $s=$pdo->prepare("SELECT request_code,amount,note,status,expires_at,created_at FROM payment_requests WHERE user_id=? ORDER BY id DESC LIMIT 10"); $s->execute([$userId]);
  $requests=$s->fetchAll();
  $s=$pdo->prepare("SELECT id,name,country,address,network FROM recipients WHERE is_active=1 AND (user_id IS NULL OR user_id=?) ORDER BY user_id IS NULL DESC,name"); $s->execute([$userId]);
  $recipients=$s->fetchAll();
  $s=$pdo->prepare("SELECT bill_type,account_reference,amount,reference,status,created_at FROM bill_payments WHERE user_id=? ORDER BY id DESC LIMIT 10"); $s->execute([$userId]);
  respond(['goals'=>$goals,'requests'=>$requests,'recipients'=>$recipients,'bills'=>$s->fetchAll()]);
}
if ($method !== 'POST') { header('Allow: GET, POST'); respond(['error'=>'Method not allowed.'],405); }
$in=json_input(); $action=(string)($in['action']??''); $pdo=db();

if ($action==='add_recipient') {
  $name=trim((string)($in['name']??'')); $country=trim((string)($in['country']??'')); $address=trim((string)($in['address']??'')); $network=trim((string)($in['network']??'Sepolia'));
  if ($name==='' || $country==='' || $address==='') respond(['error'=>'Name, country and wallet address are required.'],422);
  if (mb_strlen($name)>100 || mb_strlen($address)>120) respond(['error'=>'Recipient details are too long.'],422);
  $s=$pdo->prepare("INSERT INTO recipients(user_id,name,country,address,network) VALUES(?,?,?,?,?)");
  $s->execute([$userId,$name,$country,$address,$network]);
  respond(['added'=>true,'id'=>(int)$pdo->lastInsertId()]);
}
if ($action==='create_goal') {
  $title=trim((string)($in['title']??'')); $target=filter_var($in['targetAmount']??null,FILTER_VALIDATE_FLOAT);
  $date=$in['targetDate']??null;
  if ($title==='' || mb_strlen($title)>100 || $target===false || $target<=0) respond(['error'=>'Enter a goal name and a target amount greater than zero.'],422);
  if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) respond(['error'=>'Invalid target date.'],422);
  $s=$pdo->prepare("INSERT INTO savings_goals(user_id,title,target_amount,target_date) VALUES(?,?,?,?)"); $s->execute([$userId,$title,$target,$date ?: null]);
  respond(['created'=>true,'id'=>(int)$pdo->lastInsertId()]);
}
if ($action==='contribute_goal') {
  $goalId=(int)($in['goalId']??0); $amount=filter_var($in['amount']??null,FILTER_VALIDATE_FLOAT);
  if ($goalId<1 || $amount===false || $amount<=0) respond(['error'=>'Enter a valid goal and contribution amount.'],422);
  $pdo->beginTransaction();
  try {
    $s=$pdo->prepare("SELECT * FROM savings_goals WHERE id=? AND user_id=? FOR UPDATE"); $s->execute([$goalId,$userId]); $goal=$s->fetch();
    $s=$pdo->prepare("SELECT balance FROM wallets WHERE user_id=? FOR UPDATE"); $s->execute([$userId]); $wallet=$s->fetch();
    if (!$goal || !$wallet) { $pdo->rollBack(); respond(['error'=>'Savings goal or wallet not found.'],404); }
    if ($amount>(float)$wallet['balance']) { $pdo->rollBack(); respond(['error'=>'Insufficient available balance.'],422); }
    $s=$pdo->prepare("UPDATE wallets SET balance=balance-? WHERE user_id=?"); $s->execute([$amount,$userId]);
    $s=$pdo->prepare("UPDATE savings_goals SET saved_amount=saved_amount+? WHERE id=? AND user_id=?"); $s->execute([$amount,$goalId,$userId]);
    $ref='SV-'.strtoupper(bin2hex(random_bytes(4)));
    $s=$pdo->prepare("INSERT INTO transactions(user_id,reference,recipient_id,recipient_name,amount,fee,status,network) VALUES(?,?,NULL,?,?,0,'Savings contribution (demo)','Internal')");
    $s->execute([$userId,$ref,'Savings: '.$goal['title'],$amount]);
    $pdo->commit(); respond(['contributed'=>true,'reference'=>$ref]);
  } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); error_log($e->getMessage()); respond(['error'=>'Could not contribute to savings goal.'],500); }
}
if ($action==='create_request') {
  $amount=$in['amount']??null; $note=trim((string)($in['note']??''));
  if ($amount!==null && (!is_numeric($amount) || (float)$amount<=0)) respond(['error'=>'Request amount must be positive.'],422);
  if (mb_strlen($note)>160) respond(['error'=>'Request note is too long.'],422);
  $code='THM-'.strtoupper(bin2hex(random_bytes(4)));
  $s=$pdo->prepare("INSERT INTO payment_requests(user_id,request_code,amount,note,expires_at) VALUES(?,?,?,?,DATE_ADD(NOW(),INTERVAL 7 DAY))");
  $s->execute([$userId,$code,$amount===null?null:(float)$amount,$note]);
  respond(['created'=>true,'requestCode'=>$code,'shareText'=>"Thumela request {$code}".($amount!==null?" for ".number_format((float)$amount,2)." USDT":'').($note!==''?": {$note}":'')]);
}
if ($action==='pay_bill') {
  $type=trim((string)($in['billType']??'')); $account=trim((string)($in['accountReference']??'')); $amount=filter_var($in['amount']??null,FILTER_VALIDATE_FLOAT);
  $allowed=['Airtime','Electricity','Water','Internet'];
  if (!in_array($type,$allowed,true) || $account==='' || mb_strlen($account)>80 || $amount===false || $amount<=0) respond(['error'=>'Choose a bill type and enter a valid account reference and amount.'],422);
  $pdo->beginTransaction();
  try {
    $s=$pdo->prepare("SELECT balance FROM wallets WHERE user_id=? FOR UPDATE"); $s->execute([$userId]); $w=$s->fetch();
    if (!$w || $amount>(float)$w['balance']) { $pdo->rollBack(); respond(['error'=>'Insufficient available balance.'],422); }
    $s=$pdo->prepare("UPDATE wallets SET balance=balance-? WHERE user_id=?"); $s->execute([$amount,$userId]);
    $ref='BL-'.strtoupper(bin2hex(random_bytes(4)));
    $s=$pdo->prepare("INSERT INTO bill_payments(user_id,bill_type,account_reference,amount,reference) VALUES(?,?,?,?,?)"); $s->execute([$userId,$type,$account,$amount,$ref]);
    $s=$pdo->prepare("INSERT INTO transactions(user_id,reference,recipient_id,recipient_name,amount,fee,status,network) VALUES(?,?,NULL,?,?,0,'Paid (demo)','Demo')");
    $s->execute([$userId,$ref,$type.' · '.$account,$amount]);
    $pdo->commit(); respond(['paid'=>true,'reference'=>$ref,'balance'=>(float)$w['balance']-(float)$amount]);
  } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); error_log($e->getMessage()); respond(['error'=>'Could not record bill payment.'],500); }
}
respond(['error'=>'Unknown feature action.'],400);
