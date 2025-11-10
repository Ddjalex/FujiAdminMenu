<?php
// Helper functions

function redirect($url) {
    header("Location: $url");
    exit;
}

function flash($key, $message = null) {
    if ($message === null) {
        $msg = $_SESSION["flash_$key"] ?? null;
        unset($_SESSION["flash_$key"]);
        return $msg;
    }
    $_SESSION["flash_$key"] = $message;
}

function old($key, $default = '') {
    return $_POST[$key] ?? $default;
}

function validate_required($fields, $data) {
    $errors = [];
    foreach ($fields as $field => $label) {
        if (empty($data[$field])) {
            $errors[$field] = "$label is required";
        }
    }
    return $errors;
}

function validate_numeric($fields, $data) {
    $errors = [];
    foreach ($fields as $field => $label) {
        if (!empty($data[$field]) && !is_numeric($data[$field])) {
            $errors[$field] = "$label must be a number";
        }
    }
    return $errors;
}

function upload_image($file, $upload_dir = 'assets/uploads/') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload failed'];
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp'];
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'File too large. Max 5MB'];
    }
    
    $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
    $upload_path = BASE_PATH . '/' . $upload_dir;
    
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }
    
    $filepath = $upload_path . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'url' => '/fujicafe/' . ltrim($upload_dir, '/') . $filename];
    }
    
    return ['error' => 'Failed to save file'];
}

function require_admin_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: /fujicafe/admin/login.php');
        exit;
    }
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function csrf_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_validate() {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid security token');
        redirect($_SERVER['HTTP_REFERER'] ?? '/fujicafe/admin/');
    }
}
