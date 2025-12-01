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

// Hàm upload ảnh cho loại phòng
function uploadRoomTypeImage($file, $oldImage = '')
{
    if (!isset($file['name']) || empty($file['name'])) {
        return $oldImage;
    }

    $uploadDir = __DIR__ . '/../assets/images/room-type/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }

    if ($file['size'] > $maxSize) {
        return false;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'roomtype_' . time() . '_' . uniqid() . '.' . $extension;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        if ($oldImage && !empty($oldImage)) {
            $oldPath = '';
            if (strpos($oldImage, 'client/') !== false) {
                $oldPath = __DIR__ . '/../../client/' . str_replace('client/', '', $oldImage);
            } else {
                $oldPath = __DIR__ . '/../' . $oldImage;
            }
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }
        return 'assets/images/room-type/' . $newFileName;
    }
    return false;
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

        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploadResult = uploadRoomTypeImage($_FILES['image']);
            if ($uploadResult !== false) {
                $image = $uploadResult;
            }
        }

        $stmt = $mysqli->prepare("INSERT INTO room_type (room_type_name, description, base_price, capacity, status, amenities, area, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdissds", $room_type_name, $description, $base_price, $capacity, $status, $amenities, $area, $image);

        if ($stmt->execute()) {
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

        // Lấy ảnh cũ
        $oldImageStmt = $mysqli->prepare("SELECT image FROM room_type WHERE room_type_id = ?");
        $oldImageStmt->bind_param("i", $room_type_id);
        $oldImageStmt->execute();
        $oldImageResult = $oldImageStmt->get_result();
        $oldImage = $oldImageResult->fetch_assoc()['image'] ?? '';
        $oldImageStmt->close();

        $image = $oldImage;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $uploadResult = uploadRoomTypeImage($_FILES['image'], $oldImage);
            if ($uploadResult !== false) {
                $image = $uploadResult;
            }
        }

        $stmt = $mysqli->prepare("UPDATE room_type 
                                  SET room_type_name=?, 
                                      description=?, 
                                      base_price=?, 
                                      capacity=?, 
                                      status=?, 
                                      amenities=?, 
                                      area=?,
                                      image=?
                                  WHERE room_type_id=? 
                                  AND deleted IS NULL");
        $stmt->bind_param("ssdissdsi", $room_type_name, $description, $base_price, $capacity, $status, $amenities, $area, $image, $room_type_id);

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
                                                <?php if (!empty($rt['image'])): ?>
                                                    <img src="<?php echo h($rt['image']); ?>" class="img-fluid rounded"
                                                        alt="Room Type Image">
                                                <?php else: ?>
                                                    <img src="https://via.placeholder.com/400x300" class="img-fluid rounded"
                                                        alt="Room Type Image">
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
            <form method="POST" enctype="multipart/form-data">
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
                        <label class="form-label">Ảnh Loại Phòng</label>
                        <div class="image-upload-area" onclick="document.getElementById('roomTypeImage').click()"
                            style="border: 2px dashed #ccc; padding: 20px; text-align: center; border-radius: 5px; cursor: pointer;">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Click để chọn ảnh</p>
                            <small class="text-muted">hoặc kéo thả ảnh vào đây</small>
                        </div>
                        <input type="file" id="roomTypeImage" name="image" accept="image/*"
                            style="display: none" onchange="previewImage(this, 'roomTypePreview')" />
                        <?php if ($editRoomTypes && !empty($editRoomTypes['image'])): ?>
                            <img id="roomTypePreview" class="image-preview mt-3"
                                src="../<?php echo h($editRoomTypes['image']); ?>"
                                style="max-width: 100%; max-height: 200px; border-radius: 5px; display: block;" />
                        <?php else: ?>
                            <img id="roomTypePreview" class="image-preview mt-3"
                                style="display: none; max-width: 100%; max-height: 200px; border-radius: 5px;" />
                        <?php endif; ?>
                        <div class="mt-2">
                            <small class="text-muted">Định dạng: JPG, PNG, GIF, WEBP. Kích thước tối đa: 5MB</small>
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
    function resetRoomTypeForm() {
        // Clear URL parameters first
        const url = new URL(window.location);
        url.searchParams.delete('action');
        url.searchParams.delete('id');
        window.history.replaceState({}, '', url);

        // Reset form
        const form = document.querySelector('#addRoomTypeModal form');
        if (!form) return;

        // Xóa room_type_id hidden input nếu có
        const roomTypeIdInput = form.querySelector('input[name="room_type_id"]');
        if (roomTypeIdInput) {
            roomTypeIdInput.remove();
        }

        // Reset tất cả fields một cách rõ ràng - override giá trị từ PHP
        const roomTypeName = form.querySelector('input[name="room_type_name"]');
        const description = form.querySelector('textarea[name="description"]');
        const basePrice = form.querySelector('input[name="base_price"]');
        const capacity = form.querySelector('input[name="capacity"]');
        const area = form.querySelector('input[name="area"]');
        const amenities = form.querySelector('input[name="amenities"]');
        const status = form.querySelector('select[name="status"]');

        // Force reset từng field
        if (roomTypeName) {
            roomTypeName.value = '';
            roomTypeName.defaultValue = '';
        }
        if (description) {
            description.value = '';
            description.defaultValue = '';
        }
        if (basePrice) {
            basePrice.value = '';
            basePrice.defaultValue = '';
        }
        if (capacity) {
            capacity.value = '';
            capacity.defaultValue = '';
        }
        if (area) {
            area.value = '';
            area.defaultValue = '';
        }
        if (amenities) {
            amenities.value = '';
            amenities.defaultValue = '';
        }
        if (status) {
            status.value = 'active';
            status.selectedIndex = 0;
            // Force select option
            const activeOption = status.querySelector('option[value="active"]');
            if (activeOption) {
                activeOption.selected = true;
            }
        }

        // Reset form để clear tất cả default values
        form.reset();

        // Sau khi reset, set lại values để đảm bảo
        if (roomTypeName) roomTypeName.value = '';
        if (description) description.value = '';
        if (basePrice) basePrice.value = '';
        if (capacity) capacity.value = '';
        if (area) area.value = '';
        if (amenities) amenities.value = '';
        if (status) {
            status.value = 'active';
            const activeOption = status.querySelector('option[value="active"]');
            if (activeOption) activeOption.selected = true;
        }

        // Reset modal title
        const modalTitle = document.querySelector('#addRoomTypeModal .modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Thêm Loại Phòng';
        }

        // Reset submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.name = 'add_room_type';
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Thêm Loại Phòng';
        }
    }

    function editRoomTypes(id) {
        window.location.href = 'index.php?page=room-manager&panel=roomType-panel&action=edit&id=' + id;
    }

    <?php if ($editRoomTypes): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Populate form with edit data
            const form = document.querySelector('#addRoomTypeModal form');
            if (form) {
                const roomTypeName = form.querySelector('input[name="room_type_name"]');
                const description = form.querySelector('textarea[name="description"]');
                const basePrice = form.querySelector('input[name="base_price"]');
                const capacity = form.querySelector('input[name="capacity"]');
                const area = form.querySelector('input[name="area"]');
                const amenities = form.querySelector('input[name="amenities"]');
                const status = form.querySelector('select[name="status"]');

                if (roomTypeName) roomTypeName.value = '<?php echo h($editRoomTypes['room_type_name']); ?>';
                if (description) description.value = '<?php echo h($editRoomTypes['description'] ?? ''); ?>';
                if (basePrice) basePrice.value = '<?php echo h($editRoomTypes['base_price']); ?>';
                if (capacity) capacity.value = '<?php echo h($editRoomTypes['capacity']); ?>';
                if (area) area.value = '<?php echo h($editRoomTypes['area'] ?? ''); ?>';
                if (amenities) amenities.value = '<?php echo h($editRoomTypes['amenities'] ?? ''); ?>';
                if (status) status.value = '<?php echo h($editRoomTypes['status']); ?>';

                // Update modal title and button
                const modalTitle = document.querySelector('#addRoomTypeModal .modal-title');
                const submitBtn = form.querySelector('button[type="submit"]');
                if (modalTitle) modalTitle.textContent = 'Sửa Loại Phòng';
                if (submitBtn) {
                    submitBtn.name = 'update_room_type';
                    submitBtn.textContent = 'Cập nhật Loại Phòng';
                }
            }

            // Delay để đảm bảo modal đã được render
            setTimeout(function() {
                const modalEl = document.getElementById('addRoomTypeModal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            }, 100);
        });
    <?php endif; ?>

    // Auto-reset modal when closed or when "Add" button is clicked
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('addRoomTypeModal');
        if (modal) {
            // Clear URL and reset form when modal is closed
            modal.addEventListener('hidden.bs.modal', function() {
                const url = new URL(window.location);
                url.searchParams.delete('action');
                url.searchParams.delete('id');
                window.history.replaceState({}, '', url);
                // Reset form ngay lập tức
                setTimeout(function() {
                    resetRoomTypeForm();
                }, 100);
            });

            // Reset form when "Add" button is clicked
            const addButton = document.querySelector('[data-bs-target="#addRoomTypeModal"]');
            if (addButton) {
                addButton.addEventListener('click', function(e) {
                    // Clear URL first
                    const url = new URL(window.location);
                    url.searchParams.delete('action');
                    url.searchParams.delete('id');
                    window.history.replaceState({}, '', url);
                    // Reset form ngay lập tức - không cần delay
                    setTimeout(function() {
                        resetRoomTypeForm();
                    }, 50);
                });
            }

            // Reset form when modal opens if not in edit mode
            modal.addEventListener('show.bs.modal', function() {
                const isEditMode = window.location.search.includes('action=edit');
                if (!isEditMode) {
                    // Reset form ngay lập tức với delay nhỏ
                    setTimeout(function() {
                        resetRoomTypeForm();
                    }, 50);
                }
            });
        }
    });


    // ==================== ROOM TYPES FUNCTIONS ====================
    function editRoomTypes(id) {
        const url = new URL(window.location.href);
        url.searchParams.set('action', 'edit');
        url.searchParams.set('id', id);
        window.location.href = url.toString();
    }

    function editRoomTypeFromView(id) {
        const viewModal = bootstrap.Modal.getInstance(
            document.getElementById("viewRoomTypeModal" + id)
        );
        if (viewModal) {
            viewModal.hide();
        }
        window.location.href = 'index.php?page=room-manager&panel=roomType-panel&action=edit&id=' + id;
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

</script>