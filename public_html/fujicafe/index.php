<?php
require_once __DIR__ . '/includes/boot.php';

$categories = $pdo->query("SELECT * FROM menu_categories ORDER BY position ASC, name ASC")->fetchAll();

$items_by_category = [];
$stmt = $pdo->query("
    SELECT mi.*, mc.name as category_name 
    FROM menu_items mi 
    JOIN menu_categories mc ON mi.category_id = mc.id 
    WHERE mi.is_active = 1 
    ORDER BY mc.position ASC, mi.position ASC, mi.name ASC
");
while ($item = $stmt->fetch()) {
    $items_by_category[$item['category_name']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fuji Cafe - Digital Menu</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/menu.css">
</head>
<body>
    <div class="hero">
        <div class="container">
            <div class="brand">
                <div class="tenant-logo" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; color: white;">FC</div>
                <div>
                    <h1 class="h2">Fuji Cafe</h1>
                    <p class="subtitle">Artisan Coffee & Fresh Cuisine</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="toolbar">
            <div class="pill-tabs">
                <button class="pill active" data-cat="all">All Items</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="pill" data-cat="<?= e($cat['name']) ?>"><?= e($cat['name']) ?></button>
                <?php endforeach; ?>
            </div>
            <div class="searchbox">
                <input type="text" placeholder="Search menu..." data-search>
            </div>
        </div>

        <?php foreach ($items_by_category as $cat_name => $items): ?>
            <div data-catname="<?= e($cat_name) ?>">
                <h2 class="section-title"><?= e($cat_name) ?></h2>
                <div class="menu-grid">
                    <?php foreach ($items as $item): ?>
                        <div class="card" data-item="<?= e($item['name'] . ' ' . $item['description']) ?>">
                            <?php if ($item['image_url']): ?>
                                <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>">
                            <?php else: ?>
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect fill='%23222' width='400' height='300'/%3E%3C/svg%3E" alt="No image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h3 class="card-title"><?= e($item['name']) ?></h3>
                                <p class="card-desc"><?= e($item['description'] ?: '') ?></p>
                                <div class="row">
                                    <span class="price">$<?= number_format($item['price'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($items_by_category)): ?>
            <div style="text-align: center; padding: 60px 20px; color: var(--muted);">
                <p>No menu items available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="<?= ASSETS_URL ?>/js/app.js"></script>
</body>
</html>
