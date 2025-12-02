<?php
require_once '../includes/connect.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$categories = isset($_GET['categories']) ? $_GET['categories'] : [];

// Câu SQL cơ bản
$sql = "SELECT * FROM service WHERE status = 'Active'";

$params = [];
$types = "";

// Lọc theo danh mục (service_type)
if (!empty($categories)) {
    $placeholders = str_repeat('?,', count($categories) - 1) . '?';
    $sql .= " AND service_type IN ($placeholders)";
    foreach ($categories as $cat) {
        $params[] = $cat;
        $types .= "s";
    }
}

// Nếu có từ khóa => tìm theo tên dịch vụ hoặc loại hình
if ($keyword !== '') {
    $sql .= " AND (service_name LIKE ? OR service_type LIKE ?)";
    $like = "%$keyword%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

// Sắp xếp
switch ($sort) {
    case 'price-low':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price-high':
        $sql .= " ORDER BY price DESC";
        break;
    case 'popular':
    default:
        $sql .= " ORDER BY service_id DESC";
        break;
}

// Chuẩn bị câu SQL
$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    die("Lỗi SQL: " . $mysqli->error);
}

// Gắn giá trị nếu có
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Xác định hình ảnh theo loại hình
        switch ($row["service_type"]) {
            case 'Health':
                $hinhAnh = "https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=400";
                break;
            case 'Food':
                $hinhAnh = "https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=400";
                break;
            case 'Entertainment':
                $hinhAnh = "https://images.unsplash.com/photo-1571902943202-507ec2618e8f?w=400";
                break;
            case 'Event':
                $hinhAnh = "https://images.unsplash.com/photo-1511578314322-379afb476865?w=400";
                break;
            default:
                $hinhAnh = "https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=400";
        }

        echo '<div class="card" style="max-width: 90%">';
        echo '  <div class="row g-0">';
        echo '    <div class="picture col-md-4">';
        echo '      <img src="' . $hinhAnh . '" class="img-fluid rounded-start service-image" alt="' . htmlspecialchars($row["service_name"]) . '" />';
        echo '    </div>';
        echo '    <div class="information col-md-5">';
        echo '      <div class="card-body">';
        echo '        <h3 class="card-title">' . htmlspecialchars($row["service_name"]) . '</h3>';
        echo '        <p><strong>Thời gian:</strong> 8:00 - 24:00</p>';
        echo '        <p><strong>Loại hình:</strong> ' . htmlspecialchars($row["service_type"]) . '</p>';
        echo '        <p><strong>Đơn vị:</strong> ' . htmlspecialchars($row["unit"]) . '</p>';
        echo '      </div>';
        echo '    </div>';
        echo '    <div class="money col-md-3">';
        echo '      <h2>' . number_format($row["price"], 0, ',', '.') . '₫</h2>';
        echo "      <a href='/My-Web-Hotel/client/index.php?page=service-detail&id=" . $row['service_id'] . "' class='view-all'>Chi tiết</a>";
        echo '    </div>';
        echo '  </div>';
        echo '</div>';
    }
} else {
    echo '<p>Không có dịch vụ nào phù hợp với tìm kiếm.</p>';
}

$stmt->close();
$mysqli->close();
?>