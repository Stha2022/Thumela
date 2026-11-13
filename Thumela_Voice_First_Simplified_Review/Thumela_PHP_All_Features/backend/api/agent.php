<?php
/**
 * Safe intent-routing starter. This is NOT an LLM agent yet.
 * It provides a deterministic backend endpoint that can later be called by
 * an LLM tool-calling layer / MCP server.
 */
require_once __DIR__ . '/../config/bootstrap.php';
require_method('POST');
$userId = require_auth();
$input = json_input();
$command = trim((string)($input['command'] ?? ''));
if ($command === '') respond(['error'=>'A command is required.'], 422);

$lower = mb_strtolower($command);
if (preg_match('/balance|how much|available funds/u', $lower)) {
    $stmt = db()->prepare("SELECT balance, asset FROM wallets WHERE user_id=?"); $stmt->execute([$userId]); $wallet = $stmt->fetch();
    respond(['intent'=>'get_wallet_balance','tool'=>'get_wallet_balance','result'=>['balance'=>(float)$wallet['balance'],'asset'=>$wallet['asset']],'message'=>'Balance retrieved.']);
}
if (preg_match('/history|transactions|recent payments|activity/u', $lower)) {
    $stmt = db()->prepare("SELECT amount, fee, recipient_name, reference, status, created_at FROM transactions WHERE user_id=? ORDER BY id DESC LIMIT 10"); $stmt->execute([$userId]); $rows = $stmt->fetchAll();
    respond(['intent'=>'get_transaction_history','tool'=>'get_transaction_history','result'=>$rows,'message'=>'Transaction history retrieved.']);
}
respond([
    'intent'=>'unknown_or_payment_request',
    'message'=>'This starter endpoint does not execute natural-language payments. Use the quote and payment endpoints after recipient and amount extraction, then add an LLM agent with tool calling and MCP.',
    'requires_user_approval'=>true
], 200);
