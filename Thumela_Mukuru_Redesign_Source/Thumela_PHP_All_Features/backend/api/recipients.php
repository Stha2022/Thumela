<?php
require_once __DIR__ . '/../config/bootstrap.php';
try {
    $userId = require_auth();
    $stmt = db()->prepare("SELECT id, name, country, address, network FROM recipients WHERE is_active=1 AND (user_id IS NULL OR user_id=?) ORDER BY user_id IS NULL DESC, name");
    $stmt->execute([$userId]); $rows = $stmt->fetchAll();
    respond(['recipients'=>$rows]);
} catch (Throwable $e) { respond(['error'=>'Could not load recipients. Check database setup.'], 500); }
