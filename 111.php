<?php
session_start();
require_once 'client/includes/connect.php';

// Kiểm tra kết nối database
if (!$mysqli) {
    die("Lỗi kết nối database: " . mysqli_connect_error());
}

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

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - OceanPearl Hotel</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
    :root {
        --primary-color: #deb666;
        --primary-dark: #c59d4d;
        --text-dark: #2c3e50;
        --text-muted: #6c757d;
        --bg-light: #f8f9fa;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--text-dark);
        background-color: #fff;
    }

    /* Header Section */
    .blog-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 80px 0 60px;
        margin-bottom: 50px;
    }

    .blog-header h1 {
        font-size: 3.5rem;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .blog-header p {
        font-size: 1.2rem;
        opacity: 0.95;
    }

    /* Featured Carousel */
    .featured-section {
        margin-bottom: 60px;
    }

    .carousel-item {
        height: 500px;
        position: relative;
    }

    .carousel-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 50%, rgba(0, 0, 0, 0.1) 100%);
        z-index: 1;
    }

    .carousel-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .carousel-caption {
        z-index: 2;
        bottom: 40px;
        left: 50px;
        right: 50px;
        text-align: left;
    }

    .carousel-caption h3 {
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    .carousel-caption .badge {
        background-color: var(--primary-color);
        font-size: 0.9rem;
        padding: 8px 15px;
        margin-bottom: 10px;
    }

    /* Blog Cards */
    .blog-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
    }

    .blog-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(222, 182, 102, 0.3);
    }

    .blog-card-img {
        height: 250px;
        overflow: hidden;
        position: relative;
    }

    .blog-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .blog-card:hover .blog-card-img img {
        transform: scale(1.1);
    }

    .blog-category-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background-color: var(--primary-color);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        z-index: 2;
    }

    .blog-card-body {
        padding: 25px;
    }

    .blog-card-title {
        font-size: 1.4rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 15px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .blog-card-title:hover {
        color: var(--primary-color);
    }

    .blog-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 0.9rem;
        color: var(--text-muted);
        margin-bottom: 15px;
    }

    .blog-meta i {
        color: var(--primary-color);
    }

    .blog-description {
        color: var(--text-muted);
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .btn-read-more {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s;
        margin-top: 15px;
    }

    .btn-read-more:hover {
        background-color: var(--primary-dark);
        transform: translateX(5px);
    }

    /* Sidebar */
    .sidebar {
        position: sticky;
        top: 20px;
    }

    .sidebar-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .sidebar-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 3px solid var(--primary-color);
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        width: 100%;
        padding: 12px 45px 12px 20px;
        border: 2px solid var(--bg-light);
        border-radius: 25px;
        transition: all 0.3s;
    }

    .search-box input:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(222, 182, 102, 0.1);
    }

    .search-box button {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background-color: var(--primary-color);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        transition: all 0.3s;
    }

    .search-box button:hover {
        background-color: var(--primary-dark);
    }

    .category-list {
        list-style: none;
        padding: 0;
    }

    .category-item {
        padding: 12px 15px;
        margin-bottom: 10px;
        background-color: var(--bg-light);
        border-radius: 10px;
        transition: all 0.3s;
        cursor: pointer;
    }

    .category-item:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateX(5px);
    }

    .category-item i {
        margin-right: 10px;
        color: var(--primary-color);
    }

    .category-item:hover i {
        color: white;
    }

    .popular-post {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid var(--bg-light);
    }

    .popular-post:last-child {
        border-bottom: none;
    }

    .popular-post-img {
        width: 80px;
        height: 80px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .popular-post-content h6 {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 8px;
        line-height: 1.3;
    }

    .popular-post-content h6:hover {
        color: var(--primary-color);
    }

    .popular-post-meta {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    /* Section Title */
    .section-title {
        text-align: center;
        margin-bottom: 50px;
    }

    .section-title h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 15px;
    }

    .section-title .divider {
        width: 80px;
        height: 4px;
        background: linear-gradient(to right, var(--primary-color), var(--primary-dark));
        margin: 0 auto;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .blog-header h1 {
            font-size: 2.5rem;
        }

        .carousel-item {
            height: 350px;
        }

        .carousel-caption h3 {
            font-size: 1.5rem;
        }

        .sidebar {
            margin-top: 50px;
        }
    }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="blog-header">
        <div class="container text-center">
            <h1><i class="fas fa-blog me-3"></i>OceanPearl Blog</h1>
            <p>Khám phá những câu chuyện du lịch & trải nghiệm tuyệt vời tại Phú Quốc</p>
        </div>
    </div>

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
                    <img src="https://via.placeholder.com/1200x500/deb666/ffffff?text=No+Featured+Posts" alt="No posts">
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
                                    alt="<?= htmlspecialchars($blog['title']) ?>"
                                    onerror="this.src='https://via.placeholder.com/400x250/deb666/ffffff?text=No+Image'">
                                <span class="blog-category-badge">
                                    <?= htmlspecialchars($blog['category'] ?? 'Chưa phân loại') ?>
                                </span>
                            </div>
                            <div class="blog-card-body">
                                <h5 class="blog-card-title">
                                    <a href="blog-detail.php?slug=<?= htmlspecialchars($blog['slug']) ?>"
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

                                <a href="blog-detail.php?slug=<?= htmlspecialchars($blog['slug']) ?>"
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
                                alt="<?= htmlspecialchars($popular['title']) ?>" class="popular-post-img"
                                onerror="this.src='#'">
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $mysqli->close(); ?>