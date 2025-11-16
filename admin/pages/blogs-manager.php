<?php
// Xử lý CRUD
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$messageType = '';

// Tạo slug từ title
function createSlug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_blog'])) {
        $title = trim($_POST['title']);
        $slug = createSlug($title);
        $description = trim($_POST['description'] ?? '');
        $content = trim($_POST['content']);
        $category = trim($_POST['category'] ?? '');
        $status = $_POST['status'] ?? 'Draft';
        
        // Kiểm tra slug unique
        $checkStmt = $mysqli->prepare("SELECT blog_id FROM blog WHERE slug = ? AND deleted IS NULL");
        $checkStmt->bind_param("s", $slug);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $slug .= '-' . time();
        }
        $checkStmt->close();
        
        $stmt = $mysqli->prepare("INSERT INTO blog (title, slug, description, content, category, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $slug, $description, $content, $category, $status);
        
        if ($stmt->execute()) {
            $message = 'Thêm bài viết thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }
    
    if (isset($_POST['update_blog'])) {
        $blog_id = intval($_POST['blog_id']);
        $title = trim($_POST['title']);
        $slug = createSlug($title);
        $description = trim($_POST['description'] ?? '');
        $content = trim($_POST['content']);
        $category = trim($_POST['category'] ?? '');
        $status = $_POST['status'] ?? 'Draft';
        
        // Kiểm tra slug unique (trừ chính nó)
        $checkStmt = $mysqli->prepare("SELECT blog_id FROM blog WHERE slug = ? AND blog_id != ? AND deleted IS NULL");
        $checkStmt->bind_param("si", $slug, $blog_id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $slug .= '-' . time();
        }
        $checkStmt->close();
        
        $stmt = $mysqli->prepare("UPDATE blog SET title=?, slug=?, description=?, content=?, category=?, status=? WHERE blog_id=? AND deleted IS NULL");
        $stmt->bind_param("ssssssi", $title, $slug, $description, $content, $category, $status, $blog_id);
        
        if ($stmt->execute()) {
            $message = 'Cập nhật bài viết thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_blog'])) {
        $blog_id = intval($_POST['blog_id']);
        $stmt = $mysqli->prepare("UPDATE blog SET deleted = NOW() WHERE blog_id = ?");
        $stmt->bind_param("i", $blog_id);
        
        if ($stmt->execute()) {
            $message = 'Xóa bài viết thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }
}

// Lấy thông tin blog để edit
$editBlog = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM blog WHERE blog_id = ? AND deleted IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editBlog = $result->fetch_assoc();
    $stmt->close();
}

// Phân trang và tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Xây dựng query
$where = "WHERE b.deleted IS NULL";
$params = [];
$types = '';

if ($search) {
    $where .= " AND (b.title LIKE ? OR b.description LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam]);
    $types .= 'ss';
}

if ($status_filter) {
    $where .= " AND b.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($category_filter) {
    $where .= " AND b.category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

// Đếm tổng số
$countQuery = "SELECT COUNT(*) as total FROM blog b $where";
$countStmt = $mysqli->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalResult = $countStmt->get_result();
$total = $totalResult->fetch_assoc()['total'];
$countStmt->close();

// Lấy dữ liệu
$query = "SELECT * FROM blog b $where ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
    
$stmt = $mysqli->prepare($query);
if (!empty($params)) {
    $types .= 'ii';
    $params[] = $perPage;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$blogs = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Lấy danh sách categories
$categoriesResult = $mysqli->query("SELECT DISTINCT category FROM blog WHERE deleted IS NULL AND category IS NOT NULL AND category != '' ORDER BY category");
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);

// Thống kê
$statsResult = $mysqli->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Published' THEN 1 ELSE 0 END) as published,
    SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) as draft,
    SUM(view_count) as total_views
    FROM blog WHERE deleted IS NULL");
$stats = $statsResult->fetch_assoc();

// Build base URL for pagination
$baseUrl = "index.php?page=blogs-manager";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urlencode($status_filter);
if ($category_filter) $baseUrl .= "&category=" . urlencode($category_filter);
?>


<div class="main-content">
    <div class="content-header">
        <h1>Quản Lý Nội Dung</h1>
        <ul class="nav nav-pills mb-3" id="contentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="blog-tab" data-bs-toggle="pill" data-bs-target="#blog-panel"
                    type="button" role="tab">
                    Bài Viết & Tin Tức
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="review-tab" data-bs-toggle="pill" data-bs-target="#review-panel"
                    type="button" role="tab">
                    Đánh Giá & Review
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <!-- Blog Panel -->
        <div class="tab-pane fade show active" id="blog-panel" role="tabpanel">
            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-label">Tổng Bài Viết</div>
                    <div class="stat-value">48</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-label">Đã Xuất Bản</div>
                    <div class="stat-value">35</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="stat-label">Bản Nháp</div>
                    <div class="stat-value">13</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-label">Lượt Xem</div>
                    <div class="stat-value">12.5K</div>
                </div>
            </div>

            <!-- Content Card -->
            <div class="content-card">
                <div class="card-header-custom">
                    <h3 class="card-title">Danh Sách Bài Viết</h3>
                    <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addBlogModal">
                        <i class="fas fa-plus"></i> Thêm Bài Viết Mới
                    </button>
                </div>

                <!-- Filter -->
                <div class="filter-section">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Tìm kiếm bài viết..." />
                    </div>
                    <select class="form-select" style="width: 200px">
                        <option value="">Tất cả trạng thái</option>
                        <option value="published">Đã xuất bản</option>
                        <option value="draft">Bản nháp</option>
                    </select>
                    <select class="form-select" style="width: 200px">
                        <option value="">Tất cả danh mục</option>
                        <option value="news">Tin tức</option>
                        <option value="promotion">Khuyến mãi</option>
                        <option value="guide">Hướng dẫn</option>
                        <option value="event">Sự kiện</option>
                    </select>
                </div>

                <!-- Blog Items -->
                <div class="content-item">
                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400" alt="Blog"
                        class="content-thumbnail" />
                    <div class="content-details">
                        <div class="content-title">
                            Khám Phá Những Trải Nghiệm Tuyệt Vời Tại OceanPearl Hotel
                        </div>
                        <div class="content-meta">
                            <span><i class="fas fa-user"></i> Admin</span>
                            <span><i class="fas fa-calendar"></i> 25/10/2025</span>
                            <span><i class="fas fa-eye"></i> 1,250 lượt xem</span>
                            <span class="category-badge">Tin tức</span>
                            <span class="badge-status badge-published">Đã xuất bản</span>
                        </div>
                        <div class="content-excerpt">
                            Khách sạn OceanPearl mang đến cho bạn những trải nghiệm nghỉ
                            dưỡng đẳng cấp với hệ thống phòng hiện đại, dịch vụ chuyên
                            nghiệp và vị trí thuận lợi ngay trung tâm thành phố...
                        </div>
                        <div class="content-actions">
                            <button class="btn-sm-custom view" data-bs-toggle="modal" data-bs-target="#viewBlogModal"
                                onclick="loadBlogPreview(1)">
                                <i class="fas fa-eye"></i> Xem
                            </button>
                            <button class="btn-sm-custom edit" onclick="editBlog(1)">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <button class="btn-sm-custom delete" onclick="deleteBlog(1)">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                    </div>
                </div>

                <div class="content-item">
                    <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=400" alt="Blog"
                        class="content-thumbnail" />
                    <div class="content-details">
                        <div class="content-title">
                            Ưu Đãi Đặc Biệt Mùa Thu - Giảm Giá Lên Đến 30%
                        </div>
                        <div class="content-meta">
                            <span><i class="fas fa-user"></i> Admin</span>
                            <span><i class="fas fa-calendar"></i> 20/10/2025</span>
                            <span><i class="fas fa-eye"></i> 2,850 lượt xem</span>
                            <span class="category-badge">Khuyến mãi</span>
                            <span class="badge-status badge-published">Đã xuất bản</span>
                        </div>
                        <div class="content-excerpt">
                            Chào mừng mùa thu, OceanPearl Hotel tri ân khách hàng với
                            chương trình ưu đãi đặc biệt. Giảm giá lên đến 30% cho tất cả
                            các loại phòng khi đặt từ 3 đêm trở lên...
                        </div>
                        <div class="content-actions">
                            <button class="btn-sm-custom view" data-bs-toggle="modal" data-bs-target="#viewBlogModal"
                                onclick="loadBlogPreview(2)">
                                <i class="fas fa-eye"></i> Xem
                            </button>
                            <button class="btn-sm-custom edit" onclick="editBlog(2)">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <button class="btn-sm-custom delete" onclick="deleteBlog(2)">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                    </div>
                </div>

                <div class="content-item">
                    <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=400" alt="Blog"
                        class="content-thumbnail" />
                    <div class="content-details">
                        <div class="content-title">
                            Hướng Dẫn Check-in Online Nhanh Chóng
                        </div>
                        <div class="content-meta">
                            <span><i class="fas fa-user"></i> Admin</span>
                            <span><i class="fas fa-calendar"></i> 15/10/2025</span>
                            <span><i class="fas fa-eye"></i> 980 lượt xem</span>
                            <span class="category-badge">Hướng dẫn</span>
                            <span class="badge-status badge-draft">Bản nháp</span>
                        </div>
                        <div class="content-excerpt">
                            Tiết kiệm thời gian với hệ thống check-in online của
                            OceanPearl Hotel. Chỉ với vài thao tác đơn giản, bạn có thể
                            hoàn tất thủ tục và nhận phòng ngay khi đến...
                        </div>
                        <div class="content-actions">
                            <button class="btn-sm-custom view" data-bs-toggle="modal" data-bs-target="#viewBlogModal"
                                onclick="loadBlogPreview(3)">
                                <i class="fas fa-eye"></i> Xem
                            </button>
                            <button class="btn-sm-custom edit" onclick="editBlog(3)">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <button class="btn-sm-custom delete" onclick="deleteBlog(3)">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Panel -->
        <div class="tab-pane fade" id="review-panel" role="tabpanel">
            <!-- Stats -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-label">Tổng Đánh Giá</div>
                    <div class="stat-value">185</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-label">Đánh Giá TB</div>
                    <div class="stat-value">4.8</div>
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
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">NA</div>
                            <div>
                                <div class="reviewer-name">Nguyễn Văn A</div>
                                <div class="review-date">25/10/2025 - Phòng 301 Suite</div>
                            </div>
                        </div>
                        <div>
                            <div class="rating-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <div class="review-content">
                        Khách sạn rất tuyệt vời! Phòng ốc sạch sẽ, rộng rãi và trang bị
                        đầy đủ tiện nghi. Nhân viên phục vụ nhiệt tình, chu đáo. Vị trí
                        thuận lợi ngay trung tâm. Tôi và gia đình rất hài lòng với
                        chuyến nghỉ dưỡng này. Chắc chắn sẽ quay lại!
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

                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">LC</div>
                            <div>
                                <div class="reviewer-name">Lê Văn C</div>
                                <div class="review-date">
                                    18/10/2025 - Phòng 101 Standard
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="rating-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <div class="review-content">
                        Trải nghiệm tuyệt vời! Phòng sạch sẽ, nhân viên thân thiện. Giá
                        cả hợp lý. Sẽ giới thiệu cho bạn bè và người thân. Cảm ơn
                        OceanPearl Hotel!
                    </div>
                    <div class="review-actions">
                        <button class="btn-sm-custom view">
                            <i class="fas fa-eye"></i> Xem chi tiết
                        </button>
                        <button class="btn-sm-custom delete" onclick="deleteReview(3)">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add/Edit Blog -->
<div class="modal fade" id="addBlogModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Thêm Bài Viết Mới
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="blogForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Tiêu Đề Bài Viết *</label>
                                <input type="text" class="form-control" placeholder="Nhập tiêu đề bài viết..."
                                    required />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mô Tả Ngắn</label>
                                <textarea class="form-control" rows="3"
                                    placeholder="Mô tả ngắn về bài viết (hiển thị trong danh sách)"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nội Dung *</label>
                                <textarea id="blogEditor"></textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Trạng Thái *</label>
                                <select class="form-select" required>
                                    <option value="draft">Bản nháp</option>
                                    <option value="published">Xuất bản ngay</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Danh Mục *</label>
                                <select class="form-select" required>
                                    <option value="">Chọn danh mục</option>
                                    <option value="news">Tin tức</option>
                                    <option value="promotion">Khuyến mãi</option>
                                    <option value="guide">Hướng dẫn</option>
                                    <option value="event">Sự kiện</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tác Giả</label>
                                <input type="text" class="form-control" value="Admin" readonly />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ngày Xuất Bản</label>
                                <input type="date" class="form-control" value="2025-11-02" />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ảnh Đại Diện *</label>
                                <div class="image-upload-area" onclick="document.getElementById('blogImage').click()">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Click để chọn ảnh</p>
                                    <small class="text-muted">hoặc kéo thả ảnh vào đây</small>
                                </div>
                                <input type="file" id="blogImage" accept="image/*" style="display: none"
                                    onchange="previewImage(this, 'blogPreview')" />
                                <img id="blogPreview" class="image-preview" style="display: none" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Hủy
                </button>
                <button type="button" class="btn-primary-custom" onclick="saveBlog()">
                    <i class="fas fa-save"></i> Lưu Bài Viết
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Modal: View Blog Details -->
<div class="modal fade" id="viewBlogModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-newspaper"></i> Chi Tiết Bài Viết
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 80vh; overflow-y: auto">
                <!-- Blog Preview Content -->
                <div id="blogPreviewContent">
                    <!-- Content will be loaded dynamically -->
                    <div class="blog-preview-header">
                        <span class="blog-preview-category" id="previewCategory">Tin tức</span>
                        <h1 class="blog-preview-title" id="previewTitle">
                            Khám Phá Những Trải Nghiệm Tuyệt Vời Tại OceanPearl Hotel
                        </h1>
                        <div class="blog-preview-meta">
                            <span><i class="fas fa-user"></i>
                                <span id="previewAuthor">Admin</span></span>
                            <span><i class="fas fa-calendar"></i>
                                <span id="previewDate">25/10/2025</span></span>
                            <span><i class="fas fa-eye"></i>
                                <span id="previewViews">1,250</span> lượt xem</span>
                        </div>
                    </div>

                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200" alt="Blog"
                        class="blog-preview-image" id="previewImage" />

                    <div class="blog-preview-body" id="previewBody">
                        <p>
                            <strong>Chào mừng bạn đến với OceanPearl Hotel</strong> - điểm
                            đến lý tưởng cho những ai đang tìm kiếm một kỳ nghỉ dưỡng đẳng
                            cấp tại trung tâm thành phố. Với vị trí thuận lợi, thiết kế
                            hiện đại và dịch vụ chuyên nghiệp, chúng tôi cam kết mang đến
                            cho bạn những trải nghiệm khó quên.
                        </p>

                        <h2>Phòng Nghỉ Đẳng Cấp</h2>
                        <p>
                            OceanPearl Hotel tự hào sở hữu 48 phòng được thiết kế tinh tế,
                            kết hợp giữa phong cách hiện đại và sự thoải mái tối đa. Mỗi
                            phòng đều được trang bị đầy đủ tiện nghi cao cấp bao gồm:
                        </p>

                        <ul>
                            <li>Giường king-size/queen-size với nệm cao cấp</li>
                            <li>
                                TV màn hình phẳng 55 inch với kênh truyền hình quốc tế
                            </li>
                            <li>Hệ thống điều hòa không khí thông minh</li>
                            <li>Minibar với đầy đủ đồ uống cao cấp</li>
                            <li>Két sắt an toàn</li>
                            <li>Wifi tốc độ cao miễn phí</li>
                        </ul>
                        <div class="blog-preview-tags">
                            <strong>Tags:</strong>
                            <span class="blog-preview-tag">Khách sạn</span>
                            <span class="blog-preview-tag">Du lịch</span>
                            <span class="blog-preview-tag">Nghỉ dưỡng</span>
                            <span class="blog-preview-tag">OceanPearl</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Đóng
                    </button>
                    <button type="button" class="btn-primary-custom" onclick="editFromPreview()">
                        <i class="fas fa-edit"></i> Chỉnh Sửa
                    </button>
                </div>
            </div>
        </div>
    </div>