<?php
// Xử lý CRUD
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_service'])) {
        $service_name = trim($_POST['service_name']);
        $description = trim($_POST['description'] ?? '');
        $service_type = trim($_POST['service_type']);
        $price = floatval($_POST['price']);
        $unit = trim($_POST['unit'] ?? '');
        $status = $_POST['status'] ?? 'Active';

        $stmt = $mysqli->prepare("INSERT INTO service (service_name, description, service_type, price, unit, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdss", $service_name, $description, $service_type, $price, $unit, $status);

        if ($stmt->execute()) {
            $message = 'Thêm dịch vụ thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }

    if (isset($_POST['update_service'])) {
        $service_id = intval($_POST['service_id']);
        $service_name = trim($_POST['service_name']);
        $description = trim($_POST['description'] ?? '');
        $service_type = trim($_POST['service_type']);
        $price = floatval($_POST['price']);
        $unit = trim($_POST['unit'] ?? '');
        $status = $_POST['status'] ?? 'Active';

        $stmt = $mysqli->prepare("UPDATE service SET service_name=?, description=?, service_type=?, price=?, unit=?, status=? WHERE service_id=? AND deleted IS NULL");
        $stmt->bind_param("sssdssi", $service_name, $description, $service_type, $price, $unit, $status, $service_id);

        if ($stmt->execute()) {
            $message = 'Cập nhật dịch vụ thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }

    if (isset($_POST['delete_service'])) {
        $service_id = intval($_POST['service_id']);
        $stmt = $mysqli->prepare("UPDATE service SET deleted = NOW() WHERE service_id = ?");
        $stmt->bind_param("i", $service_id);

        if ($stmt->execute()) {
            $message = 'Xóa dịch vụ thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }
}

// Lấy thông tin dịch vụ để edit
$editService = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM service WHERE service_id = ? AND deleted IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editService = $result->fetch_assoc();
    $stmt->close();
}

// Phân trang và tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$type_filter = isset($_GET['type']) ? trim($_GET['type']) : '';
$pageNum = isset($_GET['pageNum']) ? intval($_GET['pageNum']) : 1;
$pageNum = max(1, $pageNum); // Đảm bảo pageNum >= 1
$perPage = 5;
$offset = ($pageNum - 1) * $perPage;

// Xây dựng WHERE clause
$where = "WHERE s.deleted IS NULL";
$params = [];
$types = '';

if ($search) {
    $where .= " AND (s.service_name LIKE ? OR s.service_type LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam]);
    $types .= 'ss';
}

if ($status_filter) {
    $where .= " AND s.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($type_filter) {
    $where .= " AND s.service_type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

// Đếm tổng số
$countQuery = "SELECT COUNT(*) as total FROM service s $where";
$countStmt = $mysqli->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalResult = $countStmt->get_result();
$total = $totalResult->fetch_assoc()['total'];
$countStmt->close();

// Lấy dữ liệu - FIX: Áp dụng $where, $params, $offset, $perPage
$query = "SELECT * FROM service s 
    $where
    ORDER BY s.service_id ASC 
    LIMIT $perPage OFFSET $offset";

$stmt = $mysqli->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $services = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Lỗi query: " . $stmt->error);
}
$stmt->close();

// Lấy danh sách service types
$typesResult = $mysqli->query("SELECT DISTINCT service_type FROM service WHERE deleted IS NULL ORDER BY service_type");
$serviceTypes = $typesResult->fetch_all(MYSQLI_ASSOC);

// Build base URL for pagination
$baseUrl = "index.php?page=services-manager";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urlencode($status_filter);
if ($type_filter) $baseUrl .= "&type=" . urlencode($type_filter);
?>

<div class="main-content">
    <div class="content-header">
        <h1>Quản Lý Dịch Vụ</h1>
        <div class="row m-3">
            <div class="col-md-12">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="fas fa-plus"></i> Thêm Dịch Vụ
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

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" action="index.php">
            <input type="hidden" name="page" value="services-manager">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Tìm kiếm dịch vụ..."
                            value="<?php echo h($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="Active" <?php echo $status_filter == 'Active' ? 'selected' : ''; ?>>Đang hoạt
                            động</option>
                        <option value="Inactive" <?php echo $status_filter == 'Inactive' ? 'selected' : ''; ?>>Tạm dừng
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="type">
                        <option value="">Tất cả loại</option>
                        <?php foreach ($serviceTypes as $st): ?>
                            <option value="<?php echo h($st['service_type']); ?>"
                                <?php echo $type_filter == $st['service_type'] ? 'selected' : ''; ?>>
                                <?php echo h($st['service_type']); ?>
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

    <!-- Table -->
    <div class="table-container">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Dịch Vụ</th>
                    <th>Loại</th>
                    <th>Đơn Vị</th>
                    <th>Giá</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="7" class="text-center">Không có dữ liệu</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo $service['service_id']; ?></td>
                            <td><?php echo h($service['service_name']); ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo h($service['service_type']); ?></span>
                            </td>
                            <td><?php echo h($service['unit'] ?: '-'); ?></td>
                            <td><?php echo formatCurrency($service['price']); ?></td>
                            <td>
                                <span class="badge <?php echo $service['status'] == 'Active' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo h($service['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-warning"
                                    onclick="editService(<?php echo $service['service_id']; ?>)" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="deleteService(<?php echo $service['service_id']; ?>)" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php echo getPagination($total, $perPage, $pageNum, $baseUrl); ?>
</div>

<!-- Modal Thêm/Sửa Dịch Vụ -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $editService ? 'Sửa' : 'Thêm'; ?> Dịch Vụ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php if ($editService): ?>
                        <input type="hidden" name="service_id" value="<?php echo $editService['service_id']; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên Dịch Vụ *</label>
                            <input type="text" class="form-control" name="service_name"
                                value="<?php echo h($editService['service_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loại Dịch Vụ *</label>
                            <select class="form-select" name="service_type">
                                <option value="" disabled>Tất cả loại</option>
                                <?php foreach ($serviceTypes as $st): ?>
                                    <option value="<?php echo h($st['service_type']); ?>"
                                        <?php echo $type_filter == $st['service_type'] ? 'selected' : ''; ?>>
                                        <?php echo h($st['service_type']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giá (VNĐ) *</label>
                            <input type="number" class="form-control" name="price" step="0.01"
                                value="<?php echo $editService['price'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Đơn Vị</label>
                            <input type="text" class="form-control" name="unit"
                                value="<?php echo h($editService['unit'] ?? ''); ?>"
                                placeholder="VD: Suất, Gói, Lần...">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô Tả</label>
                        <textarea class="form-control" name="description"
                            rows="3"><?php echo h($editService['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng Thái *</label>
                        <select class="form-select" name="status" required>
                            <option value="Active"
                                <?php echo ($editService['status'] ?? 'Active') == 'Active' ? 'selected' : ''; ?>>Đang
                                hoạt động</option>
                            <option value="Inactive"
                                <?php echo ($editService['status'] ?? '') == 'Inactive' ? 'selected' : ''; ?>>Tạm dừng
                            </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"
                        name="<?php echo $editService ? 'update_service' : 'add_service'; ?>">
                        <?php echo $editService ? 'Cập nhật' : 'Thêm'; ?> Dịch Vụ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editService(id) {
        window.location.href = 'index.php?page=services-manager&action=edit&id=' + id;
    }

    function deleteService(id) {
        if (confirm('Bạn có chắc chắn muốn xóa dịch vụ này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="service_id" value="' + id + '">' +
                '<input type="hidden" name="delete_service" value="1">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    <?php if ($editService): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('addServiceModal'));
            modal.show();
        });
    <?php endif; ?>
</script>