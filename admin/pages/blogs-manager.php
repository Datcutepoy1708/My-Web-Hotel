<?php
// Xử lý CRUD
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$messageType = '';

// Tạo slug từ title
function createSlug($title)
{
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
$pageNum = isset($_GET['pageNum']) ? intval($_GET['pageNum']) : 1;
$pageNum = max(1, $pageNum); // Đảm bảo pageNum >= 1
$perPage = 2;
$offset = ($pageNum - 1) * $perPage;

// Xây dựng WHERE clause
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

// Lấy dữ liệu - FIX: Hardcode LIMIT và OFFSET
$query = "SELECT * FROM blog b 
    $where 
    ORDER BY b.created_at DESC 
    LIMIT $perPage OFFSET $offset";

$stmt = $mysqli->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $blogs = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Lỗi query: " . $stmt->error);
}
$stmt->close();

// Đếm tổng số đánh giá


// Lấy danh sách categories
$categoriesResult = $mysqli->query("SELECT DISTINCT category FROM blog WHERE deleted IS NULL AND category IS NOT NULL AND category != '' ORDER BY category");
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);


// Thống kê bài viết 
$statsResult = $mysqli->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Published' THEN 1 ELSE 0 END) as published,
    SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) as draft,
    SUM(view_count) as total_views
    FROM blog WHERE deleted IS NULL");
$stats = $statsResult->fetch_assoc();

// Đếm tổng số review 
$countQuery1 = "SELECT COUNT(*) as total FROM review b $where";
$countStmt1 = $mysqli->prepare($countQuery1);
if (!empty($params)) {
    $countStmt1->bind_param($types, ...$params);
}
$countStmt1->execute();
$totalResultReview = $countStmt1->get_result();
$totalReview = $totalResultReview->fetch_assoc()['total'];
$countStmt1->close();

// Lấy ra thông tin tổng số các đánh giá và trung bình rating
$statsResultReview = $mysqli->query("SELECT 
    COUNT(*) as total,
    ROUND(AVG(rating), 1) as avg_rating
    FROM review WHERE deleted IS NULL");
$reviewCount = $statsResultReview->fetch_assoc();

// Lây dữ liệu review
$reviews = [];
if ($totalReview > 0) {
    $query = "SELECT r.*,c.full_name,d.room_number,rt.room_type_name
     FROM review r
    INNER JOIN booking b ON r.booking_id=b.booking_id
    INNER JOIN customer c ON c.customer_id=b.customer_id
    INNER JOIN room d ON d.room_id=b.room_id
    INNER JOIN room_type rt ON rt.room_type_id= d.room_type_id
    $where
    GROUP BY r.review_id
    ORDER BY r.created_at DESC
    LIMIT $perPage OFFSET $offset
    ";
    $result = $mysqli->query($query);
    if (!$result) {
        error_log("Query Error: " . $mysqli->error);
        $message = "Lỗi truy vấn: " . $mysqli->error;
        $messageType = 'danger';
    } else {
        $reviews = $result->fetch_all(MYSQLI_ASSOC);
    }
}

?>
<div class="main-content">
    <div class="content-header">
        <h1>Quản Lý Nội Dung</h1>
        <?php
            $current_panel = isset($panel) ? $panel : (isset($_GET['panel']) ? $_GET['panel'] : 'blog-panel');
        ?>
        <ul class="nav nav-pills mb-3" id="contentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="<?php echo ($current_panel=='blog-panel') ? 'nav-link active' : 'nav-link'; ?>"
                    href="/My-Web-Hotel/admin/index.php?page=blogs-manager&panel=blog-panel">
                    <span>Bài Viết & Tin Tức</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="<?php echo ($current_panel=='review-panel') ? 'nav-link active' : 'nav-link'; ?>"
                    href="/My-Web-Hotel/admin/index.php?page=blogs-manager&panel=review-panel">
                    <span>Đánh Giá & Review</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <?php
            $panel = isset($_GET['panel']) ? trim($_GET['panel']) : 'blog-panel';
            $panelAllowed = [
                'blog-panel' => 'pages/blog-panel.php',
                'review-panel' => 'pages/review-panel.php',
            ];
            if (isset($panelAllowed[$panel])) {
                include $panelAllowed[$panel];
            } else {
                include 'pages/404.php';
            }  
        ?>
    </div>
</div>


<script>
function editBlog(id) {
    window.location.href = 'index.php?page=blogs-manager&action=edit&id=' + id;
}

function deleteBlog(id) {
    if (confirm('Bạn có chắc chắn muốn xóa bài viết này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="blog_id" value="' + id + '">' +
            '<input type="hidden" name="delete_blog" value="1">';
        document.body.appendChild(form);
        form.submit();
    }
}

<?php if ($editBlog): ?>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('addBlogModal'));
    modal.show();
});
<?php endif; ?>
</script>