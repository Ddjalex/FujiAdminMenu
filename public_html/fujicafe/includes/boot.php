<?php
// Bootstrap file - DO NOT MODIFY
session_start();

// Base paths
define('BASE_PATH', dirname(__DIR__));
define('ASSETS_URL', '/fujicafe/assets');

// Include database connection
require_once __DIR__ . '/db.php';

// CSRF token generation
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF token validation
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Sanitize output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
