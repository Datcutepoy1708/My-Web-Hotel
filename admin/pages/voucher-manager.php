<?php
// Phân quyền module Voucher
$canViewVoucher   = function_exists('checkPermission') ? checkPermission('voucher.view')   : true;
$canCreateVoucher = function_exists('checkPermission') ? checkPermission('voucher.create') : true;
$canEditVoucher   = function_exists('checkPermission') ? checkPermission('voucher.edit')   : true;
$canDeleteVoucher = function_exists('checkPermission') ? checkPermission('voucher.delete') : true;

if (!$canViewVoucher) {
    http_response_code(403);
    echo '<div class="main-content"><div class="alert alert-danger m-4">Bạn không có quyền xem trang voucher.</div></div>';
    return;
}

// Xử lý CRUD
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$messageType = '';

// Hàm upload ảnh cho voucher
function uploadVoucherImage($file, $oldImage = '') {
    if (!isset($file['name']) || empty($file['name'])) {
        return $oldImage;
    }
    
    $uploadDir = '../../client/assets/images/voucher/';
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
    $newFileName = 'voucher_' . time() . '_' . uniqid() . '.' . $extension;
    $targetPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        if ($oldImage && !empty($oldImage)) {
            $oldPath = '../../client/' . $oldImage;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
        return 'assets/images/voucher/' . $newFileName;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_voucher'])) {
        if (!$canCreateVoucher) {
            $message = 'Bạn không có quyền thêm voucher.';
            $messageType = 'danger';
        } else {
            $code = trim($_POST['code']);
            $name = trim($_POST['name']);
            $description = trim($_POST['description'] ?? '');
            
            // Xử lý upload ảnh
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $uploadResult = uploadVoucherImage($_FILES['image']);
                if ($uploadResult !== false) {
                    $image = $uploadResult;
                }
            } else {
                $image = trim($_POST['image'] ?? '');
            }
            
            $discount_type = $_POST['discount_type'] ?? 'percent';
            $discount_value = floatval($_POST['discount_value']);
            $max_discount = !empty($_POST['max_discount']) ? floatval($_POST['max_discount']) : null;
            
            $min_order = !empty($_POST['min_order']) ? floatval($_POST['min_order']) : 0;
            $apply_to = $_POST['apply_to'] ?? 'all';
            $customer_types = !empty($_POST['customer_types']) ? implode(',', $_POST['customer_types']) : null;
            $min_nights = !empty($_POST['min_nights']) ? intval($_POST['min_nights']) : null;
            $min_rooms = !empty($_POST['min_rooms']) ? intval($_POST['min_rooms']) : null;
            $room_types = !empty($_POST['room_types']) ? implode(',', $_POST['room_types']) : null;
            $service_ids = !empty($_POST['service_ids']) ? implode(',', $_POST['service_ids']) : null;
            
            $total_uses = intval($_POST['total_uses'] ?? 100);
            $per_customer = intval($_POST['per_customer'] ?? 1);
            
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $valid_days = !empty($_POST['valid_days']) ? implode(',', $_POST['valid_days']) : null;
            $valid_hours = trim($_POST['valid_hours'] ?? '');
            
            $status = $_POST['status'] ?? 'active';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_public = isset($_POST['is_public']) ? 1 : 0;
            $auto_apply = isset($_POST['auto_apply']) ? 1 : 0;
            $priority = intval($_POST['priority'] ?? 0);
            $is_stackable = isset($_POST['is_stackable']) ? 1 : 0;
            $payment_methods = !empty($_POST['payment_methods']) ? implode(',', $_POST['payment_methods']) : null;
            
            $created_by = isset($_SESSION['id_nhan_vien']) ? intval($_SESSION['id_nhan_vien']) : null;
            
            // Validate
            if (empty($code) || empty($name) || empty($discount_value) || empty($start_date) || empty($end_date)) {
                $message = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
                $messageType = 'danger';
            } elseif (strtotime($end_date) < strtotime($start_date)) {
                $message = 'Ngày kết thúc phải sau ngày bắt đầu.';
                $messageType = 'danger';
            } else {
                // Check code unique
                $checkStmt = $mysqli->prepare("SELECT voucher_id FROM voucher WHERE code = ? AND deleted IS NULL");
                $checkStmt->bind_param("s", $code);
                $checkStmt->execute();
                if ($checkStmt->get_result()->num_rows > 0) {
                    $message = 'Mã voucher đã tồn tại.';
                    $messageType = 'danger';
                    $checkStmt->close();
                } else {
                    $checkStmt->close();
                    
                    $stmt = $mysqli->prepare("INSERT INTO voucher (
                        code, name, description, image, discount_type, discount_value, max_discount,
                        min_order, apply_to, customer_types, min_nights, min_rooms, room_types, service_ids,
                        total_uses, per_customer, start_date, end_date, valid_days, valid_hours,
                        status, is_featured, is_public, auto_apply, priority, is_stackable, payment_methods, created_by
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $stmt->bind_param("sssssdddsisssssiisssssiisssi",
                        $code, $name, $description, $image, $discount_type, $discount_value, $max_discount,
                        $min_order, $apply_to, $customer_types, $min_nights, $min_rooms, $room_types, $service_ids,
                        $total_uses, $per_customer, $start_date, $end_date, $valid_days, $valid_hours,
                        $status, $is_featured, $is_public, $auto_apply, $priority, $is_stackable, $payment_methods, $created_by
                    );
                    
                    if ($stmt->execute()) {
                        $message = 'Thêm voucher thành công!';
                        $messageType = 'success';
                        $action = '';
                        header("Location: index.php?page=voucher-manager");
                        exit;
                    } else {
                        $message = 'Lỗi: ' . $stmt->error;
                        $messageType = 'danger';
                    }
                    $stmt->close();
                }
            }
        }
    }
    
    if (isset($_POST['update_voucher'])) {
        if (!$canEditVoucher) {
            $message = 'Bạn không có quyền chỉnh sửa voucher.';
            $messageType = 'danger';
        } else {
            $voucher_id = intval($_POST['voucher_id']);
            $code = trim($_POST['code']);
            $name = trim($_POST['name']);
            $description = trim($_POST['description'] ?? '');
            
            // Lấy ảnh cũ
            $oldImageStmt = $mysqli->prepare("SELECT image FROM voucher WHERE voucher_id = ?");
            $oldImageStmt->bind_param("i", $voucher_id);
            $oldImageStmt->execute();
            $oldImageResult = $oldImageStmt->get_result();
            $oldImage = $oldImageResult->fetch_assoc()['image'] ?? '';
            $oldImageStmt->close();
            
            // Xử lý upload ảnh
            $image = $oldImage;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $uploadResult = uploadVoucherImage($_FILES['image'], $oldImage);
                if ($uploadResult !== false) {
                    $image = $uploadResult;
                }
            } else {
                $image = trim($_POST['image'] ?? $oldImage);
            }
            
            $discount_type = $_POST['discount_type'] ?? 'percent';
            $discount_value = floatval($_POST['discount_value']);
            $max_discount = !empty($_POST['max_discount']) ? floatval($_POST['max_discount']) : null;
            
            $min_order = !empty($_POST['min_order']) ? floatval($_POST['min_order']) : 0;
            $apply_to = $_POST['apply_to'] ?? 'all';
            $customer_types = !empty($_POST['customer_types']) ? implode(',', $_POST['customer_types']) : null;
            $min_nights = !empty($_POST['min_nights']) ? intval($_POST['min_nights']) : null;
            $min_rooms = !empty($_POST['min_rooms']) ? intval($_POST['min_rooms']) : null;
            $room_types = !empty($_POST['room_types']) ? implode(',', $_POST['room_types']) : null;
            $service_ids = !empty($_POST['service_ids']) ? implode(',', $_POST['service_ids']) : null;
            
            $total_uses = intval($_POST['total_uses'] ?? 100);
            $per_customer = intval($_POST['per_customer'] ?? 1);
            
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];
            $valid_days = !empty($_POST['valid_days']) ? implode(',', $_POST['valid_days']) : null;
            $valid_hours = trim($_POST['valid_hours'] ?? '');
            
            $status = $_POST['status'] ?? 'active';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_public = isset($_POST['is_public']) ? 1 : 0;
            $auto_apply = isset($_POST['auto_apply']) ? 1 : 0;
            $priority = intval($_POST['priority'] ?? 0);
            $is_stackable = isset($_POST['is_stackable']) ? 1 : 0;
            $payment_methods = !empty($_POST['payment_methods']) ? implode(',', $_POST['payment_methods']) : null;
            
            // Check code unique (trừ chính nó)
            $checkStmt = $mysqli->prepare("SELECT voucher_id FROM voucher WHERE code = ? AND voucher_id != ? AND deleted IS NULL");
            $checkStmt->bind_param("si", $code, $voucher_id);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                $message = 'Mã voucher đã tồn tại.';
                $messageType = 'danger';
                $checkStmt->close();
            } else {
                $checkStmt->close();
                
                $stmt = $mysqli->prepare("UPDATE voucher SET 
                    code=?, name=?, description=?, image=?, discount_type=?, discount_value=?, max_discount=?,
                    min_order=?, apply_to=?, customer_types=?, min_nights=?, min_rooms=?, room_types=?, service_ids=?,
                    total_uses=?, per_customer=?, start_date=?, end_date=?, valid_days=?, valid_hours=?,
                    status=?, is_featured=?, is_public=?, auto_apply=?, priority=?, is_stackable=?, payment_methods=?
                    WHERE voucher_id=? AND deleted IS NULL");
                
                $stmt->bind_param("sssssdddsisssssiisssssiisssi",
                    $code, $name, $description, $image, $discount_type, $discount_value, $max_discount,
                    $min_order, $apply_to, $customer_types, $min_nights, $min_rooms, $room_types, $service_ids,
                    $total_uses, $per_customer, $start_date, $end_date, $valid_days, $valid_hours,
                    $status, $is_featured, $is_public, $auto_apply, $priority, $is_stackable, $payment_methods, $voucher_id
                );
                
                if ($stmt->execute()) {
                    $message = 'Cập nhật voucher thành công!';
                    $messageType = 'success';
                    header("Location: index.php?page=voucher-manager");
                    exit;
                } else {
                    $message = 'Lỗi: ' . $stmt->error;
                    $messageType = 'danger';
                }
                $stmt->close();
            }
        }
    }
    
    if (isset($_POST['delete_voucher'])) {
        if (!$canDeleteVoucher) {
            $message = 'Bạn không có quyền xóa voucher.';
            $messageType = 'danger';
        } else {
            $voucher_id = intval($_POST['voucher_id']);
            $stmt = $mysqli->prepare("UPDATE voucher SET deleted = NOW() WHERE voucher_id = ?");
            $stmt->bind_param("i", $voucher_id);
            
            if ($stmt->execute()) {
                $message = 'Xóa voucher thành công!';
                $messageType = 'success';
                header("Location: index.php?page=voucher-manager");
                exit;
            } else {
                $message = 'Lỗi: ' . $stmt->error;
                $messageType = 'danger';
            }
            $stmt->close();
        }
    }
    
    // Gán voucher cho khách hàng
    if (isset($_POST['assign_voucher_customer'])) {
        $voucher_id = intval($_POST['voucher_id']);
        $customer_id = intval($_POST['customer_id']);
        $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        $note = trim($_POST['note'] ?? '');
        $assigned_by = isset($_SESSION['id_nhan_vien']) ? intval($_SESSION['id_nhan_vien']) : null;
        
        // Check xem đã gán chưa
        $checkStmt = $mysqli->prepare("SELECT id FROM voucher_customer WHERE voucher_id = ? AND customer_id = ?");
        $checkStmt->bind_param("ii", $voucher_id, $customer_id);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $message = 'Voucher đã được gán cho khách hàng này rồi.';
            $messageType = 'danger';
            $checkStmt->close();
        } else {
            $checkStmt->close();
            $stmt = $mysqli->prepare("INSERT INTO voucher_customer (voucher_id, customer_id, expires_at, note, assigned_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iissi", $voucher_id, $customer_id, $expires_at, $note, $assigned_by);
            
            if ($stmt->execute()) {
                $message = 'Gán voucher cho khách hàng thành công!';
                $messageType = 'success';
            } else {
                $message = 'Lỗi: ' . $stmt->error;
                $messageType = 'danger';
            }
            $stmt->close();
        }
    }
}

