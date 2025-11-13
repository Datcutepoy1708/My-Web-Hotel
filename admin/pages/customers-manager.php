<?php
// Xử lý CRUD
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_customer'])) {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $username = trim($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $gender = $_POST['gender'] ?? 'Other';
        $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
        $nationality = trim($_POST['nationality'] ?? '');
        $id_card = trim($_POST['id_card'] ?? '');
        $customer_type = $_POST['customer_type'] ?? 'Regular';
        $account_status = $_POST['account_status'] ?? 'Active';
        
        $stmt = $mysqli->prepare("INSERT INTO customer (full_name, email, phone, username, password, gender, date_of_birth, nationality, id_card, customer_type, account_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $full_name, $email, $phone, $username, $password, $gender, $date_of_birth, $nationality, $id_card, $customer_type, $account_status);
        
        if ($stmt->execute()) {
            $message = 'Thêm khách hàng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }
    
    if (isset($_POST['update_customer'])) {
        $customer_id = intval($_POST['customer_id']);
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $gender = $_POST['gender'] ?? 'Other';
        $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
        $nationality = trim($_POST['nationality'] ?? '');
        $id_card = trim($_POST['id_card'] ?? '');
        $customer_type = $_POST['customer_type'] ?? 'Regular';
        $account_status = $_POST['account_status'] ?? 'Active';
        
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE customer SET full_name=?, email=?, phone=?, password=?, gender=?, date_of_birth=?, nationality=?, id_card=?, customer_type=?, account_status=? WHERE customer_id=? AND deleted IS NULL");
            $stmt->bind_param("ssssssssssi", $full_name, $email, $phone, $password, $gender, $date_of_birth, $nationality, $id_card, $customer_type, $account_status, $customer_id);
        } else {
            $stmt = $mysqli->prepare("UPDATE customer SET full_name=?, email=?, phone=?, gender=?, date_of_birth=?, nationality=?, id_card=?, customer_type=?, account_status=? WHERE customer_id=? AND deleted IS NULL");
            $stmt->bind_param("sssssssssi", $full_name, $email, $phone, $gender, $date_of_birth, $nationality, $id_card, $customer_type, $account_status, $customer_id);
        }
        
        if ($stmt->execute()) {
            $message = 'Cập nhật khách hàng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_customer'])) {
        $customer_id = intval($_POST['customer_id']);
        $stmt = $mysqli->prepare("UPDATE customer SET deleted = NOW() WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        
        if ($stmt->execute()) {
            $message = 'Xóa khách hàng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }
}

// Lấy thông tin khách hàng để edit
$editCustomer = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM customer WHERE customer_id = ? AND deleted IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editCustomer = $result->fetch_assoc();
    $stmt->close();
}

// Phân trang và tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type_filter = isset($_GET['type']) ? trim($_GET['type']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Xây dựng query
$where = "WHERE c.deleted IS NULL";
$params = [];
$types = '';

if ($search) {
    $where .= " AND (c.full_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR c.username LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    $types .= 'ssss';
}

if ($type_filter) {
    $where .= " AND c.customer_type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

$orderBy = "ORDER BY c.created_at DESC";
switch ($sort) {
    case 'oldest':
        $orderBy = "ORDER BY c.created_at ASC";
        break;
    case 'name_asc':
        $orderBy = "ORDER BY c.full_name ASC";
        break;
    case 'name_desc':
        $orderBy = "ORDER BY c.full_name DESC";
        break;
    case 'newest':
    default:
        $orderBy = "ORDER BY c.created_at DESC";
        break;
}

// Đếm tổng số
$countQuery = "SELECT COUNT(*) as total FROM customer c $where";
$countStmt = $mysqli->prepare($countQuery);
if (!$countStmt) {
    error_log("Prepare failed: " . $mysqli->error);
    $message = "Lỗi truy vấn: " . $mysqli->error;
    $messageType = 'danger';
    $total = 0;
    $customers = [];
} else {
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    if (!$countStmt->execute()) {
        error_log("Execute failed: " . $countStmt->error);
        $message = "Lỗi thực thi: " . $countStmt->error;
        $messageType = 'danger';
        $total = 0;
    } else {
        $totalResult = $countStmt->get_result();
        $total = $totalResult->fetch_assoc()['total'];
    }
    $countStmt->close();
}

// Lấy dữ liệu
$customers = [];
if ($total > 0) {
    $query = "SELECT c.*, 
        (SELECT COUNT(*) FROM booking b WHERE b.customer_id = c.customer_id AND b.deleted IS NULL) as booking_count,
        (SELECT COALESCE(SUM(i.total_amount), 0) FROM invoice i 
         INNER JOIN booking b ON i.booking_id = b.booking_id 
         WHERE b.customer_id = c.customer_id AND i.deleted IS NULL) as total_spending
        FROM customer c 
        $where 
        $orderBy 
        LIMIT ? OFFSET ?";
        
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $mysqli->error);
        $message = "Lỗi truy vấn: " . $mysqli->error;
        $messageType = 'danger';
    } else {
        if (!empty($params)) {
            $types .= 'ii';
            $params[] = $perPage;
            $params[] = $offset;
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->bind_param("ii", $perPage, $offset);
        }
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            $message = "Lỗi thực thi: " . $stmt->error;
            $messageType = 'danger';
        } else {
            $result = $stmt->get_result();
            $customers = $result->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
    }
}

