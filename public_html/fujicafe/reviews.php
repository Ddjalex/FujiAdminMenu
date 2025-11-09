<?php
require_once __DIR__ . '/includes/boot.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $item_id = $_GET['item_id'] ?? 0;
    
    $stmt = $pdo->prepare("
        SELECT id, customer_name, rating, comment, created_at 
        FROM menu_item_reviews 
        WHERE item_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$item_id]);
    $reviews = $stmt->fetchAll();
    
    echo json_encode($reviews);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $customer_name = trim($_POST['customer_name'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    
    if ($item_id < 1 || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }
    
    if (empty($customer_name)) {
        $customer_name = 'Anonymous';
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO menu_item_reviews (item_id, customer_name, rating, comment) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$item_id, $customer_name, $rating, $comment ?: null]);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to save review']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']);
