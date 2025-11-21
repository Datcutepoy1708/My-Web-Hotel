<?php
// Xử lý CRUD
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$messageType = '';

// Xử lý Room Type
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_room_type'])) {
    $room_type_name = trim($_POST['room_type_name']);
    $description = trim($_POST['description'] ?? '');
    $base_price = floatval($_POST['base_price']);
    $capacity = intval($_POST['capacity']);
    $amenities = trim($_POST['amenities'] ?? '');
    $area = floatval($_POST['area'] ?? 0);

    $stmt = $mysqli->prepare("INSERT INTO room_type (room_type_name, description, base_price, capacity, amenities, area) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiss", $room_type_name, $description, $base_price, $capacity, $amenities, $area);

    if ($stmt->execute()) {
        $message = 'Thêm loại phòng thành công!';
        $messageType = 'success';
    } else {
        $message = 'Lỗi: ' . $stmt->error;
        $messageType = 'danger';
    }
    $stmt->close();
}

// Xử lý Room
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_room'])) {
        $room_number = trim($_POST['room_number']);
        $floor = intval($_POST['floor']);
        $room_type_id = intval($_POST['room_type_id']);
        $status = $_POST['status'] ?? 'Available';

        $stmt = $mysqli->prepare("INSERT INTO room (room_number, floor, room_type_id, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siis", $room_number, $floor, $room_type_id, $status);

        if ($stmt->execute()) {
            $message = 'Thêm phòng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }

    if (isset($_POST['update_room'])) {
        $room_id = intval($_POST['room_id']);
        $room_number = trim($_POST['room_number']);
        $floor = intval($_POST['floor']);
        $room_type_id = intval($_POST['room_type_id']);
        $status = $_POST['status'] ?? 'Available';

        $stmt = $mysqli->prepare("UPDATE room SET room_number=?, floor=?, room_type_id=?, status=? WHERE room_id=? AND deleted IS NULL");
        $stmt->bind_param("siisi", $room_number, $floor, $room_type_id, $status, $room_id);

        if ($stmt->execute()) {
            $message = 'Cập nhật phòng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }

    if (isset($_POST['delete_room'])) {
        $room_id = intval($_POST['room_id']);
        $stmt = $mysqli->prepare("UPDATE room SET deleted = NOW() WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);

        if ($stmt->execute()) {
            $message = 'Xóa phòng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }
}

// Lấy thông tin phòng để edit
$editRoom = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM room WHERE room_id = ? AND deleted IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editRoom = $result->fetch_assoc();
    $stmt->close();
}

// Phân trang và tìm kiếm
$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$type_filter = intval($_GET['type'] ?? 0);
$pageNum = isset($_GET['pageNum']) ? intval($_GET['pageNum']) : 1;
$pageNum = max(1, $pageNum);
$perPage = 5;
$offset = ($pageNum - 1) * $perPage;

// Xây dựng query
$where = "WHERE r.deleted IS NULL";
$params = [];
$types = '';

if ($search) {
    $where .= " AND (r.room_number LIKE ? OR rt.room_type_name LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam]);
    $types .= 'ss';
}

if ($status_filter) {
    $where .= " AND r.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($type_filter) {
    $where .= " AND r.room_type_id = ?";
    $params[] = $type_filter;
    $types .= 'i';
}

// Đếm tổng số
$countQuery = "SELECT COUNT(*) as total FROM room r 
    LEFT JOIN room_type rt ON r.room_type_id = rt.room_type_id 
    $where";
$countStmt = $mysqli->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalResult = $countStmt->get_result();
$total = $totalResult->fetch_assoc()['total'];
$countStmt->close();

// Lấy dữ liệu
$query = "SELECT r.*, rt.room_type_name, rt.base_price, rt.capacity, rt.area, rt.amenities 
    FROM room r 
    LEFT JOIN room_type rt ON r.room_type_id = rt.room_type_id 
    $where
    ORDER BY r.room_number ASC 
    LIMIT $perPage OFFSET $offset";

$stmt = $mysqli->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
if ($stmt->execute()) {
    $result = $stmt->get_result();
    $rooms = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Lỗi query: " . $stmt->error);
}
$stmt->close();

// lấy ra tổng số loại phòng
$countQueryRoomTypes = "SELECT COUNT(*) as total FROM room_type r 
    $where";
$countStmtRoomTypes = $mysqli->prepare($countQueryRoomTypes);
if (!empty($params)) {
    $countStmtRoomTypes->bind_param($types, ...$params);
}
$countStmtRoomTypes->execute();
$totalResultRoomTypes = $countStmtRoomTypes->get_result();
$totalRoomTypes = $totalResultRoomTypes->fetch_assoc()['total'];
$countStmtRoomTypes->close();


// Lấy danh sách room types cho room-manager
$roomTypesResult = $mysqli->query("SELECT * FROM room_type WHERE deleted IS NULL ");

$roomTypes = $roomTypesResult->fetch_all(MYSQLI_ASSOC);

//Lây danh sách room types cho room_type
$queryRoomTypes = "SELECT r.* FROM room_type r $where LIMIT $perPage OFFSET $offset";
$stmtRoomTypes = $mysqli->prepare($queryRoomTypes);
if (!empty($params)) {
    $stmtRoomTypes->bind_param($types, ...$params);
}
if ($stmtRoomTypes->execute()) {
    $result = $stmtRoomTypes->get_result();
    $roomTypesAll = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Lỗi query: " . $stmtRoomTypes->error);
}
$stmtRoomTypes->close();



// Build base URL for pagination
$baseUrl = "index.php?page=room-manager&panel=room-panel";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urlencode($status_filter);
if ($type_filter) $baseUrl .= "&type=" . $type_filter;


?>

<div class="main-content">
    <div class="content-header">
        <h1>Quản Lý Phòng</h1>
        <?php
        $current_panel = isset($panel) ? $panel : (isset($_GET['panel']) ? $_GET['panel'] : 'room-panel');
        ?>
        <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="<?php echo ($current_panel == 'room-panel') ? 'nav-link active' : 'nav-link'; ?>"
                    href="/My-Web-Hotel/admin/index.php?page=room-manager&panel=room-panel">
                    <span>Phòng</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="<?php echo ($current_panel == 'roomType-panel') ? 'nav-link active' : 'nav-link'; ?>"
                    href="/My-Web-Hotel/admin/index.php?page=room-manager&panel=roomType-panel">
                    <span>Loại Phòng</span>
                </a>
            </li>
        </ul>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo h($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- content -->
    <div class="tab-content">
        <?php
        $panel = isset($_GET['panel']) ? trim($_GET['panel']) : 'room-panel';
        $panelAllowed = [
            'room-panel' => 'pages/room-panel.php',
            'roomType-panel' => 'pages/roomType-panel.php',
        ];
        if (isset($panelAllowed[$panel])) {
            include $panelAllowed[$panel];
        } else {
            include 'pages/404.php';
        }
        ?>

    </div>
    <script>
        function editRoom(id) {
            window.location.href = 'index.php?page=room-manager&action=edit&id=' + id;
        }

        function deleteRoom(id) {
            if (confirm('Bạn có chắc chắn muốn xóa phòng này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="room_id" value="' + id + '">' +
                    '<input type="hidden" name="delete_room" value="1">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        <?php if ($editRoom): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = new bootstrap.Modal(document.getElementById('addRoomModal'));
                modal.show();
            });
        <?php endif; ?>
    </script>