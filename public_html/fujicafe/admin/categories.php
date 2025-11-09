<?php
require_once dirname(__DIR__) . '/includes/boot.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

require_admin_auth();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    if ($action === 'create' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $position = (int)($_POST['position'] ?? 0);

        $errors = array_merge(
            validate_required(['name' => 'Category name'], $_POST),
            validate_numeric(['position' => 'Position'], $_POST)
        );

        if (empty($errors)) {
            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO menu_categories (name, position) VALUES (?, ?)");
                $stmt->execute([$name, $position]);
                flash('success', 'Category created successfully');
                redirect('categories.php');
            } else {
                $stmt = $pdo->prepare("UPDATE menu_categories SET name = ?, position = ? WHERE id = ?");
                $stmt->execute([$name, $position, $id]);
                flash('success', 'Category updated successfully');
                redirect('categories.php');
            }
        }
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM menu_categories WHERE id = ?");
        $stmt->execute([$id]);
        flash('success', 'Category deleted successfully');
        redirect('categories.php');
    }
}

if ($action === 'edit' && $id) {
    $category = $pdo->prepare("SELECT * FROM menu_categories WHERE id = ?");
    $category->execute([$id]);
    $category = $category->fetch();
    if (!$category) {
        redirect('categories.php');
    }
}

if ($action === 'list') {
    $categories = $pdo->query("SELECT c.*, COUNT(mi.id) as items_count 
        FROM menu_categories c 
        LEFT JOIN menu_items mi ON c.id = mi.category_id 
        GROUP BY c.id 
        ORDER BY c.position ASC, c.name ASC")->fetchAll();
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action === 'create' ? 'New Category' : ($action === 'edit' ? 'Edit Category' : 'Categories') ?> - Admin</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css">
</head>
<body class="ui">
    <div class="container<?= $action !== 'list' ? ' narrow' : '' ?>">
        <div style="margin-bottom: 24px;">
            <a href="index.php" class="muted">← Dashboard</a>
        </div>

        <?php if ($action === 'list'): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h1>Categories</h1>
                <a href="?action=create" style="padding: 10px 16px; background: #2563eb; color: white; border-radius: 10px; text-decoration: none;">+ New Category</a>
            </div>

            <?php if ($msg = flash('success')): ?>
                <div style="background: #1a3a1a; border: 1px solid #2a5a2a; color: #90ee90; padding: 10px; border-radius: 10px; margin-bottom: 16px;">
                    <?= e($msg) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($categories)): ?>
                <div class="card">
                    <p class="muted">No categories yet. Create your first category to get started.</p>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 12px;">
                    <?php foreach ($categories as $cat): ?>
                        <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div class="card-title"><?= e($cat['name']) ?></div>
                                <div class="muted" style="font-size: 14px;">
                                    Position: <?= $cat['position'] ?> | <?= $cat['items_count'] ?> item(s)
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <a href="?action=edit&id=<?= $cat['id'] ?>" style="padding: 6px 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">Edit</a>
                                <form method="POST" action="?action=delete&id=<?= $cat['id'] ?>" style="display: inline;" onsubmit="return confirm('Delete this category? All items in this category will also be deleted.')">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <button type="submit" style="padding: 6px 12px; background: #5a2a2a; color: #ffd2d2; border: 1px solid #7a3a3a; border-radius: 8px; cursor: pointer; font-size: 14px;">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <h1><?= $action === 'create' ? 'New Category' : 'Edit Category' ?></h1>

            <?php if (!empty($errors)): ?>
                <div class="error" style="margin: 16px 0;">
                    <?php foreach ($errors as $error): ?>
                        <div><?= e($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" style="margin-top: 24px;">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Category Name</label>
                    <input type="text" name="name" value="<?= e(old('name', $category['name'] ?? '')) ?>" 
                           style="width: 100%; padding: 10px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 15px;" 
                           required>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 500;">Position</label>
                    <input type="number" name="position" value="<?= e(old('position', $category['position'] ?? 0)) ?>" 
                           style="width: 100%; padding: 10px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-size: 15px;">
                    <small class="muted">Lower numbers appear first</small>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 500;">
                        <?= $action === 'create' ? 'Create Category' : 'Update Category' ?>
                    </button>
                    <a href="categories.php" style="padding: 10px 20px; background: var(--panel); border: 1px solid var(--border); border-radius: 8px; display: inline-block;">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