// Lấy thông tin voucher để edit
$editVoucher = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM voucher WHERE voucher_id = ? AND deleted IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editVoucher = $result->fetch_assoc();
    $stmt->close();
    
    // Parse các trường dạng string thành array
    if ($editVoucher) {
        $editVoucher['customer_types_array'] = !empty($editVoucher['customer_types']) ? explode(',', $editVoucher['customer_types']) : [];
        $editVoucher['room_types_array'] = !empty($editVoucher['room_types']) ? explode(',', $editVoucher['room_types']) : [];
        $editVoucher['service_ids_array'] = !empty($editVoucher['service_ids']) ? explode(',', $editVoucher['service_ids']) : [];
        $editVoucher['valid_days_array'] = !empty($editVoucher['valid_days']) ? explode(',', $editVoucher['valid_days']) : [];
        $editVoucher['payment_methods_array'] = !empty($editVoucher['payment_methods']) ? explode(',', $editVoucher['payment_methods']) : [];
    }
}

// Phân trang và tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$pageNum = isset($_GET['pageNum']) ? intval($_GET['pageNum']) : 1;
$pageNum = max(1, $pageNum);
$perPage = 10;
$offset = ($pageNum - 1) * $perPage;

// Xây dựng WHERE clause
$where = "WHERE v.deleted IS NULL";
$params = [];
$types = '';

