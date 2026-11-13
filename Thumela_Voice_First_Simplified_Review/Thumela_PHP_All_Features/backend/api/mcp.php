<?php
/**
 * Thumela MCP-style JSON-RPC tool endpoint.
 * Session-authenticated demo tools; this is a local HTTP JSON-RPC implementation,
 * not a hosted/production MCP deployment or an LLM connection.
 */
require_once __DIR__ . '/../config/bootstrap.php';
$userId=require_auth();
if (($_SERVER['REQUEST_METHOD']??'POST')!=='POST') { header('Allow: POST'); respond(['error'=>'POST required.'],405); }
$rpc=json_input(); $id=$rpc['id']??null; $method=$rpc['method']??'';
function rpc_result($id,$result){ respond(['jsonrpc'=>'2.0','id'=>$id,'result'=>$result]); }
function rpc_error($id,$code,$message){ respond(['jsonrpc'=>'2.0','id'=>$id,'error'=>['code'=>$code,'message'=>$message]],400); }

if ($method==='initialize') {
 rpc_result($id,['protocolVersion'=>'2025-03-26','capabilities'=>['tools'=>['listChanged'=>false]],'serverInfo'=>['name'=>'thumela-local-tools','version'=>'0.1.0']]);
}
if ($method==='notifications/initialized') { http_response_code(202); exit; }
if ($method==='ping') rpc_result($id,(object)[]);
if ($method==='tools/list') {
 rpc_result($id,['tools'=>[
  ['name'=>'get_wallet_balance','description'=>'Read the signed-in user demo wallet balance.','inputSchema'=>['type'=>'object','properties'=>[]]],
  ['name'=>'list_recipients','description'=>'List shared demo and personal saved recipients.','inputSchema'=>['type'=>'object','properties'=>[]]],
  ['name'=>'get_transaction_history','description'=>'Read the signed-in user recent transactions.','inputSchema'=>['type'=>'object','properties'=>['limit'=>['type'=>'integer','minimum'=>1,'maximum'=>25]]]],
  ['name'=>'list_savings_goals','description'=>'List the signed-in user savings goals.','inputSchema'=>['type'=>'object','properties'=>[]]],
  ['name'=>'create_savings_goal','description'=>'Create a savings goal.','inputSchema'=>['type'=>'object','required'=>['title','targetAmount'],'properties'=>['title'=>['type'=>'string'],'targetAmount'=>['type'=>'number'],'targetDate'=>['type'=>'string']]]],
  ['name'=>'contribute_savings_goal','description'=>'Move demo wallet funds into a savings goal after user approval.','inputSchema'=>['type'=>'object','required'=>['goalId','amount'],'properties'=>['goalId'=>['type'=>'integer'],'amount'=>['type'=>'number']]]],
  ['name'=>'create_payment_request','description'=>'Create a shareable demo payment request code.','inputSchema'=>['type'=>'object','properties'=>['amount'=>['type'=>'number'],'note'=>['type'=>'string']]]],
  ['name'=>'pay_bill','description'=>'Record a simulated airtime or utility payment after user approval.','inputSchema'=>['type'=>'object','required'=>['billType','accountReference','amount'],'properties'=>['billType'=>['type'=>'string'],'accountReference'=>['type'=>'string'],'amount'=>['type'=>'number']]]],
  ['name'=>'prepare_payment','description'=>'Prepare a payment quote; it does not execute a transfer. Explicit user approval is still required in the app.','inputSchema'=>['type'=>'object','required'=>['recipientId','amount'],'properties'=>['recipientId'=>['type'=>'integer'],'amount'=>['type'=>'number']]]]
 ]]);
}
if ($method==='tools/call') {
 $name=$rpc['params']['name']??''; $args=$rpc['params']['arguments']??[]; $pdo=db();
 try {
  if ($name==='get_wallet_balance') {
   $s=$pdo->prepare("SELECT balance,asset,network FROM wallets WHERE user_id=?");$s->execute([$userId]);$v=$s->fetch();
   if(!$v)throw new Exception('Wallet not found.');
   rpc_result($id,['content'=>[['type'=>'text','text'=>json_encode(['balance'=>(float)$v['balance'],'asset'=>$v['asset'],'network'=>$v['network']])]],'isError'=>false]);
  }
  if ($name==='list_recipients') {
   $s=$pdo->prepare("SELECT id,name,country,address,network FROM recipients WHERE is_active=1 AND (user_id IS NULL OR user_id=?) ORDER BY name");$s->execute([$userId]);
   rpc_result($id,['content'=>[['type'=>'text','text'=>json_encode($s->fetchAll())]],'isError'=>false]);
  }
  if ($name==='get_transaction_history') {
   $limit=max(1,min(25,(int)($args['limit']??10)));
   $s=$pdo->prepare("SELECT reference,recipient_name,amount,fee,status,created_at FROM transactions WHERE user_id=? ORDER BY id DESC LIMIT ".$limit);$s->execute([$userId]);
   rpc_result($id,['content'=>[['type'=>'text','text'=>json_encode($s->fetchAll())]],'isError'=>false]);
  }
  if ($name==='list_savings_goals') {
   $s=$pdo->prepare("SELECT id,title,target_amount,saved_amount,target_date FROM savings_goals WHERE user_id=? ORDER BY id DESC");$s->execute([$userId]);
   rpc_result($id,['content'=>[['type'=>'text','text'=>json_encode($s->fetchAll())]],'isError'=>false]);
  }
  if ($name==='create_savings_goal') {
   $title=trim((string)($args['title']??''));$amount=filter_var($args['targetAmount']??null,FILTER_VALIDATE_FLOAT);
   if($title===''||$amount===false||$amount<=0)throw new Exception('Valid title and targetAmount are required.');
   $date=$args['targetDate']??null;
   $s=$pdo->prepare("INSERT INTO savings_goals(user_id,title,target_amount,target_date) VALUES(?,?,?,?)");$s->execute([$userId,$title,$amount,$date?:null]);
   rpc_result($id,['content'=>[['type'=>'text','text'=>json_encode(['created'=>true,'goalId'=>(int)$pdo->lastInsertId()])]],'isError'=>false]);
  }
  if ($name==='contribute_savings_goal') {
   $goalId=(int)($args['goalId']??0);$amount=filter_var($args['amount']??null,FILTER_VALIDATE_FLOAT);
   if($goalId<1||$amount===false||$amount<=0)throw new Exception('A valid goal and positive amount are required.');
   $pdo->beginTransaction();
   $s=$pdo->prepare("SELECT * FROM savings_goals WHERE id=? AND user_id=? FOR UPDATE");$s->execute([$goalId,$userId]);$g=$s->fetch();
   $s=$pdo->prepare("SELECT balance FROM wallets WHERE user_id=? FOR UPDATE");$s->execute([$userId]);$w=$s->fetch();
   if(!$g||!$w)throw new Exception('Goal or wallet not found.');
   if($amount>(float)$w['balance'])throw new Exception('Insufficient available balance.');
   $s=$pdo->prepare("UPDATE wallets SET balance=balance-? WHERE user_id=?");$s->execute([$amount,$userId]);
   $s=$pdo->prepare("UPDATE savings_goals SET saved_amount=saved_amount+? WHERE id=? AND user_id=?");$s->execute([$amount,$goalId,$userId]);
   $ref='SV-'.strtoupper(bin2hex(random_bytes(4)));
   $s=$pdo->prepare("INSERT INTO transactions(user_id,reference,recipient_id,recipient_name,amount,fee,status,network) VALUES(?,?,NULL,?,?,0,'Savings contribution (demo)','Internal')");$s->execute([$userId,$ref,'Savings: '.$g['title'],$amount]);
   $pdo->commit();
   rpc_result($id,['content'=>[['type'=>'text','text'=>json_encode(['contributed'=>true,'reference'=>$ref,'goal'=>$g['title'],'amount'=>(float)$amount])]],'isError'=>false]);
  }
  if ($name==='create_payment_request') {
   $amount=$args['amount']??null;$note=trim((string)($args['note']??''));
   if($amount!==null&&(!is_numeric($amount)||(float)$amount<=0))throw new Exception('Request amount must be positive.');
   if(mb_strlen($note)>160)throw new Exception('Request note is too long.');
   $code='THM-'.strtoupper(bin2hex(random_bytes(4)));
   $s=$pdo->prepare("INSERT INTO payment_requests(user_id,request_code,amount,note,expires_at) VALUES(?,?,?,?,DATE_ADD(NOW(),INTERVAL 7 DAY))");$s->execute([$userId,$code,$amount===null?null:(float)$amount,$note]);
   rpc_result($id,['content'=>[['type'=>'text','text'=>json_encode(['created'=>true,'requestCode'=>$code,'shareText'=>"Thumela request {$code}".($amount!==null?" for ".number_format((float)$amount,2)." USDT":'').($note!==''?": {$note}":'')])]],'isError'=>false]);
  }
  if ($name==='pay_bill') {
   $type=trim((string)($args['billType']??''));$account=trim((string)($args['accountReference']??''));$amount=filter_var($args['amount']??null,FILTER_VALIDATE_FLOAT);
   if(!in_array($type,['Airtime','Electricity','Water','Internet'],true)||$account===''||mb_strlen($account)>80||$amount===false||$amount<=0)throw new Exception('Choose a valid bill type, account and amount.');
   $pdo->beginTransaction();
   $s=$pdo->prepare("SELECT balance FROM wallets WHERE user_id=? FOR UPDATE");$s->execute([$userId]);$w=$s->fetch();
   if(!$w||(float)$amount>(float)$w['balance'])throw new Exception('Insufficient available balance.');
   $s=$pdo->prepare("UPDATE wallets SET balance=balance-? WHERE user_id=?");$s->execute([$amount,$userId]);
   $ref='BL-'.strtoupper(bin2hex(random_bytes(4)));
   $s=$pdo->prepare("INSERT INTO bill_payments(user_id,bill_type,account_reference,amount,reference) VALUES(?,?,?,?,?)");$s->execute([$userId,$type,$account,$amount,$ref]);
   $s=$pdo->prepare("INSERT INTO transactions(user_id,reference,recipient_id,recipient_name,amount,fee,status,network) VALUES(?,?,NULL,?,?,0,'Paid (demo)','Demo')");$s->execute([$userId,$ref,$type.' · '.$account,$amount]);
   $pdo->commit();
   rpc_result($id,['content'=>[['type'=>'text','text'=>json_encode(['paid'=>true,'reference'=>$ref,'billType'=>$type,'amount'=>(float)$amount,'balance'=>(float)$w['balance']-(float)$amount])]],'isError'=>false]);
  }
  if ($name==='prepare_payment') {
   $rid=(int)($args['recipientId']??0);$amount=filter_var($args['amount']??null,FILTER_VALIDATE_FLOAT);
   if($rid<1||$amount===false||$amount<=0)throw new Exception('Valid recipientId and amount are required.');
   $s=$pdo->prepare("SELECT id,name,country,address,network FROM recipients WHERE id=? AND is_active=1 AND (user_id IS NULL OR user_id=?)");$s->execute([$rid,$userId]);$r=$s->fetch();
   if(!$r)throw new Exception('Recipient not found.');
   $s=$pdo->prepare("SELECT balance FROM wallets WHERE user_id=?");$s->execute([$userId]);$w=$s->fetch();
   $fee=.15;$total=round((float)$amount+$fee,2);if(!$w||$total>(float)$w['balance'])throw new Exception('Insufficient balance.');
   $token=bin2hex(random_bytes(24));$exp=(new DateTimeImmutable('+5 minutes'))->format('Y-m-d H:i:s');
   $s=$pdo->prepare("INSERT INTO payment_quotes(quote_token,user_id,recipient_id,amount,fee,total,expires_at) VALUES(?,?,?,?,?,?,?)");$s->execute([$token,$userId,$rid,$amount,$fee,$total,$exp]);
   rpc_result($id,['content'=>[['type'=>'text','text'=>json_encode(['quoteId'=>$token,'recipient'=>$r,'amount'=>(float)$amount,'fee'=>$fee,'total'=>$total,'balance'=>(float)$w['balance'],'requiresApproval'=>true,'simulation'=>true])]],'isError'=>false]);
  }
  throw new Exception('Unknown tool.');
 } catch(Throwable $e) {
  if($pdo->inTransaction()) $pdo->rollBack();
  error_log('Thumela MCP tool error: '.$e->getMessage());
  rpc_result($id,['content'=>[['type'=>'text','text'=>$e->getMessage()]],'isError'=>true]);
 }
}
rpc_error($id,-32601,'Method not found.');
