<?php
require_once dirname(__DIR__) . '/includes/boot.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

require_admin_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $review_id = (int)$_POST['review_id'];
    $pdo->prepare("DELETE FROM menu_item_reviews WHERE id = ?")->execute([$review_id]);
    set_flash('success', 'Review deleted successfully');
    header('Location: reviews_mgmt.php');
    exit;
}

$all_reviews = $pdo->query("
    SELECT r.*, mi.name as item_name 
    FROM menu_item_reviews r 
    JOIN menu_items mi ON r.item_id = mi.id 
    ORDER BY r.created_at DESC
")->fetchAll();

$stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        AVG(rating) as avg_rating,
        COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
        COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
        COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
        COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
        COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
    FROM menu_item_reviews
")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews Management - Fuji Cafe</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css">
</head>
<body class="ui">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div>
                <h1 style="margin-bottom: 4px;">Reviews Management</h1>
                <p class="muted">View and manage customer reviews</p>
            </div>
            <a href="index.php" class="btn">← Back to Dashboard</a>
        </div>

        <?php if ($msg = flash('success')): ?>
            <div style="background: #1a3a1a; border: 1px solid #2a5a2a; color: #90ee90; padding: 10px; border-radius: 10px; margin: 16px 0;">
                <?= e($msg) ?>
            </div>
        <?php endif; ?>

        <div class="grid" style="margin-top: 24px;">
            <div class="card">
                <div class="card-title">Total Reviews</div>
                <div style="font-size: 32px; font-weight: bold; margin: 8px 0;"><?= $stats['total'] ?></div>
            </div>

            <div class="card">
                <div class="card-title">Average Rating</div>
                <div style="font-size: 32px; font-weight: bold; margin: 8px 0; color: #fbbf24;">
                    <?= number_format($stats['avg_rating'], 1) ?> ★
                </div>
            </div>

            <div class="card">
                <div class="card-title">Rating Distribution</div>
                <div style="font-size: 14px; margin-top: 8px;">
                    <div style="display: flex; justify-content: space-between; margin: 4px 0;">
                        <span>5★</span>
                        <span style="color: #fbbf24; font-weight: 600;"><?= $stats['five_star'] ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin: 4px 0;">
                        <span>4★</span>
                        <span style="color: #fbbf24; font-weight: 600;"><?= $stats['four_star'] ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin: 4px 0;">
                        <span>3★</span>
                        <span style="color: #fbbf24; font-weight: 600;"><?= $stats['three_star'] ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin: 4px 0;">
                        <span>2★</span>
                        <span style="color: #fbbf24; font-weight: 600;"><?= $stats['two_star'] ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin: 4px 0;">
                        <span>1★</span>
                        <span style="color: #fbbf24; font-weight: 600;"><?= $stats['one_star'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt">
            <h2>All Reviews</h2>
            <?php if (empty($all_reviews)): ?>
                <div class="card" style="text-align: center; padding: 40px; margin-top: 16px;">
                    <p class="muted">No reviews yet</p>
                </div>
            <?php else: ?>
                <div style="margin-top: 16px;">
                    <?php foreach ($all_reviews as $review): ?>
                        <div class="card" style="margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <div>
                                            <strong><?= e($review['item_name']) ?></strong>
                                            <div style="color: #fbbf24; font-size: 14px; margin-top: 4px;">
                                                <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                                            </div>
                                        </div>
                                        <div class="muted" style="font-size: 13px;">
                                            <?= date('M j, Y g:i A', strtotime($review['created_at'])) ?>
                                        </div>
                                    </div>
                                    <div style="margin-bottom: 8px;">
                                        <strong style="font-size: 14px;"><?= e($review['customer_name'] ?: 'Anonymous') ?></strong>
                                    </div>
                                    <?php if ($review['comment']): ?>
                                        <div style="color: var(--muted); font-size: 14px; line-height: 1.5;">
                                            <?= e($review['comment']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this review?')" style="margin-left: 12px;">
                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                    <button type="submit" name="delete_review" class="btn" style="background: #7f1d1d; border-color: #991b1b; color: #fff;">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