// Build base URL for pagination
$baseUrl = "index.php?page=customers-manager";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($type_filter) $baseUrl .= "&type=" . urlencode($type_filter);
if ($sort) $baseUrl .= "&sort=" . urlencode($sort);
?>

<div class="main-content">
    <div class="content-header">
        <h1>Quản Lý Khách Hàng</h1>
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
            <input type="hidden" name="page" value="customers-manager">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Tìm kiếm khách hàng..." value="<?php echo h($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="type">
                        <option value="">Tất cả hạng</option>
                        <option value="Regular" <?php echo $type_filter == 'Regular' ? 'selected' : ''; ?>>Regular</option>
                        <option value="VIP" <?php echo $type_filter == 'VIP' ? 'selected' : ''; ?>>VIP</option>
                        <option value="Corporate" <?php echo $type_filter == 'Corporate' ? 'selected' : ''; ?>>Corporate</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="sort">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                        <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Cũ nhất</option>
                        <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Tên (A → Z)</option>
                        <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Tên (Z → A)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </div>
        </form>
        <div class="row mt-3">
            <div class="col-md-12">
                <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                    <i class="fas fa-plus"></i> Thêm Khách Hàng
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Họ Tên</th>
                    <th>Hạng</th>
                    <th>Số Điện Thoại</th>
                    <th>Email</th>
                    <th>Trạng thái</th>
                    <th>Điểm tích lũy</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="7" class="text-center">Không có dữ liệu</td>
                </tr>
                <?php else: ?>
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td>
                        <div class="employee-info">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($customer['full_name']); ?>&background=d4b896&color=fff"
                                alt="Avatar" class="employee-avatar">
                            <div>
                                <p class="employee-name"><?php echo h($customer['full_name']); ?></p>
                                <p class="employee-id">ID: <?php echo $customer['customer_id']; ?></p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php
                        $badgeClass = 'badge';
                        if ($customer['customer_type'] == 'VIP') $badgeClass = 'badge badge-diamond';
                        elseif ($customer['customer_type'] == 'Corporate') $badgeClass = 'badge badge-gold';
                        ?>
                        <span class="<?php echo $badgeClass; ?>"><?php echo h($customer['customer_type']); ?></span>
                    </td>
                    <td><?php echo h($customer['phone'] ?: '-'); ?></td>
                    <td><?php echo h($customer['email']); ?></td>
                    <td>
                        <span class="badge <?php echo $customer['account_status'] == 'Active' ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo h($customer['account_status']); ?>
                        </span>
                    </td>
                    <td><?php echo number_format($customer['loyalty_points']); ?> điểm</td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                            data-bs-target="#viewCustomerModal<?php echo $customer['customer_id']; ?>" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="editCustomer(<?php echo $customer['customer_id']; ?>)" title="Sửa">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCustomer(<?php echo $customer['customer_id']; ?>)" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                
                <!-- View Modal for each customer -->
                <div class="modal fade" id="viewCustomerModal<?php echo $customer['customer_id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-user"></i> Thông Tin Khách Hàng</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-4">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($customer['full_name']); ?>&background=d4b896&color=fff&size=120"
                                        alt="Avatar" class="avatar-preview">
                                    <h5 class="mt-2"><?php echo h($customer['full_name']); ?></h5>
                                    <p class="text-muted">ID: <?php echo $customer['customer_id']; ?></p>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6"><strong>Email:</strong> <?php echo h($customer['email']); ?></div>
                                    <div class="col-md-6"><strong>Số điện thoại:</strong> <?php echo h($customer['phone'] ?: '-'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6"><strong>Ngày sinh:</strong> <?php echo formatDate($customer['date_of_birth']); ?></div>
                                    <div class="col-md-6"><strong>Giới tính:</strong> <?php echo h($customer['gender']); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6"><strong>Quốc tịch:</strong> <?php echo h($customer['nationality'] ?: '-'); ?></div>
                                    <div class="col-md-6"><strong>CMND/CCCD:</strong> <?php echo h($customer['id_card'] ?: '-'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6"><strong>Hạng:</strong> <?php echo h($customer['customer_type']); ?></div>
                                    <div class="col-md-6"><strong>Trạng thái:</strong> <?php echo h($customer['account_status']); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6"><strong>Điểm tích lũy:</strong> <?php echo number_format($customer['loyalty_points']); ?></div>
                                    <div class="col-md-6"><strong>Ngày tạo:</strong> <?php echo formatDateTime($customer['created_at']); ?></div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="button" class="btn btn-primary" onclick="editCustomer(<?php echo $customer['customer_id']; ?>)">
                                    <i class="fas fa-edit"></i> Chỉnh Sửa
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

    <!-- Pagination -->
    <?php echo getPagination($total, $perPage, $page, $baseUrl); ?>

    <!-- Modal: Thêm/Sửa khách hàng -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> <?php echo $editCustomer ? 'Sửa' : 'Thêm'; ?> Khách Hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <?php if ($editCustomer): ?>
                        <input type="hidden" name="customer_id" value="<?php echo $editCustomer['customer_id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Họ và Tên *</label>
                                <input type="text" class="form-control" name="full_name" 
                                    value="<?php echo h($editCustomer['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" 
                                    value="<?php echo h($editCustomer['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số Điện Thoại</label>
                                <input type="text" class="form-control" name="phone" 
                                    value="<?php echo h($editCustomer['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" class="form-control" name="username" 
                                    value="<?php echo h($editCustomer['username'] ?? ''); ?>" 
                                    <?php echo $editCustomer ? 'readonly' : 'required'; ?>>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mật khẩu <?php echo $editCustomer ? '' : '*'; ?></label>
                                <input type="password" class="form-control" name="password" 
                                    <?php echo $editCustomer ? '' : 'required'; ?> 
                                    placeholder="<?php echo $editCustomer ? 'Để trống nếu không đổi' : ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày Sinh</label>
                                <input type="date" class="form-control" name="date_of_birth" 
                                    value="<?php echo $editCustomer['date_of_birth'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giới Tính</label>
                                <select class="form-select" name="gender">
                                    <option value="Male" <?php echo ($editCustomer['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Female" <?php echo ($editCustomer['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="Other" <?php echo ($editCustomer['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Quốc tịch</label>
                                <input type="text" class="form-control" name="nationality" 
                                    value="<?php echo h($editCustomer['nationality'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CMND/CCCD</label>
                                <input type="text" class="form-control" name="id_card" 
                                    value="<?php echo h($editCustomer['id_card'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hạng khách hàng</label>
                                <select class="form-select" name="customer_type">
                                    <option value="Regular" <?php echo ($editCustomer['customer_type'] ?? 'Regular') == 'Regular' ? 'selected' : ''; ?>>Regular</option>
                                    <option value="VIP" <?php echo ($editCustomer['customer_type'] ?? '') == 'VIP' ? 'selected' : ''; ?>>VIP</option>
                                    <option value="Corporate" <?php echo ($editCustomer['customer_type'] ?? '') == 'Corporate' ? 'selected' : ''; ?>>Corporate</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Trạng thái tài khoản</label>
                            <select class="form-select" name="account_status">
                                <option value="Active" <?php echo ($editCustomer['account_status'] ?? 'Active') == 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Locked" <?php echo ($editCustomer['account_status'] ?? '') == 'Locked' ? 'selected' : ''; ?>>Locked</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary" name="<?php echo $editCustomer ? 'update_customer' : 'add_customer'; ?>">
                            <?php echo $editCustomer ? 'Cập nhật' : 'Thêm'; ?> Khách Hàng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editCustomer(id) {
    window.location.href = 'index.php?page=customers-manager&action=edit&id=' + id;
}

function deleteCustomer(id) {
    if (confirm('Bạn có chắc chắn muốn xóa khách hàng này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="customer_id" value="' + id + '">' +
                       '<input type="hidden" name="delete_customer" value="1">';
        document.body.appendChild(form);
        form.submit();
    }
}

<?php if ($editCustomer): ?>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
    modal.show();
});
<?php endif; ?>
</script>
