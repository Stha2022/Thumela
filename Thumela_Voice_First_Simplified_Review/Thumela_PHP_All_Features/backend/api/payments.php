<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_method('POST');
$userId = require_auth();
$input = json_input();
$quoteId = (string)($input['quoteId'] ?? '');
$approved = ($input['approved'] ?? false) === true;
if (!$approved) respond(['error'=>'Explicit user approval is required.'], 403);
if (!preg_match('/^[a-f0-9]{48}$/', $quoteId)) respond(['error'=>'Invalid or missing payment quote.'], 422);

try {
    $pdo = db();
    $pdo->beginTransaction();

    // Lock quote and wallet rows to prevent concurrent double-spend in this demo.
    $stmt = $pdo->prepare("SELECT q.*, r.name AS recipient_name, r.address, r.network FROM payment_quotes q JOIN recipients r ON r.id=q.recipient_id WHERE q.quote_token=? AND q.user_id=? AND q.used=0 AND q.expires_at > NOW() FOR UPDATE");
    $stmt->execute([$quoteId, $userId]);
    $quote = $stmt->fetch();
    if (!$quote) { $pdo->rollBack(); respond(['error'=>'Quote expired or already used. Prepare the payment again.'], 422); }

    $walletStmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id=? FOR UPDATE");
    $walletStmt->execute([$userId]); $wallet = $walletStmt->fetch();
    if (!$wallet || (float)$quote['total'] > (float)$wallet['balance']) {
        $pdo->rollBack(); respond(['error'=>'Insufficient balance.'], 422);
    }

    $newBalance = round((float)$wallet['balance'] - (float)$quote['total'], 2);
    $stmt = $pdo->prepare("UPDATE wallets SET balance=? WHERE user_id=?");
    $stmt->execute([$newBalance, $userId]);

    $reference = 'TH-' . strtoupper(bin2hex(random_bytes(4)));
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, reference, recipient_id, recipient_name, amount, fee, status, network) VALUES (?, ?, ?, ?, ?, ?, 'Confirmed (demo)', ?)");
    $stmt->execute([$userId, $reference, $quote['recipient_id'], $quote['recipient_name'], $quote['amount'], $quote['fee'], $quote['network']]);

    $stmt = $pdo->prepare("UPDATE payment_quotes SET used=1 WHERE id=?");
    $stmt->execute([$quote['id']]);
    $pdo->commit();

    respond(['amount'=>(float)$quote['amount'], 'fee'=>(float)$quote['fee'], 'to'=>$quote['recipient_name'], 'reference'=>$reference, 'status'=>'Confirmed (demo)', 'balance'=>$newBalance, 'simulation'=>true]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    respond(['error'=>'Payment could not be recorded. Check server logs.'], 500);
}
