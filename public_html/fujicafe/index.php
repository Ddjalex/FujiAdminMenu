<?php
require_once __DIR__ . '/includes/boot.php';

$settings = $pdo->query("SELECT * FROM restaurant_settings LIMIT 1")->fetch();
if (!$settings) {
    $settings = ['restaurant_name' => 'Fuji Cafe', 'restaurant_subtitle' => 'Artisan Coffee & Fresh Cuisine', 'logo_url' => null];
}

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
    <button class="hamburger-btn" onclick="toggleSidebar()" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <?php if ($settings['logo_url']): ?>
                    <img src="<?= ASSETS_URL . '/../' . e($settings['logo_url']) ?>" alt="Logo" class="tenant-logo" style="width: 40px; height: 40px; object-fit: contain; background: white; padding: 4px;">
                <?php else: ?>
                    <div class="tenant-logo" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; color: white;">
                        <?= strtoupper(substr($settings['restaurant_name'], 0, 2)) ?>
                    </div>
                <?php endif; ?>
                <span style="font-size: 18px; font-weight: bold; margin-left: 12px;"><?= e($settings['restaurant_name']) ?></span>
            </div>
            <button class="sidebar-close" onclick="toggleSidebar()">&times;</button>
        </div>
        
        <nav class="sidebar-nav">
            <a href="#menu" class="sidebar-link" onclick="toggleSidebar()">
                <span class="icon">🍽️</span>
                <span>Menu</span>
            </a>
            <a href="#feedback" class="sidebar-link" onclick="toggleSidebar()">
                <span class="icon">💬</span>
                <span>Feedback</span>
            </a>
            <a href="#contact" class="sidebar-link" onclick="toggleSidebar()">
                <span class="icon">📞</span>
                <span>Contact Us</span>
            </a>
            <a href="#review" class="sidebar-link" onclick="toggleSidebar()">
                <span class="icon">⭐</span>
                <span>Review</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <div class="social-links">
                <a href="#" aria-label="Facebook" class="social-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="#" aria-label="Instagram" class="social-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                <a href="#" aria-label="TikTok" class="social-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                    </svg>
                </a>
            </div>
            <div class="powered-by">
                <span>Powered by <a href="https://neodigitalsolutions.com/" target="_blank" style="color: var(--accent); text-decoration: none; font-weight: 600;">neodigitalsolutions.com</a></span>
            </div>
        </div>
    </div>
    
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="hero">
        <div class="container">
            <div class="brand">
                <?php if ($settings['logo_url']): ?>
                    <img src="<?= ASSETS_URL . '/../' . e($settings['logo_url']) ?>" alt="Logo" class="tenant-logo" style="width: 64px; height: 64px; object-fit: contain; background: white; padding: 8px;">
                <?php else: ?>
                    <div class="tenant-logo" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; color: white;">
                        <?= strtoupper(substr($settings['restaurant_name'], 0, 2)) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h1 class="h2"><?= e($settings['restaurant_name']) ?></h1>
                    <p class="subtitle"><?= e($settings['restaurant_subtitle']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="container" id="menu">
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
                                    <span class="price">ETB <?= number_format($item['price'], 2) ?></span>
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
        
        <section id="feedback" style="margin-top: 60px; padding: 40px; background: white; border-radius: 16px; box-shadow: var(--shadow);">
            <h2 class="section-title">Feedback</h2>
            <p style="color: var(--muted); margin-bottom: 24px;">We value your feedback! Let us know how we can improve your experience.</p>
            <form style="max-width: 600px;">
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" placeholder="Enter your name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group">
                    <label>Your Feedback</label>
                    <textarea placeholder="Share your thoughts with us..." rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Feedback</button>
            </form>
        </section>
        
        <section id="contact" style="margin-top: 40px; padding: 40px; background: white; border-radius: 16px; box-shadow: var(--shadow);">
            <h2 class="section-title">Contact Us</h2>
            <div style="display: grid; gap: 24px; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                <div>
                    <h3 style="color: var(--primary); font-size: 16px; margin-bottom: 12px;">📍 Location</h3>
                    <p style="color: var(--muted);">123 Coffee Street<br>Addis Ababa, Ethiopia</p>
                </div>
                <div>
                    <h3 style="color: var(--primary); font-size: 16px; margin-bottom: 12px;">📞 Phone</h3>
                    <p style="color: var(--muted);">+251 11 123 4567<br>+251 91 234 5678</p>
                </div>
                <div>
                    <h3 style="color: var(--primary); font-size: 16px; margin-bottom: 12px;">⏰ Hours</h3>
                    <p style="color: var(--muted);">Mon-Fri: 7:00 AM - 8:00 PM<br>Sat-Sun: 8:00 AM - 9:00 PM</p>
                </div>
                <div>
                    <h3 style="color: var(--primary); font-size: 16px; margin-bottom: 12px;">✉️ Email</h3>
                    <p style="color: var(--muted);">info@fujicafe.com<br>support@fujicafe.com</p>
                </div>
            </div>
        </section>
        
        <section id="review" style="margin: 40px 0 60px; padding: 40px; background: white; border-radius: 16px; box-shadow: var(--shadow);">
            <h2 class="section-title">Leave a Review</h2>
            <p style="color: var(--muted); margin-bottom: 24px;">Share your experience with us and help others discover great items!</p>
            <p style="color: var(--text);">Click the "Reviews" button on any menu item above to leave your feedback.</p>
        </section>
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

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const hamburger = document.querySelector('.hamburger-btn');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
            hamburger.classList.toggle('active');
            
            if (sidebar.classList.contains('open')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const sidebar = document.getElementById('sidebar');
                if (sidebar.classList.contains('open')) {
                    toggleSidebar();
                }
            }
        });
    </script>
</body>
</html>
