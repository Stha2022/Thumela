<?php
require_once __DIR__ . '/../config/bootstrap.php';
try {
    $userId = require_auth();
    $stmt = db()->prepare("SELECT balance, asset, network FROM wallets WHERE user_id=? LIMIT 1");
    $stmt->execute([$userId]);
    $wallet = $stmt->fetch();
    if (!$wallet) respond(['error'=>'Wallet not configured.'], 404);
    respond(['balance'=>(float)$wallet['balance'], 'asset'=>$wallet['asset'], 'network'=>$wallet['network']]);
} catch (Throwable $e) { respond(['error'=>'Could not load wallet. Check database setup.'], 500); }
