<?php
require_once dirname(__DIR__) . '/includes/boot.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

require_admin_auth();

$settings = $pdo->query("SELECT * FROM restaurant_settings LIMIT 1")->fetch();

if (!$settings) {
    $pdo->exec("INSERT INTO restaurant_settings (restaurant_name, restaurant_subtitle) VALUES ('Fuji Cafe', 'Artisan Coffee & Fresh Cuisine')");
    $settings = $pdo->query("SELECT * FROM restaurant_settings LIMIT 1")->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate();
    
    $restaurant_name = trim($_POST['restaurant_name'] ?? '');
    $restaurant_subtitle = trim($_POST['restaurant_subtitle'] ?? '');
    $facebook_url = trim($_POST['facebook_url'] ?? '');
    $instagram_url = trim($_POST['instagram_url'] ?? '');
    $tiktok_url = trim($_POST['tiktok_url'] ?? '');
    $twitter_url = trim($_POST['twitter_url'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    if (empty($restaurant_name)) {
        flash('error', 'Restaurant name is required');
        redirect('settings.php');
    }
    
    $logo_url = $settings['logo_url'];
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $max_size = 5 * 1024 * 1024;
        
        if ($_FILES['logo']['size'] > $max_size) {
            flash('error', 'Logo file size must not exceed 5MB');
            redirect('settings.php');
        }
        
        $image_info = getimagesize($_FILES['logo']['tmp_name']);
        if ($image_info === false) {
            flash('error', 'Uploaded file is not a valid image');
            redirect('settings.php');
        }
        
        $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
        if (!in_array($image_info[2], $allowed_types)) {
            flash('error', 'Logo must be a JPG, PNG, GIF, or WEBP image');
            redirect('settings.php');
        }
        
        $extension_map = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_GIF => 'gif',
            IMAGETYPE_WEBP => 'webp'
        ];
        $safe_ext = $extension_map[$image_info[2]];
        
        $upload_dir = dirname(__DIR__) . '/assets/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $filename = 'logo_' . time() . '.' . $safe_ext;
        $target = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
            if ($logo_url && file_exists(dirname(__DIR__) . '/' . $logo_url)) {
                unlink(dirname(__DIR__) . '/' . $logo_url);
            }
            $logo_url = 'assets/uploads/' . $filename;
        } else {
            flash('error', 'Failed to upload logo');
            redirect('settings.php');
        }
    }
    
    $stmt = $pdo->prepare("UPDATE restaurant_settings SET restaurant_name = ?, restaurant_subtitle = ?, logo_url = ?, facebook_url = ?, instagram_url = ?, tiktok_url = ?, twitter_url = ?, phone = ?, email = ?, address = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$restaurant_name, $restaurant_subtitle, $logo_url, $facebook_url, $instagram_url, $tiktok_url, $twitter_url, $phone, $email, $address, $settings['id']]);
    
    flash('success', 'Settings updated successfully!');
    redirect('settings.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Settings - Admin</title>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/app.css">
</head>
<body class="ui">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div>
                <h1 style="margin-bottom: 4px;">Restaurant Settings</h1>
                <p class="muted">Manage your restaurant information and logo</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="index.php" style="padding: 8px 16px; background: var(--panel); border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">← Back to Dashboard</a>
                <a href="logout.php" style="padding: 8px 16px; background: var(--panel); border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">Logout</a>
            </div>
        </div>

        <?php if ($msg = flash('success')): ?>
            <div style="background: #1a3a1a; border: 1px solid #2a5a2a; color: #90ee90; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px;">
                <?= e($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg = flash('error')): ?>
            <div style="background: #3a1a1a; border: 1px solid #5a2a2a; color: #ee9090; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px;">
                <?= e($msg) ?>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 800px;">
            <h2 style="margin-top: 0;">Restaurant Information</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label>Restaurant Logo</label>
                    <?php if ($settings['logo_url']): ?>
                        <div style="margin-bottom: 12px;">
                            <img src="<?= BASE_URL ?>/fujicafe/<?= e($settings['logo_url']) ?>" alt="Current Logo" style="max-width: 200px; max-height: 200px; border-radius: 12px; border: 2px solid var(--border); background: white; padding: 12px;">
                            <div class="muted" style="font-size: 13px; margin-top: 8px;">Current logo</div>
                        </div>
                    <?php else: ?>
                        <div class="muted" style="margin-bottom: 12px; font-size: 14px;">No logo uploaded yet</div>
                    <?php endif; ?>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" style="padding: 10px; background: var(--panel); border: 1px solid var(--border); border-radius: 8px; width: 100%; font-size: 14px;">
                    <div class="muted" style="font-size: 13px; margin-top: 6px;">Accepted formats: JPG, PNG, GIF, WEBP (Max 5MB)</div>
                </div>

                <div class="form-group">
                    <label>Restaurant Name</label>
                    <input type="text" name="restaurant_name" value="<?= e($settings['restaurant_name']) ?>" required maxlength="200">
                </div>

                <div class="form-group">
                    <label>Restaurant Subtitle</label>
                    <input type="text" name="restaurant_subtitle" value="<?= e($settings['restaurant_subtitle']) ?>" maxlength="200" placeholder="e.g., Artisan Coffee & Fresh Cuisine">
                </div>

                <h2 style="margin-top: 40px; margin-bottom: 20px;">Social Media Links</h2>
                
                <div class="form-group">
                    <label>Facebook URL</label>
                    <input type="url" name="facebook_url" value="<?= e($settings['facebook_url'] ?? '') ?>" placeholder="https://facebook.com/yourpage">
                </div>

                <div class="form-group">
                    <label>Instagram URL</label>
                    <input type="url" name="instagram_url" value="<?= e($settings['instagram_url'] ?? '') ?>" placeholder="https://instagram.com/youraccount">
                </div>

                <div class="form-group">
                    <label>TikTok URL</label>
                    <input type="url" name="tiktok_url" value="<?= e($settings['tiktok_url'] ?? '') ?>" placeholder="https://tiktok.com/@youraccount">
                </div>

                <div class="form-group">
                    <label>Twitter URL</label>
                    <input type="url" name="twitter_url" value="<?= e($settings['twitter_url'] ?? '') ?>" placeholder="https://twitter.com/youraccount">
                </div>

                <h2 style="margin-top: 40px; margin-bottom: 20px;">Contact Information</h2>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?= e($settings['phone'] ?? '') ?>" placeholder="+251 11 123 4567">
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= e($settings['email'] ?? '') ?>" placeholder="info@restaurant.com">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="3" placeholder="123 Coffee Street, Addis Ababa, Ethiopia"><?= e($settings['address'] ?? '') ?></textarea>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="btn-primary">Save Settings</button>
                    <a href="index.php" class="btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
