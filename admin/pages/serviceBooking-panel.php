<?php
// Include helper function để tự động tạo hóa đơn
require_once __DIR__ . '/../includes/invoice_helper.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$messageType = '';

// Thêm booking dịch vụ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_booking_service'])) {
        $customer_id = intval($_POST['customer_id']);
        $service_id = intval($_POST['service_id']);
        $quantity = intval($_POST['quantity']);
        $usage_date = $_POST['usage_date'];
        $usage_time = $_POST['usage_time'];
        $booking_id = !empty($_POST['booking_id']) ? intval($_POST['booking_id']) : null; // Cho phép NULL
        $amount = floatval($_POST['amount']); // Nên dùng floatval thay vì intval cho tiền
        $notes = trim($_POST['note'] ?? '');
        $status = $_POST['status'] ?? 'confirmed';

        // Xử lý validate dữ liệu
        $errors = [];
        if (empty($customer_id) || $customer_id <= 0) {
            $errors[] = "Khách hàng không được để trống";
        }
        if (empty($service_id) || $service_id <= 0) {
            $errors[] = "Dịch vụ không được để trống";
        }
        if (empty($quantity) || $quantity <= 0) {
            $errors[] = "Số lượng phải lớn hơn 0";
        }
        if (empty($usage_date)) {
            $errors[] = "Ngày sử dụng không được để trống";
        }
        if (empty($usage_time)) {
            $errors[] = "Giờ sử dụng không được để trống";
        }
        if (empty($amount) || $amount <= 0) {
            $errors[] = "Số tiền phải lớn hơn 0";
        }

        // Nếu có booking_id, kiểm tra xem booking có tồn tại không
        if ($booking_id) {
            $check_booking = $mysqli->prepare("SELECT booking_id FROM booking WHERE booking_id = ?");
            $check_booking->bind_param("i", $booking_id);
            $check_booking->execute();
            $result = $check_booking->get_result();
            if ($result->num_rows == 0) {
                $errors[] = "Booking ID không tồn tại";
            }
            $check_booking->close();
        }

        if (empty($errors)) {
            // INSERT với booking_id có thể NULL
            // Lấy unit_price từ service để lưu vào booking_service
            $service_stmt = $mysqli->prepare("SELECT price, unit FROM service WHERE service_id = ?");
            $service_stmt->bind_param("i", $service_id);
            $service_stmt->execute();
            $service_result = $service_stmt->get_result();
            $service_data = $service_result->fetch_assoc();
            $service_stmt->close();

            if (!$service_data) {
                $errors[] = "Không tìm thấy thông tin dịch vụ";
            }
        }

        if (empty($errors)) {
            $unit_price = $service_data['price'] ?? 0;
            $unit = $service_data['unit'] ?? '';

            // Xử lý booking_id - nếu không có thì không insert cột booking_id
            if ($booking_id) {
                $stmt = $mysqli->prepare("INSERT INTO booking_service (customer_id, service_id, quantity, usage_date, usage_time, booking_id, amount, unit_price, unit, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiissiddsss", $customer_id, $service_id, $quantity, $usage_date, $usage_time, $booking_id, $amount, $unit_price, $unit, $notes, $status);
            } else {
                // Không có booking_id - chỉ booking dịch vụ, không insert cột booking_id
                $stmt = $mysqli->prepare("INSERT INTO booking_service (customer_id, service_id, quantity, usage_date, usage_time, amount, unit_price, unit, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("iiissddsss", $customer_id, $service_id, $quantity, $usage_date, $usage_time, $amount, $unit_price, $unit, $notes, $status);
            }

            if ($stmt->execute()) {
                $booking_service_id = $stmt->insert_id;
                $message = 'Thêm booking dịch vụ thành công!';
                $messageType = 'success';

                // Tự động tạo hóa đơn nếu status = confirmed
                if ($status === 'confirmed') {
                    $invoice_id = createInvoiceForServiceBooking($mysqli, $booking_service_id);
                    if ($invoice_id) {
                        $message .= ' Hóa đơn đã được tạo tự động (ID: ' . $invoice_id . ')';
                    }
                }

                $action = '';
            } else {
                $message = 'Lỗi: ' . $stmt->error;
                $messageType = 'danger';
            }
            $stmt->close();
        } else {
            $message = implode('<br>', $errors);
            $messageType = 'danger';
        }
    }
    if (isset($_POST['update_service_booking'])) {
        $booking_service_id = intval($_POST['booking_service_id']);
        $customer_id = intval($_POST['customer_id']);
        $service_id = intval($_POST['service_id']);
        $quantity = intval($_POST['quantity']);
        $usage_date = $_POST['usage_date'];
        $usage_time = $_POST['usage_time'];
        $booking_id = !empty($_POST['booking_id']) ? intval($_POST['booking_id']) : null;
        $amount = floatval($_POST['amount']);
        $notes = trim($_POST['note'] ?? '');
        $status = $_POST['status'] ?? 'confirmed';

        // Validate dữ liệu
        $errors = [];
        if (empty($customer_id) || $customer_id <= 0) {
            $errors[] = "Khách hàng không được để trống";
        }
        if (empty($service_id) || $service_id <= 0) {
            $errors[] = "Dịch vụ không được để trống";
        }
        if (empty($quantity) || $quantity <= 0) {
            $errors[] = "Số lượng phải lớn hơn 0";
        }
        if (empty($usage_date)) {
            $errors[] = "Ngày sử dụng không được để trống";
        }
        if (empty($usage_time)) {
            $errors[] = "Giờ sử dụng không được để trống";
        }
        if (empty($amount) || $amount <= 0) {
            $errors[] = "Số tiền phải lớn hơn 0";
        }

        // Nếu có booking_id, kiểm tra xem booking có tồn tại không
        if ($booking_id) {
            $check_booking = $mysqli->prepare("SELECT booking_id FROM booking WHERE booking_id = ?");
            $check_booking->bind_param("i", $booking_id);
            $check_booking->execute();
            $result = $check_booking->get_result();
            if ($result->num_rows == 0) {
                $errors[] = "Booking ID không tồn tại";
            }
            $check_booking->close();
        }

        if (empty($errors)) {
            // Lấy unit_price từ service để cập nhật vào booking_service
            $service_stmt = $mysqli->prepare("SELECT price, unit FROM service WHERE service_id = ?");
            $service_stmt->bind_param("i", $service_id);
            $service_stmt->execute();
            $service_result = $service_stmt->get_result();
            $service_data = $service_result->fetch_assoc();
            $service_stmt->close();

            $unit_price = $service_data['price'] ?? 0;
            $unit = $service_data['unit'] ?? '';

            // UPDATE - không thay đổi booking_id nếu đã có, chỉ cập nhật các trường khác
            // Nếu booking_id đã tồn tại trong record, giữ nguyên; nếu không có thì không set
            $stmt = $mysqli->prepare("UPDATE booking_service SET customer_id=?, service_id=?, quantity=?, usage_date=?, usage_time=?, amount=?, unit_price=?, unit=?, notes=?, status=? WHERE booking_service_id=? AND deleted IS NULL");
            $stmt->bind_param("iiissddsssi", $customer_id, $service_id, $quantity, $usage_date, $usage_time, $amount, $unit_price, $unit, $notes, $status, $booking_service_id);

            if ($stmt->execute()) {
                $message = 'Cập nhật booking dịch vụ thành công!';
                $messageType = 'success';
                
                // Tự động tạo hóa đơn nếu status = confirmed và chưa có hóa đơn
                if ($status === 'confirmed') {
                    $invoice_id = createInvoiceForServiceBooking($mysqli, $booking_service_id);
                    if ($invoice_id) {
                        $message .= ' Hóa đơn đã được tạo tự động (ID: ' . $invoice_id . ')';
                    }
                }
                
                $action = '';
            } else {
                $message = 'Lỗi: ' . $stmt->error;
                $messageType = 'danger';
            }
            $stmt->close();
        } else {
            $message = implode('<br>', $errors);
            $messageType = 'danger';
        }
    }
    if (isset($_POST['delete_booking_service'])) {
        $booking_service_id = intval($_POST['booking_service_id']);
        // Kiểm tra booking_service có liên kết đến invoice hay ko
        $check_stmt = $mysqli->prepare("
        SELECT i.status, i.invoice_id
        FROM booking_service bs
        LEFT JOIN invoice i ON(i.booking_id=bs.booking_id OR i.customer_id=bs.customer_id)
        WHERE bs.booking_service_id = ? AND i.deleted IS NULL
        ");
        $check_stmt->bind_param("i", $booking_service_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $invoice_data = $result->fetch_assoc();
        $check_stmt->close();

        if ($invoice_data && $invoice_data['status'] == 'Paid') {
            $message = 'Không thể xóa!Dịch vụ này đã có hóa đơn thanh toán';
            $messageType = 'warning';
        } else {
            // Soft delete booking_service
            $stmt = $mysqli->prepare("UPDATE booking_service SET deleted=NOW() WHERE booking_service_id=?");
            $stmt->bind_param("i", $booking_service_id);
            if ($stmt->execute()) {
                $message = 'Xóa booking dịch vụ thành công';
                $messageType = 'success';

                if ($invoice_data && $invoice_data['status'] == 'Unpaid') {
                    $invoice_id = $invoice_data['invoice_id'];
                    $update_invoice = $mysqli->prepare("
                UPDATE invoice i 
                SET i.service_charge = (
                SELECT COALESCE(SUM(bs.amount),0)
                FROM booking_service bs
                WHERE (bs.booking_id=i.booking_id OR bs.customer_id=i.customer_id)
                AND bs.deleted IS NULL
                ),
                i.total_amount = i.room_charge + (
                 SELECT COALESCE(SUM(bs.amount),0)
                 FROM booking_service bs
                 WHERE (bs.booking_id=i.booking_id OR bs.customer_id=i.customer_id)
                 AND bs.deleted IS NULL
                ) + i.vat +i.other_fees
                 WHERE i.invoice_id=?
                ");
                    $update_invoice->bind_param("i", $invoice_id);
                    $update_invoice->execute();
                    $update_invoice->close();

                    $message = 'Hóa đơn đã được cập nhật lại tổng tiền';
                }
            } else {
                $message = 'Lỗi: ' . $stmt->error;
                $messageType = 'danger';
            }
            $stmt->close();
        }
    }
}
// Lấy ra thông tin booking service để edit
$editBookingService = null;
$editError = '';

if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id <= 0) {
        $editError = 'ID không hợp lệ';
        $action = '';
    } else {
        $stmt = $mysqli->prepare(
            "SELECT bs.*,
            c.customer_id,c.phone,c.full_name,c.email,
            s.service_id,s.service_name,s.price as price ,s.description,
            b.booking_id,b.check_in_date,b.check_out_date,
            r.room_number
            FROM booking_service bs
            LEFT JOIN customer c ON bs.customer_id=c.customer_id
            LEFT JOIN service s ON bs.service_id=s.service_id
            LEFT JOIN booking b ON bs.booking_id=b.booking_id
            LEFT JOIN room r ON r.room_id=b.room_id
            WHERE bs.booking_service_id=? AND bs.deleted IS NULL
            "
        );
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $editBookingService = $result->fetch_assoc();

                    if ($editBookingService['booking_id']) {
                        // Trường hợp có booking phòng
                        $invoice_stmt = $mysqli->prepare(
                            "SELECT invoice_id, status, total_amount
                            FROM invoice 
                            WHERE booking_id=? AND deleted IS NULL
                            LIMIT 1
                            "
                        );
                        $invoice_stmt->bind_param("i", $editBookingService['booking_id']);
                    } else {
                        // Trường hợp chỉ có dịch vụ không có booking phòng
                        $invoice_stmt = $mysqli->prepare(
                            "SELECT invoice_id,status, total_amount
                            FROM invoice 
                            WHERE customer_id=?  AND booking_id IS NULL AND deleted IS NULL
                            LIMIT 1"
                        );
                        $invoice_stmt->bind_param("i", $editBookingService['customer_id']);
                    }
                    $invoice_stmt->execute();
                    $invoice_result = $invoice_stmt->get_result();
                    $editBookingService['invoice'] = $invoice_result->fetch_assoc();
                    $invoice_stmt->close();

                    // Thêm flag để biết có phòng hay không
                    $editBookingService['has_room_booking'] = !empty($editBookingService['booking_id']);
                } else {
                    $editError = 'Không tìm thấy booking service này hoặc đã bị xóa';
                    $action = '';
                }
            } else {
                $editError = 'Lỗi thực thi query: ' . $stmt->error;
                $action = '';
            }
            $stmt->close();
        } else {
            $editError = 'Lỗi chuẩn bị query: ';
            $action = '';
        }
    }
}
if ($editError && empty($message)) {
    $message = $editError;
    $messageType = 'danger';
}

