<?php
  // Build base URL for pagination
$baseUrl = "index.php?page=blogs-manager&panel=review-panel";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urlencode($status_filter);
if ($category_filter) $baseUrl .= "&category=" . urlencode($category_filter);
?>
<!-- Stats -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-comments"></i>
        </div>
        <div class="stat-label">Tổng Đánh Giá</div>
        <div class="stat-value"><?php echo $reviewCount['total']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-label">Đánh Giá TB</div>
        <div class="stat-value"><?php echo  $reviewCount['avg_rating']; ?></div>
    </div>
</div>

<!-- Content Card -->
<div class="content-card">
    <div class="card-header-custom">
        <h3 class="card-title">Danh Sách Đánh Giá</h3>
    </div>

    <!-- Filter -->
    <div class="filter-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Tìm kiếm đánh giá..." />
        </div>
        <select class="form-select" style="width: 200px">
            <option value="">Tất cả đánh giá</option>
            <option value="5">5 sao</option>
            <option value="4">4 sao</option>
            <option value="3">3 sao</option>
            <option value="2">2 sao</option>
            <option value="1">1 sao</option>
        </select>
    </div>

    <!-- Review Items -->
    <?php if (empty($reviews)): ?>
    <div class="text-center py-5">
        <p class="text-muted">Không có đánh giá nào</p>
    </div>
    <?php else: ?>
    <?php foreach ($reviews as $review): ?>
    <div class="review-card">
        <div class="review-header">
            <div class="reviewer-info">
                <div class="reviewer-avatar">NA</div>
                <div>
                    <div class="reviewer-name"><?php echo $review['full_name']; ?></div>
                    <div class="review-date"><?php echo formatDate($review['created_at']); ?> - Phòng
                        <?php echo $review['room_number']; ?> <?php echo $review['room_type_name']; ?></div>
                </div>
            </div>
            <div>
                <div class="rating-stars">
                    <?php
                                        $rating = $review['rating']; // Giả sử rating từ 1-5

                                        // Vòng lặp hiển thị sao đầy
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fas fa-star"></i>'; // Sao đầy
                                            } else {
                                                echo '<i class="far fa-star"></i>'; // Sao rỗng
                                            }
                                        }
                                        ?>
                </div>
            </div>
        </div>
        <div class="review-content">
            <?php echo $review['comment']; ?>
        </div>
        <div class="review-actions">
            <button class="btn-sm-custom view">
                <i class="fas fa-eye"></i> Xem chi tiết
            </button>
            <button class="btn-sm-custom delete" onclick="deleteReview(2)">
                <i class="fas fa-trash"></i> Xóa
            </button>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
    <?php echo getPagination($totalReview, $perPage, $pageNum, $baseUrl); ?>
</div>