<?php
require_once __DIR__ . '/../config/bootstrap.php';
start_session();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    if (empty($_SESSION['user_id'])) respond(['authenticated'=>false]);
    $stmt=db()->prepare("SELECT id, first_name, email, preferred_language, voice_style FROM users WHERE id=?");
    $stmt->execute([$_SESSION['user_id']]); $u=$stmt->fetch();
    if (!$u) { session_destroy(); respond(['authenticated'=>false]); }
    respond(['authenticated'=>true,'user'=>[
        'id'=>(int)$u['id'],'firstName'=>$u['first_name'],'email'=>$u['email'],
        'preferredLanguage'=>$u['preferred_language'],'voiceStyle'=>$u['voice_style']
    ]]);
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $in=json_input(); $action=(string)($in['action']??'');
    if ($action==='register') {
        $name=trim((string)($in['firstName']??'')); $email=strtolower(trim((string)($in['email']??'')));
        $password=(string)($in['password']??'');
        $language=(string)($in['preferredLanguage']??'en-ZA'); $style=(string)($in['voiceStyle']??'warm');
        $languages=['en-ZA','af-ZA','zu-ZA','xh-ZA','st-ZA','tn-ZA','nso-ZA','ts-ZA','ve-ZA','ss-ZA','nr-ZA','sn-ZW','en-NG'];
        if ($name==='' || mb_strlen($name)>60) respond(['error'=>'Enter a first name of 1–60 characters.'],422);
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) respond(['error'=>'Enter a valid email address.'],422);
        if (strlen($password)<8) respond(['error'=>'Password must contain at least 8 characters.'],422);
        if (!in_array($language,$languages,true) || !in_array($style,['warm','clear'],true)) respond(['error'=>'Invalid personal preference.'],422);
        try {
            $pdo=db(); $pdo->beginTransaction();
            $stmt=$pdo->prepare("INSERT INTO users (first_name,email,password_hash,preferred_language,voice_style) VALUES (?,?,?,?,?)");
            $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$language,$style]);
            $uid=(int)$pdo->lastInsertId();
            $stmt=$pdo->prepare("INSERT INTO wallets (user_id,balance,asset,network) VALUES (?,250.00,'USDT','Sepolia')");
            $stmt->execute([$uid]); $pdo->commit();
            session_regenerate_id(true); $_SESSION['user_id']=$uid;
            respond(['registered'=>true]);
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            if ($e->getCode()==='23000') respond(['error'=>'An account with that email already exists. Please log in.'],409);
            error_log('Thumela registration database error: '.$e->getMessage());
            respond(['error'=>'Account creation failed. Your database may still have the old wallet tables. Import the supplied database reset SQL, then try again.'],500);
        }
    }
    if ($action==='update_preferences') {
        $uid=(int)($_SESSION['user_id']??0);
        if($uid<1) respond(['error'=>'Please log in to update preferences.'],401);
        $language=(string)($in['preferredLanguage']??'en-ZA');
        $style=(string)($in['voiceStyle']??'warm');
        if (!in_array($language,['en-ZA','af-ZA','zu-ZA','xh-ZA','st-ZA','tn-ZA','nso-ZA','ts-ZA','ve-ZA','ss-ZA','nr-ZA','sn-ZW','en-NG'],true) || !in_array($style,['warm','clear'],true)) respond(['error'=>'Invalid personal preference.'],422);
        $stmt=db()->prepare("UPDATE users SET preferred_language=?, voice_style=? WHERE id=?");
        $stmt->execute([$language,$style,$uid]);
        respond(['preferencesUpdated'=>true]);
    }
    if ($action==='login') {
        $email=strtolower(trim((string)($in['email']??''))); $password=(string)($in['password']??'');
        $stmt=db()->prepare("SELECT id,password_hash FROM users WHERE email=? LIMIT 1"); $stmt->execute([$email]); $u=$stmt->fetch();
        if (!$u || !password_verify($password,$u['password_hash'])) respond(['error'=>'Email or password is incorrect.'],401);
        session_regenerate_id(true); $_SESSION['user_id']=(int)$u['id']; respond(['authenticated'=>true]);
    }
    respond(['error'=>'Unsupported account action.'],400);
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'DELETE') {
    $_SESSION=[]; if (ini_get('session.use_cookies')) { $p=session_get_cookie_params(); setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']); }
    session_destroy(); respond(['loggedOut'=>true]);
}
require_method('GET');