// Lấy danh sách customer 
$customersResult = $mysqli->query(
    "SELECT customer_id, full_name, phone, email 
     FROM customer 
     WHERE deleted IS NULL 
     ORDER BY full_name ASC"
);
$customers = $customersResult->fetch_all(MYSQLI_ASSOC);

// Lấy ra danh sách các dịch vụ 
$serviceResult = $mysqli->query(
    "SELECT service_id, service_name,price
    FROM service
    WHERE deleted IS NULL
    "
);
$services = $serviceResult->fetch_all(MYSQLI_ASSOC);

// Phân trang và tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$type_filter = intval($_GET['type'] ?? 0);
$pageNum = isset($_GET['pageNum']) ? intval($_GET['pageNum']) : 1;
$pageNum = max(1, $pageNum);
$perPage = 5;
$offset = ($pageNum - 1) * $perPage;

// Xây dụng query

$where = "WHERE bs.deleted IS NULL";
$params = [];
$types = '';

if ($search) {
    $where .= " AND (C.full_name LIKE ? OR bs.booking_service_id LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam]);
    $types .= 'ss';
}

if ($status_filter) {
    $where .= " AND bs.status=?";
    $params[] = $status_filter;
    $types .= 's';
}
if ($type_filter) {
    $where .= " AND s.service_id = ?";
    $params[] = $type_filter;
    $types .= 'i';
}
// Đếm tổng số các booking dịch vụ
$counstSql = "SELECT COUNT(*) as total
          FROM booking_service bs
          LEFT JOIN customer c ON bs.customer_id=c.customer_id
          LEFT JOIN service s ON s.service_id=bs.service_id
          LEFT JOIN booking b ON b.booking_id=bs.booking_id
          $where
          ";
