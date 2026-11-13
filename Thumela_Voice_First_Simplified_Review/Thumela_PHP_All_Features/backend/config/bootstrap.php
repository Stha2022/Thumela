<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function json_input(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON request.']);
        exit;
    }
    return $data;
}
function respond(array $data, int $status=200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
function require_method(string $method): void {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== $method) {
        header('Allow: '.$method);
        respond(['error' => 'Method not allowed.'], 405);
    }
}

function start_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        session_set_cookie_params(['httponly'=>true, 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite'=>'Lax']);
        session_start();
    }
}
function current_user_id(): int {
    start_session();
    return (int)($_SESSION['user_id'] ?? 0);
}
function require_auth(): int {
    $id = current_user_id();
    if ($id <= 0) respond(['error'=>'Please log in to continue.'], 401);
    return $id;
}