if ($search) {
    $where .= " AND (v.code LIKE ? OR v.name LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam]);
    $types .= 'ss';
}

if ($status_filter) {
    $where .= " AND v.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

// Đếm tổng số
$countQuery = "SELECT COUNT(*) as total FROM voucher v $where";
$countStmt = $mysqli->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalResult = $countStmt->get_result();
$total = $totalResult->fetch_assoc()['total'];
$countStmt->close();

// Lấy dữ liệu
$query = "SELECT v.*, 
    nv.ho_ten as created_by_name,
    (SELECT COUNT(*) FROM voucher_usage vu WHERE vu.voucher_id = v.voucher_id) as usage_count
    FROM voucher v
    LEFT JOIN nhan_vien nv ON v.created_by = nv.id_nhan_vien
    $where
    ORDER BY v.created_at DESC 
    LIMIT $perPage OFFSET $offset";

$stmt = $mysqli->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $vouchers = $result->fetch_all(MYSQLI_ASSOC);
} else {
    die("Lỗi query: " . $stmt->error);
}
$stmt->close();

// Lấy danh sách room types, services, customers cho form
$roomTypesResult = $mysqli->query("SELECT room_type_id, room_type_name FROM room_type WHERE deleted IS NULL ORDER BY room_type_name");
$roomTypes = $roomTypesResult->fetch_all(MYSQLI_ASSOC);

