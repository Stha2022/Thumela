<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_method('POST');
$userId = require_auth();
$input = json_input();
$amount = filter_var($input['amount'] ?? null, FILTER_VALIDATE_FLOAT);
$recipientId = filter_var($input['recipientId'] ?? null, FILTER_VALIDATE_INT);
if ($amount === false || $amount <= 0 || !$recipientId) respond(['error'=>'Enter a valid amount and recipient.'], 422);

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT id, name, country, address, network FROM recipients WHERE id=? AND is_active=1 AND (user_id IS NULL OR user_id=?)");
    $stmt->execute([$recipientId, $userId]);
    $recipient = $stmt->fetch();
    if (!$recipient) respond(['error'=>'Recipient not found or inactive.'], 404);

    $walletStmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id=?");
    $walletStmt->execute([$userId]); $wallet = $walletStmt->fetch();
    if (!$wallet) respond(['error'=>'Wallet not configured.'], 404);

    $fee = 0.15; // DEMO ONLY: replace with live fee estimator for a real chain.
    $total = round((float)$amount + $fee, 2);
    if ($total > (float)$wallet['balance']) respond(['error'=>'Insufficient demo balance.'], 422);

    $quoteId = bin2hex(random_bytes(24));
    $expires = (new DateTimeImmutable('+5 minutes'))->format('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO payment_quotes (quote_token, user_id, recipient_id, amount, fee, total, expires_at, used) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->execute([$quoteId, $userId, $recipientId, $amount, $fee, $total, $expires]);

    respond(['quoteId'=>$quoteId, 'amount'=>(float)$amount, 'fee'=>$fee, 'total'=>$total, 'balance'=>(float)$wallet['balance'], 'recipient'=>$recipient, 'simulation'=>true]);
} catch (Throwable $e) { respond(['error'=>'Could not prepare payment quote. Check database setup.'], 500); }
