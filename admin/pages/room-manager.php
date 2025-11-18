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
$page = isset($_GET['pageNum']) ? intval($_GET['pageNum']) : 1;
$page = max(1, $page); // Đảm bảo page >= 1
$perPage = 5;
$offset = ($page - 1) * $perPage;

// Xây dựng WHERE clause
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

// Lấy dữ liệu - LIMIT và OFFSET hardcode vào query
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

// Lấy danh sách room types
$roomTypesResult = $mysqli->query("SELECT * FROM room_type WHERE deleted IS NULL ORDER BY room_type_name");
$roomTypes = $roomTypesResult->fetch_all(MYSQLI_ASSOC);

// Build base URL for pagination
$baseUrl = "index.php?page=room-manager";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urlencode($status_filter);
if ($type_filter) $baseUrl .= "&type=" . $type_filter;
?>
<div class="main-content">
    <div class="content-header">
        <h1>Quản Lý Phòng</h1>
        <div class="row m-3">
            <div class="col-md-12">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomTypeModal">
                    <i class="fas fa-plus"></i> Thêm Loại Phòng
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                    <i class="fas fa-plus"></i> Thêm Phòng
                </button>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo h($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter và Search -->
    <div class="tab-content">
        <div class="filter-section">
            <form method="GET" action="">
                <input type="hidden" name="page" value="room-manager">
                <div class="row">
                    <div class="col-md-4">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Tìm kiếm phòng..."
                                value="<?php echo h($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="Available" <?php echo $status_filter == 'Available' ? 'selected' : ''; ?>>Có
                                sẵn</option>
                            <option value="Booked" <?php echo $status_filter == 'Booked' ? 'selected' : ''; ?>>Đã đặt
                            </option>
                            <option value="Occupied" <?php echo $status_filter == 'Occupied' ? 'selected' : ''; ?>>Đang
                                thuê</option>
                            <option value="Maintenance"
                                <?php echo $status_filter == 'Maintenance' ? 'selected' : ''; ?>>Bảo trì</option>
                            <option value="Cleaning" <?php echo $status_filter == 'Cleaning' ? 'selected' : ''; ?>>Đang
                                dọn</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="type">
                            <option value="0">Tất cả loại phòng</option>
                            <?php foreach ($roomTypes as $rt): ?>
                                <option value="<?php echo $rt['room_type_id']; ?>"
                                    <?php echo $type_filter == $rt['room_type_id'] ? 'selected' : ''; ?>>
                                    <?php echo h($rt['room_type_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bảng danh sách phòng -->
        <div class="table-container">
            <table class="table table-hover" id="roomsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Số Phòng</th>
                        <th>Tầng</th>
                        <th>Loại Phòng</th>
                        <th>Giá/Đêm</th>
                        <th>Trạng Thái</th>
                        <th>Diện Tích</th>
                        <th>Sức chứa</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="9" class="text-center">Không có dữ liệu</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><?php echo $room['room_id']; ?></td>
                                <td><?php echo h($room['room_number']); ?></td>
                                <td><?php echo $room['floor']; ?></td>
                                <td>
                                    <?php
                                    $statusClass = 'bg-secondary';
                                    $roomText = $room['room_type_name'];
                                    switch ($room['room_type_name']) {
                                        case 'Budget Room':
                                            $statusClass = 'bg-success';
                                            $roomText = 'Budget Room';
                                            break;
                                        case 'Family Room':
                                            $statusClass = 'bg-warning';
                                            $roomText = 'Family Room';
                                            break;
                                        case 'Suite Room':
                                            $statusClass = 'bg-danger';
                                            $roomText = 'Suite Room';
                                            break;
                                        case 'Deluxe Room':
                                            $statusClass = 'bg-dark';
                                            $roomText = 'Deluxe Room';
                                            break;
                                        case 'Standard Room':
                                            $statusClass = 'bg-info';
                                            $roomText = 'Standard Room';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $roomText; ?></span>
                                </td>
                                <td><?php echo formatCurrency($room['base_price'] ?? 0); ?></td>
                                <td>
                                    <?php
                                    $statusClass = 'bg-secondary';
                                    $statusText = $room['status'];
                                    switch ($room['status']) {
                                        case 'Available':
                                            $statusClass = 'bg-success';
                                            $statusText = 'Có sẵn';
                                            break;
                                        case 'Booked':
                                            $statusClass = 'bg-warning';
                                            $statusText = 'Đã đặt';
                                            break;
                                        case 'Occupied':
                                            $statusClass = 'bg-danger';
                                            $statusText = 'Đang thuê';
                                            break;
                                        case 'Maintenance':
                                            $statusClass = 'bg-dark';
                                            $statusText = 'Bảo trì';
                                            break;
                                        case 'Cleaning':
                                            $statusClass = 'bg-info';
                                            $statusText = 'Đang dọn';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                </td>
                                <td><?php echo $room['area'] ? $room['area'] . 'm²' : '-'; ?></td>
                                <td><?php echo $room['capacity'] ?? '-'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                        data-bs-target="#viewRoomModal<?php echo $room['room_id']; ?>" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning"
                                        onclick="editRoom(<?php echo $room['room_id']; ?>)" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteRoom(<?php echo $room['room_id']; ?>)" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- View Modal -->
                            <div class="modal fade" id="viewRoomModal<?php echo $room['room_id']; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Chi Tiết Phòng</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <?php if ($room['image']): ?>
                                                        <img src="<?php echo h($room['image']); ?>" class="img-fluid rounded"
                                                            alt="Room Image">
                                                    <?php else: ?>
                                                        <img src="https://via.placeholder.com/400x300" class="img-fluid rounded"
                                                            alt="Room Image">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <h4>Phòng <?php echo h($room['room_number']); ?></h4>
                                                    <p><strong>Loại phòng:</strong>
                                                        <?php echo h($room['room_type_name'] ?? '-'); ?></p>
                                                    <p><strong>Tầng:</strong> <?php echo $room['floor']; ?></p>
                                                    <p><strong>Giá/đêm:</strong>
                                                        <?php echo formatCurrency($room['base_price'] ?? 0); ?></p>
                                                    <p><strong>Diện tích:</strong>
                                                        <?php echo $room['area'] ? $room['area'] . 'm²' : '-'; ?></p>
                                                    <p><strong>Sức chứa:</strong> <?php echo $room['capacity'] ?? '-'; ?> người
                                                    </p>
                                                    <p><strong>Trạng thái:</strong>
                                                        <span
                                                            class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                    </p>
                                                    <?php if ($room['amenities']): ?>
                                                        <p><strong>Tiện nghi:</strong> <?php echo h($room['amenities']); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Đóng</button>
                                            <button type="button" class="btn btn-primary"
                                                onclick="editRoom(<?php echo $room['room_id']; ?>)">
                                                Chỉnh Sửa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php echo getPagination($total, $perPage, $page, $baseUrl); ?>
</div>

<!-- Modal Thêm Loại Phòng -->
<div class="modal fade" id="addRoomTypeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Loại Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên loại phòng *</label>
                        <input type="text" class="form-control" name="room_type_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giá cơ bản (VNĐ) *</label>
                            <input type="number" class="form-control" name="base_price" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sức chứa (người) *</label>
                            <input type="number" class="form-control" name="capacity" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Diện tích (m²)</label>
                            <input type="number" class="form-control" name="area" step="0.01">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tiện nghi</label>
                            <input type="text" class="form-control" name="amenities"
                                placeholder="VD: WiFi, TV, Điều hòa...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" name="add_room_type">Thêm Loại Phòng</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Phòng -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRoomModalLabel"><?php echo $editRoom ? 'Sửa ' : 'Thêm ' ?>Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="roomForm" method="POST">
                    <?php if ($editRoom): ?>
                        <input type="hidden" name="room_id" value="<?php echo $editRoom['room_id']; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="roomCode" class="form-label">Số Phòng *</label>
                                <input type="text" class="form-control" name="room_number" placeholder="VD: P001, P002..."
                                    value="<?php echo h($editRoom['room_number'] ?? ''); ?>"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="roomCode" class="form-label">Tầng *</label>
                                <input type="text" class="form-control" name="floor" placeholder="VD: Tầng 1, tầng 2,...."
                                    value="<?php echo h($editRoom['floor'] ?? ''); ?>"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="roomType" class="form-label">Loại Phòng *</label>
                                <select class="form-select" name="room_type_id" required>
                                    <option value="">Chọn loại phòng</option>
                                    <?php foreach ($roomTypes as $rt): ?>
                                        <option value="<?php echo $rt['room_type_id']; ?>"
                                            <?php echo ($editRoom['room_type_id'] ?? '') == $rt['room_type_id'] ? 'selected' : ''; ?>>
                                            <?php echo h($rt['room_type_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="roomArea" class="form-label">Diện Tích (m²) *</label>
                                <input type="number" class="form-control" id="roomArea" name="area" placeholder="VD: 25, 35, 50..."
                                     value="<?php echo h($editRoom['area'] ?? ''); ?>"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="roomCapacity" class="form-label">Sức Chứa (người) *</label>
                                <input type="number" class="form-control" id="roomCapacity" placeholder="VD: 2, 4, 6..."
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="roomDescription" class="form-label">Mô Tả *</label>
                        <textarea class="form-control" id="roomDescription" rows="4" placeholder="Mô tả chi tiết về phòng, tiện nghi, view..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="roomImages" class="form-label">Hình Ảnh Phòng *</label>
                        <input type="file" class="form-control" id="roomImages" multiple accept="image/*" required>
                        <div class="form-text">Có thể chọn nhiều hình ảnh (JPG, PNG, JPEG)</div>
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="saveRoom()">Lưu Phòng</button>
            </div>
        </div>
    </div>
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