$countStmt = $mysqli->prepare($counstSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalResult = $countStmt->get_result();
$totalBookingService = $totalResult->fetch_assoc()['total'];
$countStmt->close();

// Get booking service data
$sql = "SELECT 
       bs.booking_service_id,
       bs.booking_id,
       bs.customer_id,
       bs.service_id,
       bs.quantity,
       bs.unit_price,
       bs.amount,
       bs.usage_date,
       bs.usage_time,
       bs.notes,
       bs.status,
       bs.unit,
       bs.created_at,
       bs.deleted,
       c.full_name,
       c.phone,
       c.email,
       s.service_name,
       s.price,
       s.service_type,
       s.description,
       b.booking_id,
       b.check_in_date,
       b.check_out_date,
       b.status as booking_status,
       r.room_id,
       r.room_number,
       rt.room_type_name,
       i.invoice_id,
       i.status as invoice_status,
       i.total_amount,
       CASE
          WHEN bs.booking_id IS NOT NULL THEN 1
          ELSE 0
        END as has_room_booking
        FROM booking_service bs
        INNER JOIN customer c ON bs.customer_id=c.customer_id
        INNER JOIN service s ON bs.service_id=s.service_id
        LEFT JOIN booking b ON b.booking_id=bs.booking_id
        LEFT JOIN room r ON b.room_id=r.room_id
        LEFT JOIN room_type rt ON r.room_type_id=rt.room_type_id
        LEFT JOIN invoice i ON(
        (i.booking_id =bs.booking_id AND bs.booking_id IS NOT NULL)
        OR
        (i.customer_id =bs.customer_id AND i.booking_id IS NULL)
        ) 
        $where
        ORDER BY bs.created_at DESC
        LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

$stmt = $mysqli->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$booking_service = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Build baase URL for pagination
$baseUrl = "index.php?page=booking-manager&panel=serviceBooking-panel";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urldecode($status_filter);
if ($type_filter) $baseUrl .= "&type=" . $type_filter;
?>

<div class="content-card">
    <div class="card-header-custom">
        <h3 class="card-title">Danh Sách Booking Dịch Vụ</h3>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addServiceBookingModal">
            <i class="fas fa-plus"></i> Thêm Booking Dịch Vụ
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo h($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>


    <!-- Filter -->
    <div class="filter-section">
        <form method="GET" action="index.php">
            <input type="hidden" name="page" value="booking-manager">
            <input type="hidden" name="panel" value="serviceBooking-panel">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Tìm tên khách hoặc mã booking dịch vụ..." />
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status" id="statusFilter">
                        <option value="">Tất cả trạng thái</option>
                        <option value="Confirmed" <?php echo $status_filter == 'Confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                        <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Chưa thanh toán</option>
                        <option value="Cancelled" <?php echo $status_filter == 'Cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="type" id="typeFilter">
                        <option value="0">Tất cả các dịch vụ</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo $service['service_id']; ?>"
                                <?php echo $type_filter == $service['service_id'] ? 'selected' : ''; ?>>
                                <?php echo h($service['service_name']); ?>
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
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Khách</th>
                    <th>Loại Dịch Vụ</th>
                    <th>Tên dịch vụ</th>
                    <th>Ngày</th>
                    <th>Giờ</th>
                    <th>Số Người</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($booking_service)): ?>
                    <tr>
                        <td colspan="9" class="text-center">Không có dữ liệu</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($booking_service as $bk): ?>
                        <tr>
                            <td><?php echo h($bk['booking_service_id']); ?></td>
                            <td><strong><?php echo h($bk['full_name']); ?></strong><br><small><?php echo $bk['phone']; ?></small></td>
                            <td>
                                <?php
                                $statusClass = 'bg-secondary text-white';
                                $statusText = $bk['service_type'];

                                switch ($bk['service_type']) {
                                    case 'Wellness':
                                        $statusClass = 'bg-primary text-white';
                                        $statusText = 'Wellness';
                                        break;
                                    case 'Food & Beverage':
                                        $statusClass = 'bg-success text-white';
                                        $statusText = 'Food & Beverage';
                                        break;
                                    case 'Transportation':
                                        $statusClass = 'bg-info text-white';
                                        $statusText = 'Transportation';
                                        break;
                                    case 'Tour':
                                        $statusClass = 'bg-warning text-dark';
                                        $statusText = 'Tour';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            </td>
                            <td><?php echo $bk['service_name']; ?></td>
                            <td><?php echo h($bk['usage_date']); ?></td>
                            <td><?php echo h($bk['usage_time']); ?></td>
                            <td><?php echo h($bk['quantity']); ?> người</td>
                            <td><strong><?php echo h(number_format($bk['price'] * $bk['amount'], 0, ',', '.')); ?> VNĐ</strong></td>
                            <td>
                                <?php
                                $statusClass = 'bg-secondary';
                                $statusText = $bk['status'];
                                switch ($statusText) {
                                    case 'confirmed':
                                        $statusClass = 'bg-success';
                                        $statusText = 'Đã xác nhận';
                                        break;
                                    case 'pending':
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Chưa hoàn thành';
                                        break;
                                    case 'cancelled':
                                        $statusClass = 'bg-warning';
                                        $statusText = 'Đã hủy';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info"
                                    title="Xem chi tiết"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewBookingServiceModal<?php echo $bk['booking_service_id']; ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" title="Sửa"
                                    onclick="editServiceBooking(<?php echo $bk['booking_service_id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Xóa" onclick="deleteServiceBooking(<?php echo $bk['booking_service_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- View Detail Modal -->
                        <div class="modal fade" id="viewBookingServiceModal<?php echo $bk['booking_service_id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Chi tiết booking dịch vụ #<?php echo $bk['booking_service_id']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6"><strong>Mã Booking : </strong><?php echo h($bk['booking_service_id']); ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4"><strong>Tên khách hàng: </strong><?php echo h($bk['full_name']); ?></div>
                                            <div class="col-md-4"><strong>Số điện thoại: </strong><?php echo $bk['phone']; ?></div>
                                            <div class="col-md-4"><strong>Email: </strong><?php echo $bk['email']; ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6"><strong>Loại dịch vụ : </strong>
                                                <?php
                                                $statusClass = 'bg-secondary text-white';
                                                $statusText = $bk['service_type'];

                                                switch ($bk['service_type']) {
                                                    case 'Wellness':
                                                        $statusClass = 'bg-primary text-white';
                                                        $statusText = 'Wellness';
                                                        break;
                                                    case 'Food & Beverage':
                                                        $statusClass = 'bg-success text-white';
                                                        $statusText = 'Food & Beverage';
                                                        break;
                                                    case 'Transportation':
                                                        $statusClass = 'bg-info text-white';
                                                        $statusText = 'Transportation';
                                                        break;
                                                    case 'Tour':
                                                        $statusClass = 'bg-warning text-dark';
                                                        $statusText = 'Tour';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                            </div>
                                            <div class="col-md-6"><strong>Tên dịch vụ:</strong><?php echo $bk['service_name']; ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6"><strong>Ngày sử dụng: </strong><?php echo $bk['usage_date']; ?></div>
                                            <div class="col-md-6"><strong>Giờ sử dụng: </strong><?php echo $bk['usage_time']; ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12"><strong>Số người sử dụng: </strong><?php echo h($bk['quantity']); ?> người</div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4"><strong>Số lượng: </strong><?php echo h($bk['amount']); ?> <?php echo $bk['unit']; ?> </div>
                                            <div class="col-md-4"><strong>Đơn giá: </strong><?php echo $bk['price']; ?> VNĐ</div>
                                            <div class="col-md-4"><strong>Thành tiền: </strong><?php echo h(number_format($bk['price'] * $bk['amount'], 0, ',', '.')); ?> VNĐ</div>

                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12"><strong>Ghi chú: </strong><?php echo $bk['notes']; ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12"><strong>Trạng thái: </strong>
                                                <?php
                                                $statusClass = 'bg-secondary';
                                                $statusText = $bk['status'];
                                                switch ($statusText) {
                                                    case 'confirmed':
                                                        $statusClass = 'bg-success';
                                                        $statusText = 'Đã hoàn thành';
                                                        break;
                                                    case 'pending':
                                                        $statusClass = 'bg-danger';
                                                        $statusText = 'Chưa hoàn thành';
                                                    case 'cancelled':
                                                        $statusClass = 'bg-warning';
                                                        $statusText = 'Đã hủy';
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                        <a href="index.php?page=booking-manager&panel=serviceBooking-panel&action=edit&id=<?php echo $bk['booking_service_id']; ?>"
                                            class="btn btn-primary">
                                            Chỉnh sửa
                                        </a>
                                    </div>
                                    <div>

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
    <?php echo getPagination($totalBookingService, $perPage, $pageNum, $baseUrl); ?>
</div>
<!-- Modal Thêm Booking Dịch Vụ -->
<div class="modal fade" id="addServiceBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $editBookingService ? 'Sửa' : 'Thêm'; ?> Booking dịch vụ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="bookingServiceForm">
                <?php if ($editBookingService): ?>
                    <input type="hidden" name="booking_service_id" value="<?php echo $editBookingService['booking_service_id']; ?>">
                <?php endif; ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên Khách Hàng *</label>
                            <select class="form-select customer-search" name="customer_id" required id="customerSelect">
                                <option value="">-- Chọn khách hàng --</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['customer_id']; ?>"
                                        <?php echo ($editBookingService && $editBookingService['customer_id'] == $customer['customer_id']) ? 'selected' : ''; ?> data-phone="<?php echo h($customer['phone']); ?>"
                                        data-email="<?php echo h($customer['email']); ?>">
                                        <?php echo h($customer['full_name']); ?> - <?php echo h($customer['phone']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Điện Thoại *</label>
                            <input type="tel"
                                class="form-control"
                                name="phone"
                                id="customerPhone"
                                value="<?php echo $editBookingService ? $editBookingService['phone'] : ''; ?>"
                                required
                                readonly>
                        </div>
                    </div>

                    <?php if ($editBookingService && $editBookingService['has_room_booking']): ?>
                        <div class="alert alert-info">
                            <strong>Liên kết với booking phòng:</strong><br>
                            Phòng: <?php echo h($editBookingService['room_number']); ?><br>
                            Check-in: <?php echo date('d/m/Y', strtotime($editBookingService['check_in_date'])); ?>
                        </div>
                        <input type="hidden" name="booking_id" value="<?php echo $editBookingService['booking_id']; ?>">
                    <?php else: ?>
                        <input type="hidden" name="booking_id" value="">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dịch Vụ *</label>
                            <select class="form-select" required id="serviceSelect" name="service_id">
                                <option value="">-- Chọn dịch vụ --</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['service_id']; ?>"
                                        <?php echo ($editBookingService && $editBookingService['service_id'] == $service['service_id']) ? 'selected' : ''; ?> data-price="<?php echo $service['price']; ?>">
                                        <?php echo $service['service_name']; ?> - <?php echo number_format($service['price'], 0, ',', '.'); ?> VNĐ
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Người *</label>
                            <input type="number" name="quantity" class="form-control" min="1" value="<?php echo  $editBookingService ? h($editBookingService['quantity']) : '1'; ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Sử Dụng *</label>
                            <input type="date" name="usage_date" class="form-control" required value="<?php echo $editBookingService ? $editBookingService['usage_date'] : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giờ Sử Dụng *</label>
                            <input type="time" name="usage_time" class="form-control" required value="<?php echo $editBookingService ? $editBookingService['usage_time'] : ''; ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"> Số lượng *</label>
                            <input type="number" name="amount" min="1" class="form-control" id="amount" required value="<?php echo $editBookingService ? $editBookingService['amount'] : '1'; ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Đơn giá (VNĐ)</label>
                            <input type="number"
                                class="form-control"
                                id="unitPrice"
                                value="<?php echo $editBookingService['price'] ?? 0; ?>"
                                readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tổng Tiền (VNĐ) *</label>
                            <input type="number" class="form-control" step="1000" required id="totalAmount"
                                value="<?php echo h($editBookingService['price'] * $editBookingService['amount']); ?>"
                                readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng Thái *</label>
                            <select class="form-select" name="status" required>
                                <option value="pending" <?php echo ($editBookingService && $editBookingService['status'] == 'pending') ? 'selected' : ''; ?>>Chưa thanh toán</option>
                                <option value="confirmed" <?php echo ($editBookingService && $editBookingService['status'] == 'confirmed') ? 'selected' : ''; ?>>Đã thanh toán</option>
                                <option value="cancelled" <?php echo ($editBookingService && $editBookingService['status'] == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi Chú</label>
                        <textarea class="form-control" name="note" rows="3"><?php echo $editBookingService ? h($editBookingService['notes']) : ''; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-primary-custom"
                        name="<?php echo $editBookingService ? 'update_service_booking' : 'add_booking_service'; ?>">
                        <?php echo $editBookingService ? 'Cập nhật' : 'Thêm'; ?> Booking dịch vụ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Đảm bảo input Select2 có thể gõ được */
    .select2-search__field {
        width: 100% !important;
        border: none !important;
        outline: none !important;
        padding: 5px !important;
        margin: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .select2-search__field:focus {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
    }

    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field:focus {
        border-color: #86b7fe !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }
</style>

<script>
    // Tự động mở modal edit khi có action=edit
    <?php if ($editBookingService): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('addServiceBookingModal'));
            modal.show();
            // Khởi tạo lại Select2 sau khi modal mở hoàn toàn
            const modalEl = document.getElementById('addServiceBookingModal');
            modalEl.addEventListener('shown.bs.modal', function() {
                setTimeout(initCustomerSelect2, 200);
            }, {
                once: true
            });
        });
    <?php endif; ?>

    // Khởi tạo lại Select2 khi modal mở
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('addServiceBookingModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', function() {
                setTimeout(initCustomerSelect2, 200);
            });
        }

        // Khởi tạo lần đầu nếu không trong modal
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function() {
                setTimeout(initCustomerSelect2, 300);
            });
        }
    });

    // Hàm khởi tạo Select2 cho customer
    function initCustomerSelect2() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
            return false;
        }

        const $customerSelect = jQuery('#customerSelect');
        if (!$customerSelect.length) {
            return false;
        }

        // Destroy nếu đã khởi tạo trước đó
        if ($customerSelect.hasClass('select2-hidden-accessible')) {
            $customerSelect.select2('destroy');
        }

        // Lấy modal để set dropdownParent
        const $modal = jQuery('#addServiceBookingModal');
        const dropdownParent = $modal.length ? $modal : jQuery('body');

        $customerSelect.select2({
            theme: 'bootstrap-5',
            placeholder: '-- Chọn khách hàng --',
            allowClear: true,
            minimumInputLength: 0,
            width: '100%',
            dropdownParent: dropdownParent,
            language: {
                noResults: function() {
                    return "Không tìm thấy khách hàng";
                },
                searching: function() {
                    return "Đang tìm kiếm...";
                }
            }
        });

        // Tự động điền số điện thoại khi chọn khách hàng
        $customerSelect.off('change.select2-customer').on('change.select2-customer', function() {
            const selectedOption = jQuery(this).find('option:selected');
            const phone = selectedOption.data('phone') || '';
            jQuery('#customerPhone').val(phone);
        });

        // Đảm bảo input tìm kiếm có thể gõ được
        $customerSelect.on('select2:open', function() {
            setTimeout(function() {
                const $searchField = jQuery('.select2-search__field');
                $searchField.attr('placeholder', 'Gõ để tìm kiếm...');
                $searchField.prop('readonly', false);
                $searchField.prop('disabled', false);
                $searchField.focus();
            }, 100);
        });

        return true;
    }
    // Tự động tính tổng tiền
    document.getElementById('serviceSelect')?.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        document.getElementById('unitPrice').value = price;
        calculateTotal();
    });

    document.getElementById('amount')?.addEventListener('input', calculateTotal);

    function calculateTotal() {
        const unitPrice = parseFloat(document.getElementById('unitPrice')?.value) || 0;
        const amount = parseFloat(document.getElementById('amount')?.value) || 0;
        const total = unitPrice * amount;
        document.getElementById('totalAmount').value = total;
    }

    function editServiceBooking(id) {
        window.location.href = 'index.php?page=booking-manager&panel=serviceBooking-panel&action=edit&id=' + id;
    }

    function deleteServiceBooking(id) {
        if (confirm('Bạn có chắc chắn muốn xóa phòng này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="booking_service_id" value="' + id + '">' +
                '<input type="hidden" name="delete_booking_service" value="1">';
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>