$servicesResult = $mysqli->query("SELECT service_id, service_name FROM service WHERE deleted IS NULL ORDER BY service_name");
$services = $servicesResult->fetch_all(MYSQLI_ASSOC);

$customersResult = $mysqli->query("SELECT customer_id, full_name, phone FROM customer WHERE deleted IS NULL ORDER BY full_name");
$customers = $customersResult->fetch_all(MYSQLI_ASSOC);

// Build base URL for pagination
$baseUrl = "index.php?page=voucher-manager";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urlencode($status_filter);
?>

<div class="main-content">
    <div class="content-header">
        <h1>Quản Lý Voucher</h1>
        <div class="row m-3">
            <div class="col-md-12">
                <?php if ($canCreateVoucher): ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVoucherModal">
                    <i class="fas fa-plus"></i> Thêm Voucher
                </button>
                <?php endif; ?>
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
            <input type="hidden" name="page" value="voucher-manager">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Tìm mã hoặc tên voucher..."
                            value="<?php echo h($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Đang
                            hoạt
                            động</option>
                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>
                            Tạm dừng
                        </option>
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
                    <th>Mã</th>
                    <th>Tên Voucher</th>
                    <th>Loại Giảm</th>
                    <th>Giá Trị</th>
                    <th>Điều Kiện</th>
                    <th>Thời Hạn</th>
                    <th>Đã Dùng</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vouchers)): ?>
                <tr>
                    <td colspan="9" class="text-center">Không có dữ liệu</td>
                </tr>
                <?php else: ?>
                <?php foreach ($vouchers as $voucher): ?>
                <tr>
                    <td><strong><?php echo h($voucher['code']); ?></strong></td>
                    <td><?php echo h($voucher['name']); ?></td>
                    <td>
                        <span class="badge bg-info">
                            <?php echo $voucher['discount_type'] == 'percent' ? '%' : 'VNĐ'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($voucher['discount_type'] == 'percent'): ?>
                        <?php echo number_format($voucher['discount_value'], 0); ?>%
                        <?php if ($voucher['max_discount']): ?>
                        <br><small>(Tối đa: <?php echo formatCurrency($voucher['max_discount']); ?>)</small>
                        <?php endif; ?>
                        <?php else: ?>
                        <?php echo formatCurrency($voucher['discount_value']); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <small>
                            <?php if ($voucher['min_order'] > 0): ?>
                            Đơn tối thiểu: <?php echo formatCurrency($voucher['min_order']); ?><br>
                            <?php endif; ?>
                            <?php if ($voucher['apply_to'] != 'all'): ?>
                            Áp dụng: <?php echo $voucher['apply_to'] == 'room' ? 'Phòng' : 'Dịch vụ'; ?><br>
                            <?php endif; ?>
                        </small>
                    </td>
                    <td>
                        <small>
                            <?php echo formatDate($voucher['start_date']); ?><br>
                            → <?php echo formatDate($voucher['end_date']); ?>
                        </small>
                    </td>
                    <td>
                        <?php echo $voucher['usage_count'] ?? 0; ?> / <?php echo $voucher['total_uses']; ?>
                    </td>
                    <td>
                        <span
                            class="badge <?php echo $voucher['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $voucher['status'] == 'active' ? 'Hoạt động' : 'Tạm dừng'; ?>
                        </span>
                        <?php if ($voucher['is_featured']): ?>
                        <span class="badge bg-warning">Nổi bật</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-info"
                            onclick="viewVoucherDetails(<?php echo $voucher['voucher_id']; ?>)" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php if ($canEditVoucher): ?>
                        <button class="btn btn-sm btn-outline-warning"
                            onclick="editVoucher(<?php echo $voucher['voucher_id']; ?>)" title="Sửa">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php endif; ?>
                        <?php if ($canDeleteVoucher): ?>
                        <button class="btn btn-sm btn-outline-danger"
                            onclick="deleteVoucher(<?php echo $voucher['voucher_id']; ?>)" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-outline-primary"
                            onclick="assignVoucher(<?php echo $voucher['voucher_id']; ?>)" title="Gán cho khách hàng">
                            <i class="fas fa-user-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary"
                            onclick="viewVoucherUsage(<?php echo $voucher['voucher_id']; ?>)" title="Lịch sử sử dụng">
                            <i class="fas fa-history"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php 
    if (function_exists('getPagination')) {
        echo getPagination($total, $perPage, $pageNum, $baseUrl);
    }
    ?>
