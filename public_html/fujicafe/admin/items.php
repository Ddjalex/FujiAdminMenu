<?php
require_once dirname(__DIR__) . '/includes/boot.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

require_admin_auth();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    if ($action === 'create' || $action === 'edit') {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $tiktok_link = trim($_POST['tiktok_link'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $position = (int)($_POST['position'] ?? 0);
        $image_url = $_POST['existing_image_url'] ?? '';

        $errors = array_merge(
            validate_required(['name' => 'Item name', 'price' => 'Price', 'category_id' => 'Category'], $_POST),
            validate_numeric(['price' => 'Price', 'position' => 'Position', 'category_id' => 'Category'], $_POST)
        );

        if (!empty($_FILES['image']['name'])) {
            $upload_result = upload_image($_FILES['image'], 'assets/uploads/');
            if (isset($upload_result['error'])) {
                $errors['image'] = $upload_result['error'];
            } else {
                $image_url = $upload_result['url'];
            }
        }

        if (empty($errors)) {
            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO menu_items (category_id, name, price, description, tiktok_link, image_url, is_active, position) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$category_id, $name, $price, $description, $tiktok_link, $image_url, $is_active, $position]);
                flash('success', 'Menu item created successfully');
                redirect('items.php');
            } else {
                $stmt = $pdo->prepare("UPDATE menu_items SET category_id = ?, name = ?, price = ?, description = ?, tiktok_link = ?, image_url = ?, is_active = ?, position = ? WHERE id = ?");
                $stmt->execute([$category_id, $name, $price, $description, $tiktok_link, $image_url, $is_active, $position, $id]);
                flash('success', 'Menu item updated successfully');
                redirect('items.php');
            }
        }
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
        flash('success', 'Menu item deleted successfully');
        redirect('items.php');
    }
}

if ($action === 'edit' && $id) {
    $item = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $item->execute([$id]);
    $item = $item->fetch();
    if (!$item) {
        redirect('items.php');
    }
}

