<?php
require_once dirname(__DIR__) . '/includes/boot.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

require_admin_auth();

$categories_count = $pdo->query("SELECT COUNT(*) FROM menu_categories")->fetchColumn();
$items_count = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
$active_items = $pdo->query("SELECT COUNT(*) FROM menu_items WHERE is_active = 1")->fetchColumn();
$reviews_count = $pdo->query("SELECT COUNT(*) FROM menu_item_reviews")->fetchColumn();
$avg_rating = $pdo->query("SELECT COALESCE(AVG(rating), 0) FROM menu_item_reviews")->fetchColumn();

$recent_reviews = $pdo->query("
    SELECT r.*, mi.name as item_name 
    FROM menu_item_reviews r 
    JOIN menu_items mi ON r.item_id = mi.id 
    ORDER BY r.created_at DESC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fuji Cafe</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css">
</head>
<body class="ui">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div>
                <h1 style="margin-bottom: 4px;">Fuji Cafe Admin</h1>
                <p class="muted">Manage your digital menu</p>
            </div>
            <a href="logout.php" style="padding: 8px 16px; background: var(--panel); border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">Logout</a>
        </div>

        <?php if ($msg = flash('success')): ?>
            <div style="background: #1a3a1a; border: 1px solid #2a5a2a; color: #90ee90; padding: 10px; border-radius: 10px; margin: 16px 0;">
                <?= e($msg) ?>
            </div>
        <?php endif; ?>

        <div class="grid" style="margin-top: 32px;">
            <div class="card">
                <div class="card-title">Categories</div>
                <div style="font-size: 32px; font-weight: bold; margin: 8px 0;"><?= $categories_count ?></div>
                <a href="categories.php">Manage Categories →</a>
            </div>

            <div class="card">
                <div class="card-title">Menu Items</div>
                <div style="font-size: 32px; font-weight: bold; margin: 8px 0;"><?= $items_count ?></div>
                <div class="muted" style="font-size: 14px;"><?= $active_items ?> active</div>
                <a href="items.php">Manage Items →</a>
            </div>

            <div class="card">
                <div class="card-title">Customer Reviews</div>
                <div style="font-size: 32px; font-weight: bold; margin: 8px 0;"><?= $reviews_count ?></div>
                <div class="muted" style="font-size: 14px;">
                    <?php if ($reviews_count > 0): ?>
                        ⭐ <?= number_format($avg_rating, 1) ?> average rating
                    <?php else: ?>
                        No reviews yet
                    <?php endif; ?>
                </div>
                <a href="reviews_mgmt.php">Manage Reviews →</a>
            </div>

            <div class="card">
                <div class="card-title">Public Menu</div>
                <p class="muted">View the customer-facing menu</p>
                <a href="../index.php" target="_blank">View Menu →</a>
            </div>

            <div class="card">
                <div class="card-title">Restaurant Settings</div>
                <p class="muted">Manage logo and restaurant info</p>
                <a href="settings.php">Manage Settings →</a>
            </div>
        </div>

        <div class="mt">
            <h2>Quick Actions</h2>
            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 12px;">
                <a href="categories.php?action=create" style="padding: 10px 16px; background: var(--panel); border: 1px solid var(--border); border-radius: 10px; display: inline-block;">+ New Category</a>
                <a href="items.php?action=create" style="padding: 10px 16px; background: var(--panel); border: 1px solid var(--border); border-radius: 10px; display: inline-block;">+ New Menu Item</a>
            </div>
        </div>

        <?php if (!empty($recent_reviews)): ?>
        <div class="mt">
            <h2>Recent Reviews</h2>
            <div style="margin-top: 12px;">
                <?php foreach ($recent_reviews as $review): ?>
                    <div class="card" style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                            <div>
                                <strong><?= e($review['item_name']) ?></strong>
                                <div style="color: #fbbf24; font-size: 14px; margin-top: 4px;">
                                    <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                                </div>
                            </div>
                            <div class="muted" style="font-size: 13px;">
                                <?= date('M j, Y', strtotime($review['created_at'])) ?>
                            </div>
                        </div>
                        <div class="muted" style="font-size: 14px;">
                            <strong><?= e($review['customer_name'] ?: 'Anonymous') ?>:</strong>
                            <?= e($review['comment'] ?: 'No comment') ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <a href="reviews_mgmt.php" style="display: inline-block; margin-top: 8px; padding: 8px 14px; background: var(--panel); border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">View All Reviews →</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