</div>

<!-- Modal Thêm/Sửa Voucher -->
<div class="modal fade" id="addVoucherModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $editVoucher ? 'Sửa' : 'Thêm'; ?> Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="voucherForm" enctype="multipart/form-data">
                <?php if ($editVoucher): ?>
                <input type="hidden" name="voucher_id" value="<?php echo $editVoucher['voucher_id']; ?>">
                <?php endif; ?>

                <div class="modal-body">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs mb-3" id="voucherTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic"
                                type="button">Thông Tin Cơ Bản</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="discount-tab" data-bs-toggle="tab" data-bs-target="#discount"
                                type="button">Giảm Giá</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="condition-tab" data-bs-toggle="tab" data-bs-target="#condition"
                                type="button">Điều Kiện</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="time-tab" data-bs-toggle="tab" data-bs-target="#time"
                                type="button">Thời Hạn</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings"
                                type="button">Cài Đặt</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="voucherTabContent">
                        <!-- Tab 1: Thông Tin Cơ Bản -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mã Voucher *</label>
                                    <input type="text" class="form-control" name="code"
                                        value="<?php echo h($editVoucher['code'] ?? ''); ?>" required maxlength="20"
                                        placeholder="VD: SUMMER2024">
                                    <small class="text-muted">Mã phải duy nhất, tối đa 20 ký tự</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên Voucher *</label>
                                    <input type="text" class="form-control" name="name"
                                        value="<?php echo h($editVoucher['name'] ?? ''); ?>" required maxlength="100">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô Tả</label>
                                <textarea class="form-control" name="description" rows="3"
                                    maxlength="300"><?php echo h($editVoucher['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hình Ảnh</label>
                                <div class="image-upload-area" onclick="document.getElementById('voucherImage').click()"
                                    style="border: 2px dashed #ccc; padding: 20px; text-align: center; border-radius: 5px; cursor: pointer;">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Click để chọn ảnh</p>
                                    <small class="text-muted">hoặc kéo thả ảnh vào đây</small>
                                </div>
                                <input type="file" id="voucherImage" name="image" accept="image/*" style="display: none"
                                    onchange="previewImage(this, 'voucherPreview')" />
                                <input type="hidden" name="image" value="<?php echo h($editVoucher['image'] ?? ''); ?>"
                                    id="voucherImageHidden">
                                <?php if ($editVoucher && !empty($editVoucher['image'])): ?>
                                <img id="voucherPreview" class="image-preview mt-3"
                                    src="../../client/<?php echo h($editVoucher['image']); ?>"
                                    style="max-width: 100%; max-height: 200px; border-radius: 5px; display: block;" />
                                <?php else: ?>
                                <img id="voucherPreview" class="image-preview mt-3"
                                    style="display: none; max-width: 100%; max-height: 200px; border-radius: 5px;" />
                                <?php endif; ?>
                                <div class="mt-2">
                                    <small class="text-muted">Định dạng: JPG, PNG, GIF, WEBP. Kích thước tối đa:
                                        5MB</small>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Giảm Giá -->
                        <div class="tab-pane fade" id="discount" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Loại Giảm *</label>
                                    <select class="form-select" name="discount_type" id="discount_type" required>
                                        <option value="percent"
                                            <?php echo ($editVoucher['discount_type'] ?? 'percent') == 'percent' ? 'selected' : ''; ?>>
                                            Phần trăm (%)</option>
                                        <option value="fixed"
                                            <?php echo ($editVoucher['discount_type'] ?? '') == 'fixed' ? 'selected' : ''; ?>>
                                            Số tiền cố định (VNĐ)</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Giá Trị Giảm *</label>
                                    <input type="number" class="form-control" name="discount_value"
                                        value="<?php echo $editVoucher['discount_value'] ?? ''; ?>" required step="0.01"
                                        min="0">
                                    <small class="text-muted" id="discount_hint">Nhập số phần trăm
                                        (0-100)</small>
                                </div>
                                <div class="col-md-4 mb-3" id="max_discount_container"
                                    style="<?php echo ($editVoucher['discount_type'] ?? 'percent') == 'fixed' ? 'display: none;' : ''; ?>">
                                    <label class="form-label">Giảm Tối Đa (VNĐ)</label>
                                    <input type="number" class="form-control" name="max_discount"
                                        value="<?php echo $editVoucher['max_discount'] ?? ''; ?>" step="0.01" min="0"
                                        placeholder="Chỉ áp dụng cho %">
                                    <small class="text-muted">Chỉ áp dụng khi giảm theo %</small>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Điều Kiện -->
                        <div class="tab-pane fade" id="condition" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Đơn Hàng Tối Thiểu (VNĐ)</label>
                                    <input type="number" class="form-control" name="min_order"
                                        value="<?php echo $editVoucher['min_order'] ?? '0'; ?>" step="0.01" min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Áp Dụng Cho</label>
                                    <select class="form-select" name="apply_to">
                                        <option value="all"
                                            <?php echo ($editVoucher['apply_to'] ?? 'all') == 'all' ? 'selected' : ''; ?>>
                                            Tất cả</option>
                                        <option value="room"
                                            <?php echo ($editVoucher['apply_to'] ?? '') == 'room' ? 'selected' : ''; ?>>
                                            Chỉ phòng</option>
                                        <option value="service"
                                            <?php echo ($editVoucher['apply_to'] ?? '') == 'service' ? 'selected' : ''; ?>>
                                            Chỉ dịch vụ</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại Khách Hàng</label>
                                    <div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="customer_types[]"
                                                value="VIP"
                                                <?php echo (isset($editVoucher['customer_types_array']) && in_array('VIP', $editVoucher['customer_types_array'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label">VIP</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="customer_types[]"
                                                value="Corporate"
                                                <?php echo (isset($editVoucher['customer_types_array']) && in_array('Corporate', $editVoucher['customer_types_array'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Corporate</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="customer_types[]"
                                                value="Regular"
                                                <?php echo (isset($editVoucher['customer_types_array']) && in_array('Regular', $editVoucher['customer_types_array'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Regular</label>
                                        </div>
                                    </div>
                                    <small class="text-muted">Để trống = áp dụng cho tất cả</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số Đêm Tối Thiểu</label>
                                    <input type="number" class="form-control" name="min_nights"
                                        value="<?php echo $editVoucher['min_nights'] ?? ''; ?>" min="1"
                                        placeholder="Để trống = không giới hạn">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số Phòng Tối Thiểu</label>
                                    <input type="number" class="form-control" name="min_rooms"
                                        value="<?php echo $editVoucher['min_rooms'] ?? ''; ?>" min="1"
                                        placeholder="Để trống = không giới hạn">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại Phòng</label>
                                    <select class="form-select" name="room_types[]" multiple size="5">
                                        <?php foreach ($roomTypes as $rt): ?>
                                        <option value="<?php echo $rt['room_type_id']; ?>"
                                            <?php echo (isset($editVoucher['room_types_array']) && in_array($rt['room_type_id'], $editVoucher['room_types_array'])) ? 'selected' : ''; ?>>
                                            <?php echo h($rt['room_type_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Giữ Ctrl để chọn nhiều. Để trống = tất cả loại
                                        phòng</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dịch Vụ</label>
                                <select class="form-select" name="service_ids[]" multiple size="5">
                                    <?php foreach ($services as $sv): ?>
                                    <option value="<?php echo $sv['service_id']; ?>"
                                        <?php echo (isset($editVoucher['service_ids_array']) && in_array($sv['service_id'], $editVoucher['service_ids_array'])) ? 'selected' : ''; ?>>
                                        <?php echo h($sv['service_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Giữ Ctrl để chọn nhiều. Để trống = tất cả dịch
                                    vụ</small>
                            </div>
                        </div>

                        <!-- Tab 4: Thời Hạn -->
                        <div class="tab-pane fade" id="time" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày Bắt Đầu *</label>
                                    <input type="date" class="form-control" name="start_date"
                                        value="<?php echo $editVoucher['start_date'] ?? ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày Kết Thúc *</label>
                                    <input type="date" class="form-control" name="end_date"
                                        value="<?php echo $editVoucher['end_date'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày Hợp Lệ Trong Tuần</label>
                                    <div>
                                        <?php 
                                        $days = ['Mon' => 'Thứ 2', 'Tue' => 'Thứ 3', 'Wed' => 'Thứ 4', 'Thu' => 'Thứ 5', 'Fri' => 'Thứ 6', 'Sat' => 'Thứ 7', 'Sun' => 'Chủ nhật'];
                                        foreach ($days as $dayCode => $dayName): 
                                        ?>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="valid_days[]"
                                                value="<?php echo $dayCode; ?>"
                                                <?php echo (isset($editVoucher['valid_days_array']) && in_array($dayCode, $editVoucher['valid_days_array'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label"><?php echo $dayName; ?></label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <small class="text-muted">Để trống = tất cả các ngày</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giờ Hợp Lệ</label>
                                    <input type="text" class="form-control" name="valid_hours"
                                        value="<?php echo h($editVoucher['valid_hours'] ?? ''); ?>"
                                        placeholder="VD: 14:00-18:00">
                                    <small class="text-muted">Định dạng: HH:MM-HH:MM. Để trống = tất cả
                                        giờ</small>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 5: Cài Đặt -->
                        <div class="tab-pane fade" id="settings" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tổng Số Lần Sử Dụng *</label>
                                    <input type="number" class="form-control" name="total_uses"
                                        value="<?php echo $editVoucher['total_uses'] ?? '100'; ?>" required min="1">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mỗi Khách Dùng Tối Đa</label>
                                    <input type="number" class="form-control" name="per_customer"
                                        value="<?php echo $editVoucher['per_customer'] ?? '1'; ?>" min="1">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Trạng Thái *</label>
                                    <select class="form-select" name="status" required>
                                        <option value="active"
                                            <?php echo ($editVoucher['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>
                                            Hoạt động</option>
                                        <option value="inactive"
                                            <?php echo ($editVoucher['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>
                                            Tạm dừng</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Độ Ưu Tiên</label>
                                    <input type="number" class="form-control" name="priority"
                                        value="<?php echo $editVoucher['priority'] ?? '0'; ?>" min="0">
                                    <small class="text-muted">Số càng cao càng ưu tiên</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phương Thức Thanh Toán</label>
                                    <div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="payment_methods[]"
                                                value="Cash"
                                                <?php echo (isset($editVoucher['payment_methods_array']) && in_array('Cash', $editVoucher['payment_methods_array'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Tiền mặt</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="payment_methods[]"
                                                value="Bank Transfer"
                                                <?php echo (isset($editVoucher['payment_methods_array']) && in_array('Bank Transfer', $editVoucher['payment_methods_array'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Chuyển khoản</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="payment_methods[]"
                                                value="Credit Card"
                                                <?php echo (isset($editVoucher['payment_methods_array']) && in_array('Credit Card', $editVoucher['payment_methods_array'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label">Thẻ tín dụng</label>
                                        </div>
                                    </div>
                                    <small class="text-muted">Để trống = tất cả phương thức</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                                        id="is_featured"
                                        <?php echo ($editVoucher['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_featured">Nổi bật</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_public" value="1"
                                        id="is_public" <?php echo ($editVoucher['is_public'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_public">Công khai</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="auto_apply" value="1"
                                        id="auto_apply"
                                        <?php echo ($editVoucher['auto_apply'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="auto_apply">Tự động áp dụng</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_stackable" value="1"
                                        id="is_stackable"
                                        <?php echo ($editVoucher['is_stackable'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_stackable">Có thể dùng chung với
                                        voucher
                                        khác</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-primary-custom"
                        name="<?php echo $editVoucher ? 'update_voucher' : 'add_voucher'; ?>">
                        <?php echo $editVoucher ? 'Cập nhật' : 'Thêm'; ?> Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Gán Voucher Cho Khách Hàng -->
<div class="modal fade" id="assignVoucherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gán Voucher Cho Khách Hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="voucher_id" id="assign_voucher_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chọn Khách Hàng *</label>
                        <select class="form-select" name="customer_id" required>
                            <option value="">-- Chọn khách hàng --</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['customer_id']; ?>">
                                <?php echo h($customer['full_name']); ?> - <?php echo h($customer['phone']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ngày Hết Hạn Riêng</label>
                        <input type="date" class="form-control" name="expires_at">
                        <small class="text-muted">Để trống = dùng ngày hết hạn của voucher</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi Chú</label>
                        <textarea class="form-control" name="note" rows="2" maxlength="200"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="assign_voucher_customer" class="btn-primary-custom">Gán
                        Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xem Chi Tiết Voucher -->
<div class="modal fade" id="viewVoucherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi Tiết Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="voucherDetailsContent">
                <!-- Nội dung sẽ được load bằng AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lịch Sử Sử Dụng Voucher -->
<div class="modal fade" id="voucherUsageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lịch Sử Sử Dụng Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="voucherUsageContent">
                <!-- Nội dung sẽ được load bằng AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle max_discount field based on discount_type
document.addEventListener('DOMContentLoaded', function() {
    const discountType = document.getElementById('discount_type');
    const maxDiscountContainer = document.getElementById('max_discount_container');
    const discountHint = document.getElementById('discount_hint');

    if (discountType && maxDiscountContainer) {
        discountType.addEventListener('change', function() {
            if (this.value === 'percent') {
                maxDiscountContainer.style.display = 'block';
                if (discountHint) discountHint.textContent = 'Nhập số phần trăm (0-100)';
            } else {
                maxDiscountContainer.style.display = 'none';
                if (discountHint) discountHint.textContent = 'Nhập số tiền (VNĐ)';
            }
        });
    }

    // Auto-open edit modal
    <?php if ($editVoucher): ?>
    const editModal = new bootstrap.Modal(document.getElementById('addVoucherModal'));
    editModal.show();
    <?php endif; ?>
});

function editVoucher(id) {
    window.location.href = 'index.php?page=voucher-manager&action=edit&id=' + id;
}

function deleteVoucher(id) {
    if (confirm('Bạn có chắc chắn muốn xóa voucher này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="voucher_id" value="' + id + '">' +
            '<input type="hidden" name="delete_voucher" value="1">';
        document.body.appendChild(form);
        form.submit();
    }
}

function assignVoucher(id) {
    document.getElementById('assign_voucher_id').value = id;
    const modal = new bootstrap.Modal(document.getElementById('assignVoucherModal'));
    modal.show();
}

function viewVoucherDetails(id) {
    fetch('api/voucher-api.php?action=view&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('voucherDetailsContent').innerHTML = data.html;
                const modal = new bootstrap.Modal(document.getElementById('viewVoucherModal'));
                modal.show();
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi tải dữ liệu.');
        });
}

function viewVoucherUsage(id) {
    fetch('api/voucher-api.php?action=usage&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('voucherUsageContent').innerHTML = data.html;
                const modal = new bootstrap.Modal(document.getElementById('voucherUsageModal'));
                modal.show();
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi tải dữ liệu.');
        });
}

// Reset form when modal closes
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addVoucherModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            const url = new URL(window.location);
            url.searchParams.delete('action');
            url.searchParams.delete('id');
            window.history.replaceState({}, '', url);

            const form = document.getElementById('voucherForm');
            if (form) {
                form.reset();
                // Reset tabs to first
                const firstTab = document.getElementById('basic-tab');
                if (firstTab) {
                    firstTab.click();
                }
            }
        });
    }

    // Reset form when "Add" button is clicked
    document.querySelectorAll('[data-bs-target="#addVoucherModal"]').forEach(button => {
        button.addEventListener('click', function() {
            const url = new URL(window.location);
            url.searchParams.delete('action');
            url.searchParams.delete('id');
            window.history.replaceState({}, '', url);
        });
    });
});
</script>