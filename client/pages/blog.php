<?php
// Lấy 3 bài viết featured cho slider (bỏ điều kiện status và deleted)
$sql_featured = "SELECT * FROM blog ORDER BY view_count DESC LIMIT 3";
$result_featured = $mysqli->query($sql_featured);
$featured_blogs = [];
if ($result_featured && $result_featured->num_rows > 0) {
while ($row = $result_featured->fetch_assoc()) {
$featured_blogs[] = $row;
}
}

// Debug: Kiểm tra có bao nhiêu bài viết
$count_query = "SELECT COUNT(*) as total FROM blog";
$count_result = $mysqli->query($count_query);
$total_blogs = 0;
if ($count_result) {
$count_row = $count_result->fetch_assoc();
$total_blogs = $count_row['total'];
}

// Lấy bài viết gần đây (bỏ điều kiện lọc)
$sql_recent = "SELECT * FROM blog ORDER BY created_at DESC LIMIT 6";
$result_recent = $mysqli->query($sql_recent);



// Lấy categories (bỏ điều kiện lọc)
$sql_categories = "SELECT DISTINCT category FROM blog WHERE category IS NOT NULL AND category != ''";
$result_categories = $mysqli->query($sql_categories);
?>
<main>
    <!-- Featured Carousel -->
    <div class="container featured-section">
        <div id="featuredCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php for($i = 0; $i < count($featured_blogs); $i++): ?>
                <button type="button" data-bs-target="#featuredCarousel" data-bs-slide-to="<?= $i ?>"
                    class="<?= $i === 0 ? 'active' : '' ?>" aria-current="true"></button>
                <?php endfor; ?>
            </div>

            <div class="carousel-inner">
                <?php if (!empty($featured_blogs)): ?>
                <?php foreach($featured_blogs as $index => $featured): ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <img src="<?= htmlspecialchars($featured['thumbnail']) ?>"
                        alt="<?= htmlspecialchars($featured['title']) ?>">
                    <div class="carousel-caption">
                        <span class="badge">
                            <i class="fas fa-fire me-1"></i><?= $featured['view_count'] ?> lượt xem
                        </span>
                        <h3><?= htmlspecialchars($featured['title']) ?></h3>
                        <p><?= htmlspecialchars(substr($featured['description'], 0, 150)) ?>...</p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="carousel-item active">
                    <img src="https://hanoispiritofplace.com/wp-content/uploads/2017/12/hinh-nen-thien-nhien-4k-12.jpg"
                        alt="No posts">
                    <div class="carousel-caption">
                        <h3>Chưa có bài viết nào</h3>
                        <p>Nội dung đang được cập nhật</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        <div class="row">
            <!-- Blog Posts -->
            <div class="col-lg-8">
                <div class="section-title">
                    <h2>Bài Viết Mới Nhất</h2>
                    <div class="divider"></div>
                </div>

                <div class="row g-4">
                    <?php 
                    if($result_recent && $result_recent->num_rows > 0): 
                    ?>
                    <?php while($blog = $result_recent->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="card blog-card">
                            <div class="blog-card-img">
                                <img src="<?= htmlspecialchars($blog['thumbnail']) ?>"
                                    alt="<?= htmlspecialchars($blog['title']) ?>">
                                <span class="blog-category-badge">
                                    <?= htmlspecialchars($blog['category'] ?? 'Chưa phân loại') ?>
                                </span>
                            </div>
                            <div class="blog-card-body">
                                <h5 class="blog-card-title">
                                    <a href="pages/blog-detail.php?slug=<?= htmlspecialchars($blog['slug']) ?>"
                                        class="text-decoration-none">
                                        <?= htmlspecialchars($blog['title']) ?>
                                    </a>
                                </h5>

                                <div class="blog-meta">
                                    <span><i class="far fa-calendar-alt"></i>
                                        <?= date('d/m/Y', strtotime($blog['created_at'])) ?>
                                    </span>
                                    <span><i class="far fa-eye"></i>
                                        <?= $blog['view_count'] ?? 0 ?>
                                    </span>
                                </div>

                                <p class="blog-description">
                                    <?= htmlspecialchars($blog['description']) ?>
                                </p>

                                <a href="pages/blog-detail.php?slug=<?= htmlspecialchars($blog['slug']) ?>"
                                    class="btn btn-read-more">
                                    Đọc thêm <i class="fas fa-arrow-right ms-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Không tìm thấy bài viết!</strong><br>
                            <small>Có thể do: chưa có dữ liệu trong database hoặc có lỗi kết nối</small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="sidebar">
                    <!-- Search Box -->
                    <div class="sidebar-card">
                        <h5 class="sidebar-title">
                            <i class="fas fa-search me-2"></i>Tìm Kiếm
                        </h5>
                        <form method="GET" class="search-box">
                            <input type="text" name="search" placeholder="Tìm kiếm bài viết..."
                                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                            <button type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Categories -->
                    <div class="sidebar-card">
                        <h5 class="sidebar-title">
                            <i class="fas fa-folder me-2"></i>Danh Mục
                        </h5>
                        <ul class="category-list">
                            <?php 
                            if ($result_categories && $result_categories->num_rows > 0):
                                while($cat = $result_categories->fetch_assoc()): 
                            ?>
                            <li class="category-item">
                                <i class="fas fa-angle-right"></i>
                                <?= htmlspecialchars($cat['category']) ?>
                            </li>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <li class="text-muted"><small>Chưa có danh mục nào</small></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Popular Posts -->
                    <div class="sidebar-card">
                        <h5 class="sidebar-title">
                            <i class="fas fa-fire me-2"></i>Bài Viết Phổ Biến
                        </h5>
                        <?php
                        $sql_popular = "SELECT blog_id, title, slug, thumbnail, view_count, created_at 
                                       FROM blog 
                                       ORDER BY view_count DESC 
                                       LIMIT 4";
                        $result_popular = $mysqli->query($sql_popular);
                        
                        if ($result_popular && $result_popular->num_rows > 0):
                            while($popular = $result_popular->fetch_assoc()):
                        ?>
                        <div class="popular-post">
                            <img src="<?= htmlspecialchars($popular['thumbnail']) ?>"
                                alt="<?= htmlspecialchars($popular['title']) ?>" class="popular-post-img">
                            <div class="popular-post-content">
                                <h6>
                                    <a href="blog-detail.php?slug=<?= htmlspecialchars($popular['slug']) ?>"
                                        class="text-decoration-none text-dark">
                                        <?= htmlspecialchars($popular['title']) ?>
                                    </a>
                                </h6>
                                <div class="popular-post-meta">
                                    <i class="far fa-eye me-1"></i><?= $popular['view_count'] ?? 0 ?> views
                                    <span class="mx-2">•</span>
                                    <i class="far fa-calendar me-1"></i>
                                    <?= date('d/m/Y', strtotime($popular['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <p class="text-muted text-center"><small>Chưa có bài viết phổ biến</small></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php $mysqli->close(); ?>