<?php
require_once __DIR__ . '/includes/boot.php';

$categories = $pdo->query("SELECT * FROM menu_categories ORDER BY position ASC, name ASC")->fetchAll();

$items_by_category = [];
$stmt = $pdo->query("
    SELECT mi.id, mi.category_id, mi.name, mi.price, mi.description, mi.image_url, 
           mi.is_active, mi.position, mi.created_at, mi.updated_at,
           mc.name as category_name, mc.position as cat_position,
           COALESCE(AVG(r.rating), 0) as avg_rating,
           COUNT(r.id) as review_count
    FROM menu_items mi 
    JOIN menu_categories mc ON mi.category_id = mc.id 
    LEFT JOIN menu_item_reviews r ON mi.id = r.item_id
    WHERE mi.is_active = 1 
    GROUP BY mi.id, mi.category_id, mi.name, mi.price, mi.description, mi.image_url,
             mi.is_active, mi.position, mi.created_at, mi.updated_at,
             mc.name, mc.position
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
                    <?php foreach ($items as $item): 
                        $avg_rating = round($item['avg_rating'], 1);
                        $full_stars = floor($avg_rating);
                        $has_half = ($avg_rating - $full_stars) >= 0.5;
                        $empty_stars = 5 - $full_stars - ($has_half ? 1 : 0);
                    ?>
                        <div class="card" data-item="<?= e($item['name'] . ' ' . $item['description']) ?>">
                            <?php if ($item['image_url']): ?>
                                <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>">
                            <?php else: ?>
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect fill='%23222' width='400' height='300'/%3E%3C/svg%3E" alt="No image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h3 class="card-title"><?= e($item['name']) ?></h3>
                                <p class="card-desc"><?= e($item['description'] ?: '') ?></p>
                                
                                <?php if ($item['review_count'] > 0): ?>
                                    <div class="rating-bar">
                                        <div class="stars">
                                            <?php for ($i = 0; $i < $full_stars; $i++): ?>
                                                <span class="star">★</span>
                                            <?php endfor; ?>
                                            <?php if ($has_half): ?>
                                                <span class="star">★</span>
                                            <?php endif; ?>
                                            <?php for ($i = 0; $i < $empty_stars; $i++): ?>
                                                <span class="star empty">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="rating-count"><?= $avg_rating ?> (<?= $item['review_count'] ?>)</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="row">
                                    <span class="price">$<?= number_format($item['price'], 2) ?></span>
                                    <button class="btn btn-outline" onclick="openReviewModal(<?= $item['id'] ?>, '<?= e($item['name']) ?>')">
                                        Reviews
                                    </button>
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

    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalItemName">Reviews</h2>
                <button class="modal-close" onclick="closeReviewModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="reviewsList"></div>
                
                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border);">
                    <h3 style="margin-top: 0; font-size: 18px;">Leave a Review</h3>
                    <form id="reviewForm" onsubmit="submitReview(event)">
                        <input type="hidden" id="itemId" name="item_id">
                        
                        <div class="form-group">
                            <label>Your Rating</label>
                            <div class="star-rating" id="starRating">
                                <button type="button" class="star-btn" data-rating="1">★</button>
                                <button type="button" class="star-btn" data-rating="2">★</button>
                                <button type="button" class="star-btn" data-rating="3">★</button>
                                <button type="button" class="star-btn" data-rating="4">★</button>
                                <button type="button" class="star-btn" data-rating="5">★</button>
                            </div>
                            <input type="hidden" id="ratingValue" name="rating" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Your Name (optional)</label>
                            <input type="text" name="customer_name" placeholder="Anonymous" maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label>Your Review</label>
                            <textarea name="comment" placeholder="Share your experience..." rows="4"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= ASSETS_URL ?>/js/app.js"></script>
    <script>
        let currentItemId = null;

        function openReviewModal(itemId, itemName) {
            currentItemId = itemId;
            document.getElementById('modalItemName').textContent = itemName;
            document.getElementById('itemId').value = itemId;
            document.getElementById('reviewModal').classList.add('open');
            loadReviews(itemId);
            resetForm();
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').classList.remove('open');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function loadReviews(itemId) {
            fetch(`reviews.php?item_id=${itemId}`)
                .then(res => res.json())
                .then(reviews => {
                    const container = document.getElementById('reviewsList');
                    if (reviews.length === 0) {
                        container.innerHTML = '<p class="muted">No reviews yet. Be the first to review!</p>';
                        return;
                    }
                    
                    container.innerHTML = reviews.map(review => {
                        const name = escapeHtml(review.customer_name || 'Anonymous');
                        const comment = review.comment ? escapeHtml(review.comment) : '';
                        const date = new Date(review.created_at).toLocaleDateString();
                        const stars = '★'.repeat(review.rating);
                        const emptyStars = '<span class="star empty">★</span>'.repeat(5 - review.rating);
                        
                        return `
                            <div class="review-card">
                                <div class="review-header">
                                    <div>
                                        <div class="review-name">${name}</div>
                                        <div class="stars">
                                            ${stars}${emptyStars}
                                        </div>
                                    </div>
                                    <div class="review-date">${date}</div>
                                </div>
                                ${comment ? `<p class="review-comment">${comment}</p>` : ''}
                            </div>
                        `;
                    }).join('');
                });
        }

        function submitReview(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            fetch('reviews.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadReviews(currentItemId);
                    resetForm();
                    location.reload();
                } else {
                    alert(data.error || 'Failed to submit review');
                }
            })
            .catch(err => {
                alert('An error occurred. Please try again.');
            });
        }

        function resetForm() {
            document.getElementById('reviewForm').reset();
            document.getElementById('ratingValue').value = '';
            document.querySelectorAll('.star-btn').forEach(btn => btn.classList.remove('active'));
        }

        document.querySelectorAll('.star-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                document.getElementById('ratingValue').value = rating;
                
                document.querySelectorAll('.star-btn').forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('active');
                    } else {
                        star.classList.remove('active');
                    }
                });
            });
        });

        document.getElementById('reviewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReviewModal();
            }
        });
    </script>
</body>
</html>
