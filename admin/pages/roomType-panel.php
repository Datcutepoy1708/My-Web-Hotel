<?php



// Phân quyền riêng cho loại phòng
$canViewRoomType = function_exists('checkPermission') ? (checkPermission('roomType.view') || checkPermission('room.view')) : true;
$canCreateRoomType = function_exists('checkPermission') ? (checkPermission('roomType.create') || checkPermission('room.create')) : true;
$canEditRoomType = function_exists('checkPermission') ? (checkPermission('roomType.edit') || checkPermission('room.edit')) : true;
$canDeleteRoomType = function_exists('checkPermission') ? (checkPermission('roomType.delete') || checkPermission('room.delete')) : true;

if (!$canViewRoomType) {
    http_response_code(403);
    echo '<div class="alert alert-danger m-4">Bạn không có quyền xem loại phòng.</div>';
    return;
}

// Xử lý CRUD
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$messageType = '';

// Kiểm tra xem có file ảnh nào được chọn không (hỗ trợ cả single/multi)
function hasRoomTypeImages($files)
{
    if (!isset($files['name'])) {
        return false;
    }
    if (is_array($files['name'])) {
        return count(array_filter($files['name'], fn($name) => !empty($name))) > 0;
    }
    return !empty($files['name']);
}

// Hàm upload nhiều ảnh cho loại phòng lên Cloudinary, lưu vào roomtype_images
function uploadRoomTypeImages($files, $roomTypeId, $maxImages = 6)
{
    global $mysqli;

    if (!$roomTypeId || !hasRoomTypeImages($files)) {
        return [];
    }

    require_once __DIR__ . '/../includes/cloudinary_helper.php';

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    $uploaded = [];

    // Đếm số ảnh hiện có và lấy display_order tiếp theo
    $statsStmt = $mysqli->prepare("SELECT COUNT(*) AS total, COALESCE(MAX(display_order), -1) AS max_order FROM roomtype_images WHERE room_type_id = ?");
    $statsStmt->bind_param("i", $roomTypeId);
    $statsStmt->execute();
    $statsRow = $statsStmt->get_result()->fetch_assoc();
    $statsStmt->close();

    $existingCount = (int)($statsRow['total'] ?? 0);
    $nextOrder = ((int)($statsRow['max_order'] ?? -1)) + 1;
    $remainingSlots = max(0, $maxImages - $existingCount);

    if ($remainingSlots <= 0) {
        return [];
    }

    // Xử lý nhiều file upload
    if (is_array($files['name'])) {
        $fileCount = count($files['name']);
        for ($i = 0; $i < $fileCount && count($uploaded) < $remainingSlots; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK || empty($files['name'][$i])) {
                continue;
            }

            $type = $files['type'][$i];
            $size = $files['size'][$i];
            $tmp  = $files['tmp_name'][$i];

            if (!in_array($type, $allowedTypes) || $size > $maxSize) {
                continue;
            }

            // Upload lên Cloudinary
            $url = CloudinaryHelper::upload($tmp, 'room-type');
            if ($url === false) {
                error_log("Cloudinary upload failed for file: " . $files['name'][$i]);
                continue;
            }

            $uploaded[] = $url;

            // Lưu vào bảng roomtype_images
            $displayOrder = $nextOrder++;
            $isPrimary    = ($existingCount === 0 && count($uploaded) === 1) ? 1 : 0;

            $insertStmt = $mysqli->prepare(
                "INSERT INTO roomtype_images (room_type_id, image_url, display_order, is_primary) VALUES (?, ?, ?, ?)"
            );
            $insertStmt->bind_param("isii", $roomTypeId, $url, $displayOrder, $isPrimary);
            $insertStmt->execute();
            $insertStmt->close();
        }
    } else {
        // Single file
        if ($files['error'] === UPLOAD_ERR_OK && !empty($files['name'])) {
            $type = $files['type'];
            $size = $files['size'];
            $tmp  = $files['tmp_name'];

            if (in_array($type, $allowedTypes) && $size <= $maxSize && $remainingSlots > 0) {
                $url = CloudinaryHelper::upload($tmp, 'room-type');
                if ($url !== false) {
                    $uploaded[] = $url;

                    $displayOrder = $nextOrder++;
                    $isPrimary    = ($existingCount === 0) ? 1 : 0;

                    $insertStmt = $mysqli->prepare(
                        "INSERT INTO roomtype_images (room_type_id, image_url, display_order, is_primary) VALUES (?, ?, ?, ?)"
                    );
                    $insertStmt->bind_param("isii", $roomTypeId, $url, $displayOrder, $isPrimary);
                    $insertStmt->execute();
                    $insertStmt->close();
                } else {
                    error_log("Cloudinary upload failed for single file: " . $files['name']);
                }
            }
        }
    }

    return $uploaded;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['add_room_type']) && $canCreateRoomType) {
        $room_type_name = trim($_POST['room_type_name']);
        $description = trim($_POST['description'] ?? '');
        $base_price = floatval($_POST['base_price']);
        $capacity = intval($_POST['capacity']);
        $amenities = trim($_POST['amenities'] ?? '');
        $area = floatval($_POST['area'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        // Tạo loại phòng trước
        $stmt = $mysqli->prepare("INSERT INTO room_type (room_type_name, description, base_price, capacity, status, amenities, area) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdissd", $room_type_name, $description, $base_price, $capacity, $status, $amenities, $area);

        if ($stmt->execute()) {
            $roomTypeId = $mysqli->insert_id;

            // Upload ảnh nếu có
            if (isset($_FILES['room_type_images']) && hasRoomTypeImages($_FILES['room_type_images'])) {
                uploadRoomTypeImages($_FILES['room_type_images'], $roomTypeId, 6);
            }

            $message = 'Thêm loại phòng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }

    if (isset($_POST['update_room_type']) && $canEditRoomType) {
        $room_type_id = intval($_POST['room_type_id']);
        $room_type_name = trim($_POST['room_type_name']);
        $description = trim($_POST['description'] ?? '');
        $base_price = floatval($_POST['base_price']);
        $capacity = intval($_POST['capacity']);
        $amenities = trim($_POST['amenities'] ?? '');
        $area = floatval($_POST['area'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        // Upload thêm ảnh mới nếu có (luôn chạy nếu có file)
        if (isset($_FILES['room_type_images']) && hasRoomTypeImages($_FILES['room_type_images'])) {
            uploadRoomTypeImages($_FILES['room_type_images'], $room_type_id, 6);
        }

        $stmt = $mysqli->prepare("UPDATE room_type 
                                  SET room_type_name=?, 
                                      description=?, 
                                      base_price=?, 
                                      capacity=?, 
                                      status=?, 
                                      amenities=?, 
                                      area=?
                                  WHERE room_type_id=? 
                                  AND deleted IS NULL");
        $stmt->bind_param("ssdissdi", $room_type_name, $description, $base_price, $capacity, $status, $amenities, $area, $room_type_id);

        if ($stmt->execute()) {
            $message = 'Cập nhật loại phòng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }

    if (isset($_POST['delete_room_type']) && $canDeleteRoomType) {
        $room_type_id = intval($_POST['room_type_id']);
        $stmt = $mysqli->prepare("UPDATE room_type SET deleted = NOW() WHERE room_type_id = ?");
        $stmt->bind_param("i", $room_type_id);

        if ($stmt->execute()) {
            $message = 'Xóa loại phòng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }

}

// Lấy thông tin loại phòng để edit
$editRoomTypes = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM room_type WHERE room_type_id = ? AND deleted IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editRoomTypes = $result->fetch_assoc();
    $stmt->close();
}

// Phân trang và filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';
$pageNum = isset($_GET['pageNum']) ? intval($_GET['pageNum']) : 1;
$pageNum = max(1, $pageNum);
$perPage = 10;
$offset = ($pageNum - 1) * $perPage;

// Xây dựng query
$where = "WHERE rt.deleted IS NULL";
$params = [];
$types = '';

if ($search) {
    $where .= " AND (rt.room_type_name LIKE ? OR rt.description LIKE ? OR rt.amenities LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    $types .= 'sss';
}

if ($status_filter) {
    $where .= " AND rt.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

// Order by
$orderBy = "ORDER BY rt.room_type_id DESC";
if ($sort == 'area_asc') {
    $orderBy = "ORDER BY rt.area ASC";
} elseif ($sort == 'area_desc') {
    $orderBy = "ORDER BY rt.area DESC";
}

// Đếm tổng số
$countQuery = "SELECT COUNT(*) as total FROM room_type rt $where";
$countStmt = $mysqli->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalResult = $countStmt->get_result();
$totalRoomTypes = $totalResult->fetch_assoc()['total'];
$countStmt->close();

// Lấy dữ liệu
$roomTypesAll = [];
if ($totalRoomTypes > 0) {
    $query = "SELECT rt.*, COUNT(r.room_id) as room_count
              FROM room_type rt
              LEFT JOIN room r ON rt.room_type_id = r.room_type_id AND r.deleted IS NULL
              $where
              GROUP BY rt.room_type_id
              $orderBy
              LIMIT ? OFFSET ?";

    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $mysqli->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $roomTypesAll = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Build base URL for pagination
$baseUrl = "index.php?page=room-manager&panel=roomType-panel";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urlencode($status_filter);
if ($sort) $baseUrl .= "&sort=" . urlencode($sort);
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo h($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="content-card">
    <div class="card-header-custom">
        <h3 class="card-title">Danh Sách Loại Phòng</h3>
        <?php if ($canCreateRoomType): ?>
            <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addRoomTypeModal">
                <i class="fas fa-plus"></i> Thêm Loại Phòng
            </button>
        <?php endif; ?>
    </div>

    <div class="filter-section">
        <form method="GET" action="">
            <input type="hidden" name="page" value="room-manager">
            <input type="hidden" name="panel" value="roomType-panel">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" name="search" placeholder="Tìm kiếm" value="<?php echo h($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Đang Hoạt Động</option>
                        <option value="maintenance" <?php echo $status_filter == 'maintenance' ? 'selected' : ''; ?>>Đang bảo trì</option>
                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Dừng Hoạt Động</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="sort">
                        <option value="">Sắp xếp mặc định</option>
                        <option value="area_asc" <?php echo $sort == 'area_asc' ? 'selected' : ''; ?>>Diện Tích Tăng Dần</option>
                        <option value="area_desc" <?php echo $sort == 'area_desc' ? 'selected' : ''; ?>>Diện tích Giảm Dần</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Bảng danh sách loại phòng -->
    <div class="table-container">
        <table class="table table-hover" id="roomsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Loại</th>
                    <th>Giá/Đêm</th>
                    <th>Diện Tích</th>
                    <th>Sức Chứa</th>
                    <th>Tiện Nghi</th>
                    <th>Số Phòng</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($roomTypesAll)): ?>
                    <tr>
                        <td colspan="9" class="text-center">Không có dữ liệu</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($roomTypesAll as $rt): ?>
                        <tr>
                            <td><?php echo $rt['room_type_id']; ?></td>
                            <td><strong><?php echo h($rt['room_type_name']); ?></strong></td>
                            <td><?php echo formatCurrency($rt['base_price'] ?? 0); ?></td>
                            <td><?php echo $rt['area'] ? number_format($rt['area'], 1) . ' m²' : '-'; ?></td>
                            <td><?php echo $rt['capacity'] ?? '-'; ?> người</td>
                            <td><?php echo h($rt['amenities'] ?? '-'); ?></td>
                            <td><span class="badge bg-info"><?php echo $rt['room_count'] ?? 0; ?></span></td>
                            <td>
                                <?php
                                $statusClass = 'bg-secondary';
                                $statusText = $rt['status'];
                                switch ($rt['status']) {
                                    case 'active':
                                        $statusClass = 'bg-success';
                                        $statusText = 'Đang hoạt động';
                                        break;
                                    case 'maintenance':
                                        $statusClass = 'bg-warning';
                                        $statusText = 'Đang bảo trì';
                                        break;
                                    case 'inactive':
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Dừng hoạt động';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                    data-bs-target="#viewRoomTypeModal<?php echo $rt['room_type_id']; ?>" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($canEditRoomType): ?>
                                    <button class="btn btn-sm btn-outline-warning"
                                        onclick="editRoomTypes(<?php echo $rt['room_type_id']; ?>)" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if ($canDeleteRoomType): ?>
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteRoomTypes(<?php echo $rt['room_type_id']; ?>)" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <div class="modal fade" id="viewRoomTypeModal<?php echo $rt['room_type_id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Chi Tiết Loại Phòng</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <?php
                                                // Ảnh loại phòng: lấy từ roomtype_images
                                                $rtImages = [];
                                                $rtIdView = (int)$rt['room_type_id'];
                                                $imgStmtView = $mysqli->prepare("SELECT image_url FROM roomtype_images WHERE room_type_id = ? ORDER BY is_primary DESC, display_order ASC, id ASC");
                                                $imgStmtView->bind_param("i", $rtIdView);
                                                $imgStmtView->execute();
                                                $imgResView = $imgStmtView->get_result();
                                                while ($rowView = $imgResView->fetch_assoc()) {
                                                    $rtImages[] = $rowView['image_url'];
                                                }
                                                $imgStmtView->close();

                                                if (!empty($rtImages)):
                                                    foreach ($rtImages as $imgUrl):
                                                        if (!empty($imgUrl)):
                                                ?>
                                                            <img src="<?php echo h($imgUrl); ?>" class="img-fluid rounded mb-2"
                                                                alt="Room Type Image" style="max-width: 100%; max-height: 200px; object-fit: cover;">
                                                    <?php
                                                        endif;
                                                    endforeach;
                                                else:
                                                    ?>
                                                    <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 100%; height: 200px;">
                                                        <div class="text-center text-muted">
                                                            <i class="fas fa-image fa-3x mb-2"></i>
                                                            <p class="mb-0">Chưa có ảnh</p>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Tên loại phòng:</strong> <?php echo h($rt['room_type_name'] ?? '-'); ?></p>
                                                <p><strong>Mô tả:</strong> <?php echo nl2br(h($rt['description'] ?? '-')); ?></p>
                                                <p><strong>Giá/đêm:</strong> <?php echo formatCurrency($rt['base_price'] ?? 0); ?></p>
                                                <p><strong>Diện tích:</strong> <?php echo $rt['area'] ? number_format($rt['area'], 1) . 'm²' : '-'; ?></p>
                                                <p><strong>Sức chứa:</strong> <?php echo $rt['capacity'] ?? '-'; ?> người</p>
                                                <p><strong>Số phòng:</strong> <span class="badge bg-info"><?php echo $rt['room_count'] ?? 0; ?></span></p>
                                                <p><strong>Trạng thái:</strong> <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></p>
                                                <?php if ($rt['amenities']): ?>
                                                    <p><strong>Tiện nghi:</strong> <?php echo h($rt['amenities']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                        <?php if ($canEditRoomType): ?>
                                            <button type="button" class="btn btn-primary"
                                                onclick="editRoomTypeFromView(<?php echo $rt['room_type_id']; ?>)">
                                                Chỉnh Sửa
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php echo getPagination($totalRoomTypes, $perPage, $pageNum, $baseUrl); ?>
</div>

<!-- Modal Thêm/Sửa Loại Phòng -->
<div class="modal fade" id="addRoomTypeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $editRoomTypes ? 'Sửa' : 'Thêm'; ?> Loại Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="roomTypeForm">
                <div class="modal-body">
                    <?php if ($editRoomTypes): ?>
                        <input type="hidden" name="room_type_id" value="<?php echo $editRoomTypes['room_type_id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Tên loại phòng *</label>
                        <input type="text" class="form-control" name="room_type_name" required
                            value="<?php echo h($editRoomTypes['room_type_name'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="3"><?php echo h($editRoomTypes['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giá cơ bản (VNĐ) *</label>
                            <input type="number" class="form-control" name="base_price" step="0.01" required
                                value="<?php echo h($editRoomTypes['base_price'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sức chứa (người) *</label>
                            <input type="number" class="form-control" name="capacity" required
                                value="<?php echo h($editRoomTypes['capacity'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Diện tích (m²)</label>
                            <input type="number" class="form-control" name="area" step="0.01"
                                value="<?php echo h($editRoomTypes['area'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tiện nghi</label>
                            <input type="text" class="form-control" name="amenities"
                                placeholder="VD: WiFi, TV, Điều hòa..."
                                value="<?php echo h($editRoomTypes['amenities'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái *</label>
                        <select class="form-select" name="status" required>
                            <option value="active" <?php echo ($editRoomTypes['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>
                                Đang hoạt động</option>
                            <option value="inactive" <?php echo ($editRoomTypes['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>
                                Dừng hoạt động</option>
                            <option value="maintenance" <?php echo ($editRoomTypes['status'] ?? '') == 'maintenance' ? 'selected' : ''; ?>>
                                Bảo trì</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ảnh Loại Phòng (4 - 6 ảnh, lấy từ Cloudinary)</label>
                        <div class="image-upload-area" id="triggerRoomTypeUpload"
                            style="border: 2px dashed #ccc; padding: 20px; text-align: center; border-radius: 8px; cursor: pointer; background: #f9f9f9; transition: all 0.3s;">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Click để chọn ảnh (có thể chọn nhiều)</p>
                            <small class="text-muted">hoặc kéo thả ảnh vào đây (tối đa 6 ảnh)</small>
                        </div>
                        <input type="file"
                            id="roomTypeImagesInput"
                            name="room_type_images[]"
                            accept="image/*"
                            multiple
                            style="opacity: 0; position: absolute; width: 1px; height: 1px; top: -100px;" />
                        <div id="roomTypePreviewContainer" class="mt-3 d-flex flex-wrap gap-2">
                            <?php if ($editRoomTypes): ?>
                                <?php
                                $rtImages = [];
                                $rtId = (int)$editRoomTypes['room_type_id'];
                                $imgStmt = $mysqli->prepare("SELECT id, image_url FROM roomtype_images WHERE room_type_id = ? ORDER BY is_primary DESC, display_order ASC, id ASC");
                                $imgStmt->bind_param("i", $rtId);
                                $imgStmt->execute();
                                $imgRes = $imgStmt->get_result();
                                while ($row = $imgRes->fetch_assoc()) {
                                    $rtImages[] = $row;
                                }
                                $imgStmt->close();

                                // Fallback nếu chưa có (cột cũ)
                                if (empty($rtImages) && !empty($editRoomTypes['image'])) {
                                    $rtImages[] = ['id' => 0, 'image_url' => $editRoomTypes['image']];
                                }

                                foreach ($rtImages as $img) :
                                    if (!empty($img['image_url'])) :
                                ?>
                                        <div class="image-preview-item position-relative me-2 mb-2" data-img-id="<?php echo $img['id']; ?>">
                                            <img src="<?php echo h($img['image_url']); ?>"
                                                style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd;" />
                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                                                onclick="this.parentElement.remove()">×</button>
                                        </div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Định dạng: JPG, PNG, GIF, WEBP. Kích thước tối đa: 5MB mỗi ảnh. Tối đa 6 ảnh / loại phòng.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" name="<?php echo $editRoomTypes ? 'update_room_type' : 'add_room_type'; ?>">
                        <?php echo $editRoomTypes ? 'Cập nhật' : 'Thêm'; ?> Loại Phòng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Preview multiple images (client-side)
    function previewMultipleImages(input, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const files = input.files;
        if (!files || !files.length) return;

        // Xóa preview tạm trước (giữ ảnh cũ từ DB)
        container.querySelectorAll('.image-preview-temp').forEach(el => el.remove());

        const maxPreview = 6;
        const existingCount = container.querySelectorAll('.image-preview-item:not(.image-preview-temp)').length;
        const remaining = maxPreview - existingCount;
        const count = Math.min(files.length, remaining);

        for (let i = 0; i < count; i++) {
            const file = files[i];
            if (!file.type.startsWith('image/')) continue;

            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.createElement('div');
                wrapper.className = 'image-preview-item image-preview-temp position-relative me-2 mb-2';
                wrapper.innerHTML = `
                    <img src="${e.target.result}"
                         style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 2px solid #ddd;" />
                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                            onclick="this.parentElement.remove()">×</button>
                `;
                container.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        }
    }

    // Fix trigger upload ảnh
    document.addEventListener('DOMContentLoaded', function() {
        const trigger = document.getElementById('triggerRoomTypeUpload');
        const fileInput = document.getElementById('roomTypeImagesInput');

        if (trigger && fileInput) {
            trigger.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', () => previewMultipleImages(fileInput, 'roomTypePreviewContainer'));
            trigger.addEventListener('mouseenter', () => trigger.style.borderColor = '#007bff');
            trigger.addEventListener('mouseleave', () => trigger.style.borderColor = '#ccc');
        }

        // Đảm bảo form luôn có enctype
        const form = document.getElementById('roomTypeForm');
        if (form && form.getAttribute('enctype') !== 'multipart/form-data') {
            form.setAttribute('enctype', 'multipart/form-data');
            form.encoding = 'multipart/form-data';
        }
    });

    function resetRoomTypeForm() {
        const url = new URL(window.location);
        url.searchParams.delete('action');
        url.searchParams.delete('id');
        window.history.replaceState({}, '', url);

        const form = document.getElementById('roomTypeForm');
        if (!form) return;

        const roomTypeIdInput = form.querySelector('input[name="room_type_id"]');
        if (roomTypeIdInput) roomTypeIdInput.remove();

        form.reset();

        // Reset fields
        form.querySelector('input[name="room_type_name"]').value = '';
        form.querySelector('textarea[name="description"]').value = '';
        form.querySelector('input[name="base_price"]').value = '';
        form.querySelector('input[name="capacity"]').value = '';
        form.querySelector('input[name="area"]').value = '';
        form.querySelector('input[name="amenities"]').value = '';
        const statusSelect = form.querySelector('select[name="status"]');
        statusSelect.value = 'active';
        statusSelect.querySelector('option[value="active"]').selected = true;

        // Clear preview (giữ ảnh cũ nếu cần, nhưng reset thì xóa tạm)
        document.getElementById('roomTypePreviewContainer').innerHTML = '';

        // Reset title & button
        document.querySelector('#addRoomTypeModal .modal-title').textContent = 'Thêm Loại Phòng';
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.name = 'add_room_type';
        submitBtn.textContent = 'Thêm Loại Phòng';
    }

    function editRoomTypes(id) {
        const url = new URL(window.location.href);
        url.searchParams.set('action', 'edit');
        url.searchParams.set('id', id);
        window.location.href = url.toString();
    }

    function editRoomTypeFromView(id) {
        if (window.bootstrap) {
            const modalEl = document.getElementById("viewRoomTypeModal" + id);
            const modalInstance = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        editRoomTypes(id);
    }

    function deleteRoomTypes(id) {
        if (confirm('Bạn có chắc chắn muốn xóa loại phòng này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="room_type_id" value="' + id + '">' +
                '<input type="hidden" name="delete_room_type" value="1">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Auto reset khi mở/thêm/sửa
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('addRoomTypeModal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', () => setTimeout(resetRoomTypeForm, 100));
            const addBtn = document.querySelector('[data-bs-target="#addRoomTypeModal"]');
            if (addBtn) addBtn.addEventListener('click', () => setTimeout(resetRoomTypeForm, 50));
            modal.addEventListener('show.bs.modal', () => {
                if (!window.location.search.includes('action=edit')) {
                    setTimeout(resetRoomTypeForm, 50);
                }
            });
        }
    });

    <?php if ($editRoomTypes): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('roomTypeForm');
            if (form) {
                form.querySelector('input[name="room_type_name"]').value = '<?php echo addslashes(h($editRoomTypes['room_type_name'])); ?>';
                form.querySelector('textarea[name="description"]').value = '<?php echo addslashes(h($editRoomTypes['description'] ?? '')); ?>';
                form.querySelector('input[name="base_price"]').value = '<?php echo $editRoomTypes['base_price']; ?>';
                form.querySelector('input[name="capacity"]').value = '<?php echo $editRoomTypes['capacity']; ?>';
                form.querySelector('input[name="area"]').value = '<?php echo $editRoomTypes['area'] ?? ''; ?>';
                form.querySelector('input[name="amenities"]').value = '<?php echo addslashes(h($editRoomTypes['amenities'] ?? '')); ?>';
                form.querySelector('select[name="status"]').value = '<?php echo $editRoomTypes['status']; ?>';

                document.querySelector('#addRoomTypeModal .modal-title').textContent = 'Sửa Loại Phòng';
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.name = 'update_room_type';
                submitBtn.textContent = 'Cập nhật Loại Phòng';
            }

            setTimeout(() => {
                const modalEl = document.getElementById('addRoomTypeModal');
                if (modalEl && bootstrap) bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }, 100);
        });
    <?php endif; ?>
</script>