<?php
// session_start() đã được gọi trong index.php
require_once __DIR__ . '/../includes/connect.php';

// Lấy thống kê review
$statsQuery = "SELECT 
    COUNT(*) as total_reviews,
    AVG(rating) as avg_rating,
    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
FROM review 
WHERE status = 'Approved' AND deleted IS NULL";
$statsResult = $mysqli->query($statsQuery);
$stats = $statsResult->fetch_assoc();

$totalReviews = $stats['total_reviews'] ?? 0;
$avgRating = $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 0;

// Hàm hiển thị sao
function displayStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '★';
        } else {
            $stars .= '☆';
        }
    }
    return $stars;
}

// Lấy danh sách reviews với thông tin customer và ảnh
$reviewsQuery = "SELECT 
    r.review_id,
    r.rating,
    r.comment,
    r.created_at,
    c.customer_id,
    c.full_name,
    c.avatar
FROM review r
INNER JOIN customer c ON r.customer_id = c.customer_id
WHERE r.status = 'Approved' AND r.deleted IS NULL
ORDER BY r.created_at DESC
LIMIT 20";
$reviewsResult = $mysqli->query($reviewsQuery);
$reviews = $reviewsResult->fetch_all(MYSQLI_ASSOC);

// Lấy ảnh cho mỗi review
foreach ($reviews as &$review) {
    $imagesQuery = "SELECT image_url, display_order 
                    FROM review_images 
                    WHERE review_id = ? AND deleted IS NULL 
                    ORDER BY display_order ASC, id ASC";
    $imgStmt = $mysqli->prepare($imagesQuery);
    $imgStmt->bind_param("i", $review['review_id']);
    $imgStmt->execute();
    $imagesResult = $imgStmt->get_result();
    $review['images'] = $imagesResult->fetch_all(MYSQLI_ASSOC);
    $imgStmt->close();
}

// Lấy tất cả ảnh từ review_images để hiển thị trong gallery bên phải
$galleryQuery = "SELECT image_url 
                 FROM review_images 
                 WHERE deleted IS NULL 
                 ORDER BY created_at DESC 
                 LIMIT 20";
$galleryResult = $mysqli->query($galleryQuery);
$galleryImages = $galleryResult->fetch_all(MYSQLI_ASSOC);

// Kiểm tra thông báo success/error
$successMsg = isset($_GET['success']) ? 'Đánh giá của bạn đã được gửi thành công! Cảm ơn bạn đã chia sẻ trải nghiệm.' : null;
$errorMsg = isset($_GET['error']) ? 'Có lỗi xảy ra khi gửi đánh giá. Vui lòng thử lại.' : null;
?>

<main>
    <div class="main-content">
        <!-- Left -->
        <div class="left">
            <!-- Summary -->
            <div class="summary-box">
                <h2>Tổng lượt đánh giá</h2>
                <div class="stars"><?php echo displayStars(round($avgRating)); ?> (<?php echo $avgRating; ?>/5)</div>
                <p>Dựa trên <?php echo number_format($totalReviews); ?> đánh giá</p>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($successMsg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-2"></i> <?php echo htmlspecialchars($successMsg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($errorMsg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-times-circle me-2"></i> <?php echo htmlspecialchars($errorMsg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <!-- Reviews -->
            <?php if (empty($reviews)): ?>
                <div class="review">
                    <p class="text-muted">Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá!</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $index => $review): ?>
                <div class="review <?php echo $index >= 3 ? 'hidden' : ''; ?>" data-review-id="<?php echo $review['review_id']; ?>">
                    <div class="review-header">
                        <?php if (!empty($review['avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($review['avatar']); ?>" class="avatar" alt="<?php echo htmlspecialchars($review['full_name']); ?>" />
                        <?php else: ?>
                            <img src="/My-Web-Hotel/client/assets/images/275f99923b080b18e7b474ed6155a17f.jpg" class="avatar" alt="Avatar" />
                        <?php endif; ?>
                        <div>
                            <div class="review-name"><?php echo htmlspecialchars($review['full_name']); ?></div>
                            <div class="stars"><?php echo displayStars($review['rating']); ?></div>
                        </div>
                    </div>
                    <div class="review-text">
                        <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                    </div>
                    <?php if (!empty($review['images'])): ?>
                        <?php foreach ($review['images'] as $img): ?>
                            <img src="<?php echo htmlspecialchars($img['image_url']); ?>" 
                                 class="review-img" 
                                 alt="Ảnh đánh giá" 
                                 style="max-height: 60px; width: auto; margin-right: 5px; cursor: pointer;"
                                 onclick="openImageModal('<?php echo htmlspecialchars($img['image_url']); ?>')" />
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="review-date" style="font-size: 0.85em; color: #666; margin-top: 8px;">
                        <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (count($reviews) > 3): ?>
            <div>
                <!-- Nút xem thêm -->
                <button id="showMore">Xem thêm</button>
            </div>
            <?php endif; ?>
            
            <!-- Write Review -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="write-review">
                <h3>Viết đánh giá</h3>
                <form method="post" action="/My-Web-Hotel/client/controller/submit-review.php" enctype="multipart/form-data" id="reviewForm">
                    <div class="star-input" id="starInput">
                        <span data-value="1">★</span>
                        <span data-value="2">★</span>
                        <span data-value="3">★</span>
                        <span data-value="4">★</span>
                        <span data-value="5">★</span>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" value="0" required>
                    <textarea name="comment" id="commentInput" placeholder="Chia sẻ trải nghiệm của bạn..." required></textarea>
                    
                    <!-- Upload nhiều ảnh -->
                    <div class="mb-3">
                        <label class="form-label">Thêm ảnh (tối đa 6 ảnh)</label>
                        <input type="file" class="form-control" name="review_images[]" id="reviewImagesInput" 
                               accept="image/*" multiple onchange="previewReviewImages(this)">
                        <div id="reviewImagesPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        <small class="text-muted">Định dạng: JPG, PNG, GIF, WEBP. Kích thước tối đa: 5MB mỗi ảnh.</small>
                    </div>
                    
                    <button type="submit">Gửi đánh giá</button>
                </form>
            </div>
            <?php else: ?>
            <div class="write-review">
                <p class="text-muted">Vui lòng <a href="/My-Web-Hotel/client/index.php?page=logIn">đăng nhập</a> để viết đánh giá.</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Gallery -->
        <div class="right">
            <?php if (!empty($galleryImages)): ?>
                <?php foreach ($galleryImages as $galleryImg): ?>
                    <img src="<?php echo htmlspecialchars($galleryImg['image_url']); ?>" 
                         alt="Ảnh đánh giá" 
                         style="cursor: pointer;"
                         onclick="openImageModal('<?php echo htmlspecialchars($galleryImg['image_url']); ?>')" />
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">Chưa có ảnh nào</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Ảnh đánh giá" style="max-width: 100%; height: auto;" />
            </div>
        </div>
    </div>
</div>

<script>
// Mở modal xem ảnh lớn
function openImageModal(imageUrl) {
    document.getElementById('modalImage').src = imageUrl;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}
</script>
