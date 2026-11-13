<?php
require_once __DIR__ . '/../config/bootstrap.php';
try {
    $userId = require_auth();
    $stmt = db()->prepare("SELECT amount, fee, recipient_name AS `to`, reference, status, created_at AS `time` FROM transactions WHERE user_id=? ORDER BY id DESC LIMIT 25");
    $stmt->execute([$userId]); $rows = $stmt->fetchAll();
    foreach ($rows as &$row) { $row['amount']=(float)$row['amount']; $row['fee']=(float)$row['fee']; }
    respond(['transactions'=>$rows]);
} catch (Throwable $e) { respond(['error'=>'Could not load transactions. Check database setup.'], 500); }