if ($action === 'list') {
    $items = $pdo->query("SELECT mi.*, mc.name as category_name 
        FROM menu_items mi 
        LEFT JOIN menu_categories mc ON mi.category_id = mc.id 
        ORDER BY mc.position ASC, mi.position ASC, mi.name ASC")->fetchAll();
}

$categories = $pdo->query("SELECT * FROM menu_categories ORDER BY position ASC, name ASC")->fetchAll();
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action === 'create' ? 'New Item' : ($action === 'edit' ? 'Edit Item' : 'Menu Items') ?> - Admin</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css">
</head>
<body class="ui">
    <div class="container<?= $action !== 'list' ? ' narrow' : '' ?>">
        <div style="margin-bottom: 24px;">
            <a href="index.php" class="muted">← Dashboard</a>
        </div>

        <?php if ($action === 'list'): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h1>Menu Items</h1>
                <a href="?action=create" style="padding: 10px 16px; background: #2563eb; color: white; border-radius: 10px; text-decoration: none;">+ New Item</a>
            </div>

            <?php if ($msg = flash('success')): ?>
                <div style="background: #1a3a1a; border: 1px solid #2a5a2a; color: #90ee90; padding: 10px; border-radius: 10px; margin-bottom: 16px;">
                    <?= e($msg) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($items)): ?>
                <div class="card">
                    <p class="muted">No menu items yet. Create your first item to get started.</p>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 12px;">
                    <?php foreach ($items as $item): ?>
                        <div class="card" style="display: flex; gap: 16px; align-items: center;">
                            <?php if ($item['image_url']): ?>
                                <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; flex-shrink: 0;">
                            <?php else: ?>
                                <div style="width: 80px; height: 80px; background: var(--bg); border-radius: 8px; flex-shrink: 0;"></div>
                            <?php endif; ?>
                            <div style="flex: 1; min-width: 0;">
                                <div class="card-title"><?= e($item['name']) ?></div>
                                <div class="muted" style="font-size: 14px;">
                                    <?= e($item['category_name'] ?? 'No category') ?> | ETB <?= number_format($item['price'], 2) ?> | 
                                    <?= $item['is_active'] ? '<span style="color: #90ee90;">Active</span>' : '<span style="color: #ffd2d2;">Inactive</span>' ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px; flex-shrink: 0;">
                                <a href="?action=edit&id=<?= $item['id'] ?>" style="padding: 6px 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; font-size: 14px; white-space: nowrap;">Edit</a>
                                <form method="POST" action="?action=delete&id=<?= $item['id'] ?>" style="display: inline;" onsubmit="return confirm('Delete this item?')">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <button type="submit" style="padding: 6px 12px; background: #5a2a2a; color: #ffd2d2; border: 1px solid #7a3a3a; border-radius: 8px; cursor: pointer; font-size: 14px;">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <h1><?= $action === 'create' ? 'New Menu Item' : 'Edit Menu Item' ?></h1>

            <?php if (!empty($errors)): ?>
                <div class="error" style="margin: 16px 0;">
                    <?php foreach ($errors as $error): ?>
                        <div><?= e($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($categories)): ?>
                <div class="error" style="margin: 16px 0;">
                    Please create at least one category before adding menu items.
                    <a href="categories.php?action=create">Create Category</a>
                </div>
            <?php else: ?>

            <form method="POST" enctype="multipart/form-data" style="margin-top: 24px;">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="existing_image_url" value="<?= e($item['image_url'] ?? '') ?>">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Item Name</label>
                    <input type="text" name="name" value="<?= e(old('name', $item['name'] ?? '')) ?>" 
                           style="width: 100%; padding: 10px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 15px;" 
                           required>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Category</label>
                    <select name="category_id" 
                            style="width: 100%; padding: 10px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 15px;" 
                            required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (old('category_id', $item['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Price</label>
                    <input type="number" step="0.01" name="price" value="<?= e(old('price', $item['price'] ?? '')) ?>" 
                           style="width: 100%; padding: 10px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 15px;" 
                           required>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Description</label>
                    <textarea name="description" rows="3" 
                              style="width: 100%; padding: 10px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 15px; resize: vertical;"><?= e(old('description', $item['description'] ?? '')) ?></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">TikTok Link (Optional)</label>
                    <input type="url" name="tiktok_link" value="<?= e(old('tiktok_link', $item['tiktok_link'] ?? '')) ?>" 
                           placeholder="https://www.tiktok.com/@username/video/..." 
                           style="width: 100%; padding: 10px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 15px;">
                    <div style="background: #1a2a3a; border: 1px solid #2a4a6a; border-radius: 8px; padding: 10px; margin-top: 8px;">
                        <div style="display: flex; align-items: start; gap: 8px;">
                            <span style="font-size: 18px; flex-shrink: 0;">ℹ️</span>
                            <div style="font-size: 13px; color: #90c5ff;">
                                <strong>TikTok Video Link:</strong> Add a TikTok video link to showcase your menu item. When you add a link here, customers will see a TikTok icon on the menu that links directly to your video. Perfect for showing how drinks are made or highlighting special items!
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Image</label>
                    <?php if (!empty($item['image_url'])): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?= e($item['image_url']) ?>" alt="Current image" style="max-width: 200px; border-radius: 8px; border: 1px solid var(--border);">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" 
                           style="width: 100%; padding: 10px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 15px;">
                    <small class="muted">JPG, PNG, GIF, WEBP. Max 5MB.</small>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Position</label>
                    <input type="number" name="position" value="<?= e(old('position', $item['position'] ?? 0)) ?>" 
                           style="width: 100%; padding: 10px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 15px;">
                    <small class="muted">Lower numbers appear first</small>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" <?= old('is_active', $item['is_active'] ?? 1) ? 'checked' : '' ?> 
                               style="margin-right: 8px; width: 18px; height: 18px;">
                        <span>Active (visible on public menu)</span>
                    </label>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 500;">
                        <?= $action === 'create' ? 'Create Item' : 'Update Item' ?>
                    </button>
                    <a href="items.php" style="padding: 10px 20px; background: var(--panel); border: 1px solid var(--border); border-radius: 8px; display: inline-block;">Cancel</a>
                </div>
            </form>

            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
