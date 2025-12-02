<?php
// Phân quyền module Hóa Đơn
$canViewInvoice   = function_exists('checkPermission') ? checkPermission('invoice.view')   : true;
$canCreateInvoice = function_exists('checkPermission') ? checkPermission('invoice.create') : true;
$canEditInvoice   = function_exists('checkPermission') ? checkPermission('invoice.edit')   : true;
$canDeleteInvoice = function_exists('checkPermission') ? checkPermission('invoice.delete') : true;

if (!$canViewInvoice) {
    http_response_code(403);
    echo '<div class="main-content"><div class="alert alert-danger m-4">Bạn không có quyền xem trang hóa đơn.</div></div>';
    return;
}

// Xử lý CRUD trong invoice-manager
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Thêm hóa đơn
    if (isset($_POST['add_invoice'])) {
        $booking_id = !empty($_POST['booking_id']) ? intval($_POST['booking_id']) : null;
        $customer_id = intval($_POST['customer_id'] ?? 0);
        $room_charge = floatval($_POST['room_charge']);
        $service_charge = floatval($_POST['service_charge']);
        $vat = floatval($_POST['vat']);
        $other_fees = floatval($_POST['other_fees']);
        $total_amount = $room_charge + $service_charge + $vat + $other_fees;
        $deposit_amount = floatval($_POST['deposit_amount'] ?? 0);
        $remaining_amount = $total_amount - $deposit_amount;
        $payment_method = trim($_POST['payment_method']);
        $status = $_POST['status'] ?? 'Unpaid';
        $payment_time = !empty($_POST['payment_time']) ? $_POST['payment_time'] : null;
        $note = trim($_POST['note'] ?? '');

        // Nếu có booking_id, lấy customer_id từ booking
        if ($booking_id) {
            $stmt_get_customer = $mysqli->prepare("SELECT customer_id FROM booking WHERE booking_id = ?");
            $stmt_get_customer->bind_param("i", $booking_id);
            $stmt_get_customer->execute();
            $result = $stmt_get_customer->get_result();
            $booking_data = $result->fetch_assoc();
            $customer_id = $booking_data['customer_id'] ?? $customer_id;
            $stmt_get_customer->close();
        }

        // Kiểm tra customer_id
        if (!$customer_id || $customer_id <= 0) {
            $message = 'Lỗi: Khách hàng không được để trống!';
            $messageType = 'danger';
        } else {
            // INSERT với booking_id có thể NULL
            if ($booking_id) {
                $stmt = $mysqli->prepare("INSERT INTO invoice (booking_id, customer_id, room_charge, service_charge, vat, other_fees, total_amount, deposit_amount, remaining_amount, payment_method, status, payment_time, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiddddddssss", $booking_id, $customer_id, $room_charge, $service_charge, $vat, $other_fees, $total_amount, $deposit_amount, $remaining_amount, $payment_method, $status, $payment_time, $note);
            } else {
                $stmt = $mysqli->prepare("INSERT INTO invoice (booking_id, customer_id, room_charge, service_charge, vat, other_fees, total_amount, deposit_amount, remaining_amount, payment_method, status, payment_time, note) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iddddddssss", $customer_id, $room_charge, $service_charge, $vat, $other_fees, $total_amount, $deposit_amount, $remaining_amount, $payment_method, $status, $payment_time, $note);
            }

            if ($stmt->execute()) {
                $message = 'Thêm hóa đơn thành công!';
                $messageType = 'success';
                $action = '';
            } else {
                $message = 'Lỗi: ' . $stmt->error;
                $messageType = 'danger';
            }
            $stmt->close();
        }
    }

    // Cập nhật hóa đơn
    if (isset($_POST['update_invoice'])) {
        $invoice_id = intval($_POST['invoice_id']);
        $booking_id = !empty($_POST['booking_id']) ? intval($_POST['booking_id']) : null;
        $customer_id = intval($_POST['customer_id'] ?? 0);
        $room_charge = floatval($_POST['room_charge']);
        $service_charge = floatval($_POST['service_charge']);
        $vat = floatval($_POST['vat']);
        $other_fees = floatval($_POST['other_fees']);
        $total_amount = $room_charge + $service_charge + $vat + $other_fees;
        $deposit_amount = floatval($_POST['deposit_amount'] ?? 0);
        $remaining_amount = $total_amount - $deposit_amount;
        $payment_method = trim($_POST['payment_method']);
        $status = $_POST['status'] ?? 'Unpaid';
        $payment_time = !empty($_POST['payment_time']) ? $_POST['payment_time'] : null;
        $note = trim($_POST['note'] ?? '');

        // Nếu có booking_id, lấy customer_id từ booking
        if ($booking_id) {
            $stmt_get_customer = $mysqli->prepare("SELECT customer_id FROM booking WHERE booking_id = ?");
            $stmt_get_customer->bind_param("i", $booking_id);
            $stmt_get_customer->execute();
            $result = $stmt_get_customer->get_result();
            $booking_data = $result->fetch_assoc();
            $customer_id = $booking_data['customer_id'] ?? $customer_id;
            $stmt_get_customer->close();
        }

        // Kiểm tra customer_id
        if (!$customer_id || $customer_id <= 0) {
            $message = 'Lỗi: Khách hàng không được để trống!';
            $messageType = 'danger';
        } else {
            // UPDATE với booking_id có thể NULL
            $stmt = $mysqli->prepare("UPDATE invoice SET booking_id=?, customer_id=?, room_charge=?, service_charge=?, vat=?, other_fees=?, total_amount=?, deposit_amount=?, remaining_amount=?, payment_method=?, status=?, payment_time=?, note=? WHERE invoice_id=? AND deleted IS NULL");
            $stmt->bind_param("iiddddddssssi", $booking_id, $customer_id, $room_charge, $service_charge, $vat, $other_fees, $total_amount, $deposit_amount, $remaining_amount, $payment_method, $status, $payment_time, $note, $invoice_id);

            if ($stmt->execute()) {
                $message = 'Cập nhật hóa đơn thành công!';
                $messageType = 'success';
                $action = '';
                header("Location: index.php?page=invoices-manager&success=1");
                exit;
            } else {
                $message = 'Lỗi: ' . $stmt->error;
                $messageType = 'danger';
            }
            $stmt->close();
        }
    }

    // Xóa hóa đơn
    if (isset($_POST['delete_invoice'])) {
        $invoice_id = intval($_POST['invoice_id']);
        $stmt = $mysqli->prepare("UPDATE invoice SET deleted = NOW() WHERE invoice_id = ?");
        $stmt->bind_param("i", $invoice_id);

        if ($stmt->execute()) {
            $message = 'Xóa hóa đơn thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }
}

// Lấy thông tin hóa đơn để edit
$editInvoice = null;
$editBookingInfo = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM invoice WHERE invoice_id = ? AND deleted IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editInvoice = $result->fetch_assoc();
    $stmt->close();

    // Nếu có booking_id, lấy thông tin booking để hiển thị
    if ($editInvoice && $editInvoice['booking_id']) {
        $booking_stmt = $mysqli->prepare("
            SELECT b.booking_id, b.check_in_date, b.check_out_date,
                   c.full_name, c.phone,
                   r.room_number, rt.room_type_name
            FROM booking b
            LEFT JOIN customer c ON b.customer_id = c.customer_id
            LEFT JOIN room r ON b.room_id = r.room_id
            LEFT JOIN room_type rt ON r.room_type_id = rt.room_type_id
            WHERE b.booking_id = ? AND b.deleted IS NULL
        ");
        $booking_stmt->bind_param("i", $editInvoice['booking_id']);
        $booking_stmt->execute();
        $booking_result = $booking_stmt->get_result();
        $editBookingInfo = $booking_result->fetch_assoc();
        $booking_stmt->close();
    }
}

// Lấy danh sách booking chưa có hóa đơn (chỉ booking phòng)
$bookingsWithoutInvoice = [];
$bookingsQuery = $mysqli->query("
    SELECT b.booking_id, b.check_in_date, b.check_out_date, 
           c.full_name, c.phone, c.email,
           r.room_number, rt.room_type_name
    FROM booking b
    INNER JOIN customer c ON b.customer_id = c.customer_id
    INNER JOIN room r ON b.room_id = r.room_id
    INNER JOIN room_type rt ON r.room_type_id = rt.room_type_id
    LEFT JOIN invoice i ON b.booking_id = i.booking_id AND i.deleted IS NULL
    WHERE b.deleted IS NULL 
    AND i.invoice_id IS NULL
    AND b.status NOT IN ('Cancelled')
    ORDER BY b.check_in_date DESC
");
if ($bookingsQuery) {
    $bookingsWithoutInvoice = $bookingsQuery->fetch_all(MYSQLI_ASSOC);
}

// Phân trang và tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$pageNum = isset($_GET['pageNum']) ? intval($_GET['pageNum']) : 1;
$pageNum = max(1, $pageNum);
$perPage = 5;
$offset = ($pageNum - 1) * $perPage;

// Xây dựng WHERE clause
$where = "WHERE i.deleted IS NULL";
$params = [];
$types = '';
if ($search) {
    $where .= " AND (b.booking_id LIKE ? OR c.full_name LIKE ? OR i.invoice_id LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    $types .= 'isi';
}

if ($status_filter) {
    $where .= " AND i.status=?";
    $params[] = $status_filter;
    $types .= 's';
}

// Xây dựng ORDER BY
$orderBy = "ORDER BY i.created_at DESC";
switch ($sort) {
    case 'oldest':
        $orderBy = "ORDER BY i.created_at ASC";
        break;
    case 'amount_high':
        $orderBy = "ORDER BY i.total_amount DESC";
        break;
    case 'amount_low':
        $orderBy = "ORDER BY i.total_amount ASC";
        break;
    case 'newest':
    default:
        $orderBy = "ORDER BY i.created_at DESC";
        break;
}

// Đếm tổng số
$countQuery = "SELECT COUNT(DISTINCT i.invoice_id) as total 
    FROM invoice i 
    LEFT JOIN booking b ON i.booking_id = b.booking_id
    LEFT JOIN customer c ON i.customer_id = c.customer_id
    $where";
$countResult = $mysqli->query($countQuery);
$total = 0;

if ($countResult) {
    $totalRow = $countResult->fetch_assoc();
    $total = $totalRow['total'] ?? 0;
} else {
    error_log("Count Error: " . $mysqli->error);
    $message = "Lỗi đếm dữ liệu: " . $mysqli->error;
    $messageType = 'danger';
}

// Lấy dữ liệu
$invoices = [];
if ($total > 0) {
    // Lấy customer từ invoice.customer_id (vì invoice đã có customer_id)
    // Và lấy thông tin booking_service qua invoice_service
    $query = "SELECT i.*, 
                i.booking_id,
                b.booking_id as booking_exists,
                c.full_name,
                c.customer_id,
                GROUP_CONCAT(DISTINCT bs.booking_service_id) as booking_service_ids,
                COUNT(DISTINCT isv.booking_service_id) as service_count
        FROM invoice i 
        LEFT JOIN booking b ON i.booking_id = b.booking_id
        LEFT JOIN customer c ON i.customer_id = c.customer_id
        LEFT JOIN invoice_service isv ON i.invoice_id = isv.invoice_id
        LEFT JOIN booking_service bs ON isv.booking_service_id = bs.booking_service_id AND bs.deleted IS NULL
        $where 
        GROUP BY i.invoice_id
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
    $invoices = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Build base URL for pagination
$baseUrl = "index.php?page=invoices-manager";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urlencode($status_filter);
if ($sort) $baseUrl .= "&sort=" . urlencode($sort);
?>

<div class="main-content">
    <div class="content-header">
        <h1>Quản lý hóa đơn</h1>
        <div class="row m-3">
            <div class="col-md-12">
                <button class="btn-add " data-bs-toggle="modal" data-bs-target="#addInvoiceModal"
                    onclick="resetInvoiceForm()">
                    <i class="fas fa-plus"></i> Thêm Hóa Đơn
                </button>
            </div>
        </div>
        <!-- Tabs loại hóa đơn -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo h($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="tab-content">
        <!-- Bảng: Hóa đơn phòng -->
        <div class="filter-section ">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="invoices-manager">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" name="search" placeholder="Tìm kiếm hóa đơn..."
                                value="<?php echo h($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter" name="status">
                            <option value="">Tất cả tình trạng</option>
                            <option value="Paid" <?php echo $status_filter == 'Paid' ? 'selected' : ''; ?>>Đã thanh toán
                            </option>
                            <option value="Unpaid" <?php echo $status_filter == 'Unpaid' ? 'selected' : ''; ?>>Chưa
                                thanh toán</option>
                            <!-- Removed Partial status as it's not in the new schema -->
                            toán một phần</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select">
                            <option value="all-invoices">Tất cả hóa đơn</option>
                            <option value="room-invoices">Hóa đơn phòng</option>
                            <option value="service-invoice">Hóa đơn dịch vụ</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table class="table table-hover" id="invoiceTable">
                <thead>
                    <tr>
                        <th>Mã HĐ</th>
                        <th>Booking ID</th>
                        <th>Khách Hàng</th>
                        <th>Phí Phòng</th>
                        <th>Phí Dịch Vụ</th>
                        <th>Tổng Tiền</th>
                        <th>Hình Thức TT</th>
                        <th>Tình Trạng</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="9" class="text-center">Không có dữ liệu</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr data-invoice-id="<?php echo $invoice['invoice_id']; ?>">
                                <td><?php echo $invoice['invoice_id']; ?></td>
                                <td>
                                    <?php
                                    if ($invoice['booking_id']) {
                                        echo $invoice['booking_id'];
                                    } else {
                                        echo '<span class="text-muted">Service Only</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo h($invoice['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo number_format($invoice['room_charge'], 0, ',', '.'); ?> VNĐ</td>
                                <td><?php echo number_format($invoice['service_charge'], 0, ',', '.'); ?> VNĐ</td>
                                <td><strong><?php echo number_format($invoice['total_amount'], 0, ',', '.'); ?> VNĐ</strong>
                                </td>
                                <td><?php echo h($invoice['payment_method']); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = 'badge';
                                    if ($invoice['status'] == 'Paid') $badgeClass = 'badge bg-success';
                                    elseif ($invoice['status'] == 'Unpaid') $badgeClass = 'badge bg-danger';
                                    // Removed Partial status handling
                                    ?>
                                    <span class="<?php echo $badgeClass; ?>">
                                        <?php
                                        $statusText = [
                                            'Paid' => 'Đã thanh toán',
                                            'Unpaid' => 'Chưa thanh toán',
                                            'Partial' => 'Thanh toán một phần'
                                        ];
                                        echo $statusText[$invoice['status']] ?? $invoice['status'];
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info"
                                        onclick="viewInvoice(<?php echo $invoice['invoice_id']; ?>)" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <a href="index.php?page=invoices-manager&action=edit&id=<?php echo $invoice['invoice_id']; ?>">
                                        <button class="btn btn-sm btn-outline-warning"
                                            title="Sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteInvoice(<?php echo $invoice['invoice_id']; ?>)" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- pagination -->
    <?php echo getPagination($total, $perPage, $pageNum, $baseUrl); ?>

    <!-- View Invoice Modal - Một modal duy nhất cho tất cả invoices -->
    <div class="modal fade" id="viewInvoiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-invoice"></i> Chi Tiết Hóa Đơn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Mã Hóa Đơn:</strong> <span id="viewInvoiceId">-</span></div>
                        <div class="col-md-6"><strong>Ngày Tạo:</strong> <span id="viewCreatedAt">-</span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Khách Hàng:</strong> <span id="viewCustomerName">-</span></div>
                        <div class="col-md-6"><strong>Điện Thoại:</strong> <span id="viewCustomerPhone">-</span></div>
                    </div>

                    <!-- Thông tin phòng (nếu có) -->
                    <div id="viewRoomInfo" style="display: none;" class="mb-3">
                        <div class="row">
                            <div class="col-md-6"><strong>Phòng:</strong> <span id="viewRoomNumber">-</span></div>
                            <div class="col-md-6"><strong>Loại Phòng:</strong> <span id="viewRoomType">-</span></div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6"><strong>Check-in:</strong> <span id="viewCheckIn">-</span></div>
                            <div class="col-md-6"><strong>Check-out:</strong> <span id="viewCheckOut">-</span></div>
                        </div>
                    </div>

                    <!-- Danh sách dịch vụ -->
                    <div id="viewServicesSection" class="mb-3" style="display: none;">
                        <strong>Dịch Vụ:</strong>
                        <table class="table table-sm table-bordered mt-2">
                            <thead>
                                <tr>
                                    <th>Tên Dịch Vụ</th>
                                    <th class="text-center">Số Lượng</th>
                                    <th class="text-end">Đơn Giá</th>
                                    <th class="text-end">Thành Tiền</th>
                                </tr>
                            </thead>
                            <tbody id="viewServicesList">
                            </tbody>
                        </table>
                    </div>

                    <!-- Bảng tính tiền -->
                    <table class="table table-sm mt-3">
                        <tr>
                            <td>Phí Phòng:</td>
                            <td class="text-end" id="viewRoomCharge">0 VNĐ</td>
                        </tr>
                        <tr>
                            <td>Phí Dịch Vụ:</td>
                            <td class="text-end" id="viewServiceCharge">0 VNĐ</td>
                        </tr>
                        <tr>
                            <td>VAT:</td>
                            <td class="text-end" id="viewVat">0 VNĐ</td>
                        </tr>
                        <tr style="border-top: 2px solid #ddd; font-weight: bold;">
                            <td>Tổng Cộng:</td>
                            <td class="text-end" id="viewTotalAmount">0 VNĐ</td>
                        </tr>
                        <tr>
                            <td>Tiền Cọc:</td>
                            <td class="text-end" id="viewDepositAmount">0 VNĐ</td>
                        </tr>
                        <tr style="font-weight: bold;">
                            <td>Còn Lại:</td>
                            <td class="text-end" id="viewRemainingAmount">0 VNĐ</td>
                        </tr>
                    </table>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Hình Thức TT:</strong> <span id="viewPaymentMethod">-</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Tình Trạng:</strong> <span id="viewStatus">-</span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <strong>Ngày Thanh Toán:</strong> <span id="viewPaymentTime">-</span>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <strong>Ghi Chú:</strong> <span id="viewNote">-</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-success" onclick="exportInvoiceToWord()">
                        <i class="fas fa-file-word"></i> Xuất Word
                    </button>
                    <button type="button" class="btn btn-primary" onclick="editInvoiceFromView()">
                        <i class="fas fa-edit"></i> Chỉnh Sửa
                    </button>
                </div>
            </div>
        </div>
    </div>



    <!-- Bảng: Hóa đơn dịch vụ -->
    <!-- <div class="tab-pane fade" id="panel-service" role="tabpanel">
        <div class="filter-section">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchServiceInvoice" placeholder="Tìm hóa đơn dịch vụ..." />
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusServiceInvoice">
                        <option value="">Tất cả tình trạng</option>
                        <option value="completed">Hoàn tất</option>
                        <option value="deposit">Đã cọc</option>
                        <option value="unpaid">Chưa thanh toán</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="serviceTypeFilter">
                        <option value="">Tất cả loại dịch vụ</option>
                        <option value="spa">Spa</option>
                        <option value="restaurant">Nhà hàng</option>
                        <option value="event">Sự kiện</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="table table-hover" id="tableServiceInvoice">
                <thead>
                    <tr>
                        <th>Mã HĐ</th>
                        <th>Khách Hàng</th>
                        <th>Loại Dịch Vụ</th>
                        <th>Dịch Vụ</th>
                        <th>Tổng Tiền</th>
                        <th>Ngày Sử Dụng</th>
                        <th>Tình Trạng</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>HDS001</strong></td>
                        <td>Phạm Thị D</td>
                        <td>Spa</td>
                        <td>Spa 90 phút</td>
                        <td>800,000 VNĐ</td>
                        <td>20/10/2025</td>
                        <td><span class="badge bg-success">Hoàn tất</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editServiceInvoice('HDS001')"
                                title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice('HDS001')"
                                title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                data-bs-target="#viewServiceInvoiceModal" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>HDS002</strong></td>
                        <td>Hoàng Văn E</td>
                        <td>Sự kiện</td>
                        <td>Tiệc cưới</td>
                        <td>50,000,000 VNĐ</td>
                        <td>25/11/2025</td>
                        <td><span class="badge bg-warning">Đã cọc</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editServiceInvoice('HDS002')"
                                title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice('HDS002')"
                                title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>HDS003</strong></td>
                        <td>Vũ Thị F</td>
                        <td>Nhà hàng</td>
                        <td>Bữa tối VIP</td>
                        <td>2,500,000 VNĐ</td>
                        <td>28/10/2025</td>
                        <td><span class="badge bg-danger">Chưa thanh toán</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editServiceInvoice('HDS003')"
                                title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice('HDS003')"
                                title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div> -->
</div>
</div>

<!-- Modal Thêm/Sửa Hóa Đơn Phòng -->
<!-- <div class="modal fade" id="invoiceRoomModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Thêm Hóa Đơn Phòng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="roomInvoiceForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tình Trạng *</label>
                            <select class="form-select" required>
                                <option value="completed">Hoàn tất</option>
                                <option value="deposit" selected>Đã cọc</option>
                                <option value="unpaid">Chưa thanh toán</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tổng Cộng *</label>
                            <input type="text" class="form-control" placeholder="1,500,000 VNĐ" readonly />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi Chú</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú về hóa đơn..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Hủy
                </button>
                <button type="button" class="btn btn-primary" onclick="saveRoomInvoice()">
                    Lưu Hóa Đơn
                </button>
            </div>
        </div>
    </div>
</div> -->

<!-- Modal Thêm/Sửa Hóa Đơn Dịch Vụ -->
<!-- <div class="modal fade" id="invoiceServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle"></i> Thêm Hóa Đơn Dịch Vụ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="serviceInvoiceForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Mã Hóa Đơn *</label>
                            <input type="text" class="form-control" placeholder="VD: HDS001" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Loại Dịch Vụ *</label>
                            <select class="form-select" id="serviceType" required>
                                <option selected>Chọn loại dịch vụ</option>
                                <option value="spa">Spa</option>
                                <option value="restaurant">Nhà hàng</option>
                                <option value="event">Sự kiện</option>
                                <option value="entertainment">Giải trí</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Khách Hàng *</label>
                            <input type="text" class="form-control" placeholder="Nhập tên khách hàng" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số Điện Thoại</label>
                            <input type="text" class="form-control" placeholder="Nhập số điện thoại" />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Dịch Vụ *</label>
                            <select class="form-select" id="serviceSelect" required>
                                <option selected>Chọn dịch vụ</option>
                                <option value="spa60">Spa 60 phút - 600,000 VNĐ</option>
                                <option value="spa90">Spa 90 phút - 800,000 VNĐ</option>
                                <option value="wedding">Tiệc cưới - 50,000,000 VNĐ</option>
                                <option value="conference">
                                    Hội nghị - 10,000,000 VNĐ
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Số Lượng *</label>
                            <input type="number" class="form-control" placeholder="1" min="1" value="1" required />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Đơn Giá</label>
                            <input type="text" class="form-control" placeholder="800,000 VNĐ" readonly />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Ngày Sử Dụng *</label>
                            <input type="date" class="form-control" required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thời Gian</label>
                            <input type="time" class="form-control" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chi Tiết Bổ Sung</label>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Mô tả</th>
                                        <th width="150">Số lượng</th>
                                        <th width="150">Đơn giá</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="additionalServices">
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control form-control-sm"
                                                placeholder="VD: Trang trí hoa" />
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" value="1"
                                                min="1" />
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm" placeholder="0" />
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="removeRow(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addServiceRow()">
                                <i class="fas fa-plus"></i> Thêm dòng
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tình Trạng *</label>
                            <select class="form-select" required>
                                <option value="completed">Hoàn tất</option>
                                <option value="deposit" selected>Đã cọc</option>
                                <option value="unpaid">Chưa thanh toán</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tổng Cộng *</label>
                            <input type="text" class="form-control" placeholder="800,000 VNĐ" readonly />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi Chú</label>
                        <textarea class="form-control" rows="3" placeholder="Nhập ghi chú về dịch vụ..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Hủy
                </button>
                <button type="button" class="btn btn-primary" onclick="saveServiceInvoice()">
                    Lưu Hóa Đơn
                </button>
            </div>
        </div>
    </div>
</div> -->

<!-- Modal: Thêm Hóa Đơn -->
<div class="modal fade" id="addInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-invoice"></i> <span id="modalTitle">Thêm Hóa Đơn</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="invoiceForm">
                <div class="modal-body">
                    <input type="hidden" name="invoice_id" id="invoice_id" value="">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Booking ID <?php echo $editInvoice ? '(Không thể thay đổi)' : '*'; ?></label>
                            <?php if ($editInvoice): ?>
                                <!-- Khi edit: hiển thị readonly -->
                                <?php if ($editInvoice['booking_id']): ?>
                                    <!-- Có booking_id: hiển thị thông tin booking -->
                                    <select class="form-select" id="booking_id" name="booking_id" readonly disabled style="background-color: #e9ecef; cursor: not-allowed;">
                                        <option value="<?php echo $editInvoice['booking_id']; ?>" selected>
                                            <?php if ($editBookingInfo): ?>
                                                Booking #<?php echo $editInvoice['booking_id']; ?> -
                                                <?php echo h($editBookingInfo['full_name']); ?> -
                                                Phòng <?php echo h($editBookingInfo['room_number']); ?> -
                                                <?php echo date('d/m/Y', strtotime($editBookingInfo['check_in_date'])); ?>
                                            <?php else: ?>
                                                Booking #<?php echo $editInvoice['booking_id']; ?>
                                            <?php endif; ?>
                                        </option>
                                    </select>
                                    <!-- Hidden input để gửi booking_id khi submit -->
                                    <input type="hidden" name="booking_id" value="<?php echo $editInvoice['booking_id']; ?>">
                                <?php else: ?>
                                    <!-- Không có booking_id: hóa đơn chỉ dịch vụ -->
                                    <input type="text" class="form-control" value="Không có (Hóa đơn chỉ dịch vụ)" readonly disabled style="background-color: #e9ecef; cursor: not-allowed;">
                                    <input type="hidden" name="booking_id" value="">
                                <?php endif; ?>
                                <small class="text-muted">Booking ID không thể thay đổi khi chỉnh sửa hóa đơn</small>
                            <?php else: ?>
                                <!-- Khi thêm mới: select bình thường -->
                                <select class="form-select booking-search" id="booking_id" name="booking_id" required>
                                    <option value="">-- Chọn booking chưa có hóa đơn --</option>
                                    <?php foreach ($bookingsWithoutInvoice as $booking): ?>
                                        <option value="<?php echo $booking['booking_id']; ?>"
                                            data-customer-name="<?php echo h($booking['full_name']); ?>"
                                            data-checkin="<?php echo $booking['check_in_date']; ?>"
                                            data-checkout="<?php echo $booking['check_out_date']; ?>"
                                            data-room="<?php echo h($booking['room_number']); ?>">
                                            Booking #<?php echo $booking['booking_id']; ?> -
                                            <?php echo h($booking['full_name']); ?> -
                                            Phòng <?php echo h($booking['room_number']); ?> -
                                            <?php echo date('d/m/Y', strtotime($booking['check_in_date'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Hoặc nhập Booking ID thủ công nếu không có trong danh sách</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hình Thức Thanh Toán *</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Chọn hình thức</option>
                                <option value="Cash">Tiền mặt</option>
                                <option value="Bank Transfer">Chuyển khoản</option>
                                <option value="Credit Card">Thẻ tín dụng</option>
                                <option value="E-Wallet">Ví điện tử</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phí Phòng (VNĐ) *</label>
                            <input type="number" class="form-control" id="room_charge" name="room_charge" step="0.01"
                                min="0" value="0" required onchange="calculateTotal()">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phí Dịch Vụ (VNĐ) *</label>
                            <input type="number" class="form-control" id="service_charge" name="service_charge"
                                step="0.01" min="0" value="0" required onchange="calculateTotal()">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">VAT (VNĐ) *</label>
                            <input type="number" class="form-control" id="vat" name="vat" step="0.01" min="0" value="0"
                                required onchange="calculateTotal()">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phí Khác (VNĐ)</label>
                            <input type="number" class="form-control" id="other_fees" name="other_fees" step="0.01"
                                min="0" value="0" onchange="calculateTotal()">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tổng Tiền (VNĐ) *</label>
                        <input type="number" class="form-control" id="total_amount" name="total_amount" step="0.01"
                            min="0" readonly required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tiền Cọc (VNĐ)</label>
                            <input type="number" class="form-control" id="deposit_amount" name="deposit_amount" step="0.01"
                                min="0" value="0" onchange="calculateRemaining()">
                            <small class="text-muted">Số tiền đã cọc (30% = tổng tiền × 0.3)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Còn Lại (VNĐ)</label>
                            <input type="number" class="form-control" id="remaining_amount" name="remaining_amount" step="0.01"
                                min="0" value="0" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tình Trạng *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Unpaid">Chưa thanh toán</option>
                                <option value="Paid">Đã thanh toán</option>
                                <option value="Refunded">Đã hoàn tiền</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Thanh Toán</label>
                            <input type="datetime-local" class="form-control" id="payment_time" name="payment_time">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ghi Chú</label>
                        <textarea class="form-control" id="note" name="note" rows="3"
                            placeholder="Nhập ghi chú..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Thêm Hóa Đơn</button>
                </div>
            </form>
        </div>
    </div>
</div>



</div>

<!-- Modal Xem Chi Tiết Hóa Đơn Phòng -->
<!-- <div class="modal fade" id="viewRoomInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice"></i> Chi Tiết Hóa Đơn Phòng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Mã Hóa Đơn:</strong> HDR001</div>
                    <div class="col-md-6">
                        <strong>Phòng:</strong> Phòng 101 - Standard
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Khách Hàng:</strong> Nguyễn Văn A
                    </div>
                    <div class="col-md-6"><strong>SĐT:</strong> 0912345678</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Check-in:</strong> 15/10/2025</div>
                    <div class="col-md-6"><strong>Check-out:</strong> 18/10/2025</div>
                </div>
                <div class="mb-3">
                    <strong>Chi Tiết:</strong>
                    <table class="table table-sm mt-2">
                        <thead>
                            <tr>
                                <th>Mô tả</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Phòng Standard - 3 đêm</td>
                                <td>1</td>
                                <td>500,000 VNĐ</td>
                                <td>1,500,000 VNĐ</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end">
                                    <strong>Tổng Cộng:</strong>
                                </td>
                                <td><strong>1,500,000 VNĐ</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tình Trạng:</strong>
                        <span class="badge bg-success">Hoàn tất</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Ngày Thanh Toán:</strong> 18/10/2025
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Ghi Chú:</strong> Khách hàng đã thanh toán đầy đủ
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Đóng
                </button>
                <button type="button" class="btn btn-primary">
                    <i class="fas fa-print"></i> In Hóa Đơn
                </button>
            </div>
        </div>
    </div>
</div> -->

<!-- Modal Xem Chi Tiết Hóa Đơn Dịch Vụ -->
<!-- <div class="modal fade" id="viewServiceInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice"></i> Chi Tiết Hóa Đơn Dịch Vụ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Mã Hóa Đơn:</strong> HDS001</div>
                    <div class="col-md-6"><strong>Loại Dịch Vụ:</strong> Spa</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Khách Hàng:</strong> Phạm Thị D
                    </div>
                    <div class="col-md-6"><strong>SĐT:</strong> 0923456789</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Ngày Sử Dụng:</strong> 20/10/2025
                    </div>
                    <div class="col-md-6"><strong>Thời Gian:</strong> 14:00</div>
                </div>
                <div class="mb-3">
                    <strong>Chi Tiết:</strong>
                    <table class="table table-sm mt-2">
                        <thead>
                            <tr>
                                <th>Mô tả</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Spa 90 phút</td>
                                <td>1</td>
                                <td>800,000 VNĐ</td>
                                <td>800,000 VNĐ</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end">
                                    <strong>Tổng Cộng:</strong>
                                </td>
                                <td><strong>800,000 VNĐ</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tình Trạng:</strong>
                        <span class="badge bg-success">Hoàn tất</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Ngày Thanh Toán:</strong> 20/10/2025
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Ghi Chú:</strong> Khách hàng rất hài lòng với dịch vụ
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Đóng
                </button>
                <button type="button" class="btn btn-primary">
                    <i class="fas fa-print"></i> In Hóa Đơn
                </button>
            </div>
        </div>
    </div>
</div> -->




<script>
    // JavaScript functions for invoice management
    function editInvoice(invoiceId) {
        // Logic to edit invoice
        window.location.href = 'index.php?page=invoices-manager&action=edit&id=' + invoiceId;
    }

    function deleteInvoice(invoiceId) {
        if (confirm("Bạn có chắc chắn muốn xóa hóa đơn này?")) {
            // Logic to delete invoice
            console.log("Delete invoice:", invoiceId);
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="invoice_id" value="' + invoiceId + '">' +
                '<input type="hidden" name="delete_invoice" value="1">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function viewInvoice(invoiceId) {
        // Lấy dữ liệu từ database qua AJAX
        fetch(`api/invoice-api.php?action=get_invoice&id=${invoiceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.invoice) {
                    const inv = data.invoice;

                    // Format số tiền
                    const formatMoney = (amount) => {
                        return new Intl.NumberFormat('vi-VN').format(amount || 0) + ' VNĐ';
                    };

                    // Format ngày
                    const formatDate = (dateStr) => {
                        if (!dateStr || dateStr === '0000-00-00 00:00:00') return '-';
                        const date = new Date(dateStr);
                        return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    };

                    // Format trạng thái
                    const formatStatus = (status) => {
                        const statusMap = {
                            'Paid': 'Đã thanh toán',
                            'Unpaid': 'Chưa thanh toán',
                            'Refunded': 'Đã hoàn tiền'
                        };
                        return statusMap[status] || status;
                    };

                    // Set text fields trong modal
                    const setElement = (id, value) => {
                        const el = document.getElementById(id);
                        if (el) el.textContent = value;
                    };

                    setElement('viewInvoiceId', '#' + (inv.invoice_id || '-'));
                    setElement('viewCustomerName', inv.full_name || '-');
                    setElement('viewCustomerPhone', inv.phone || '-');
                    setElement('viewCreatedAt', formatDate(inv.created_at));

                    // Hiển thị thông tin phòng (nếu có)
                    const roomInfoEl = document.getElementById('viewRoomInfo');
                    if (inv.room_number && inv.room_type_name) {
                        setElement('viewRoomNumber', inv.room_number || '-');
                        setElement('viewRoomType', inv.room_type_name || '-');
                        setElement('viewCheckIn', inv.check_in_date ? formatDate(inv.check_in_date) : '-');
                        setElement('viewCheckOut', inv.check_out_date ? formatDate(inv.check_out_date) : '-');
                        if (roomInfoEl) roomInfoEl.style.display = 'block';
                    } else {
                        if (roomInfoEl) roomInfoEl.style.display = 'none';
                    }

                    // Hiển thị danh sách dịch vụ
                    const servicesSection = document.getElementById('viewServicesSection');
                    const servicesList = document.getElementById('viewServicesList');
                    if (inv.services && inv.services.length > 0) {
                        if (servicesList) {
                            servicesList.innerHTML = '';
                            inv.services.forEach(service => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${service.service_name || '-'}</td>
                                    <td class="text-center">${service.quantity || 0} ${service.unit || ''}</td>
                                    <td class="text-end">${formatMoney(service.unit_price || 0)}</td>
                                    <td class="text-end">${formatMoney(service.total_price || 0)}</td>
                                `;
                                servicesList.appendChild(row);
                            });
                        }
                        if (servicesSection) servicesSection.style.display = 'block';
                    } else {
                        if (servicesSection) servicesSection.style.display = 'none';
                    }

                    // Thông tin thanh toán
                    setElement('viewRoomCharge', formatMoney(inv.room_charge || 0));
                    setElement('viewServiceCharge', formatMoney(inv.service_charge || 0));
                    setElement('viewVat', formatMoney(inv.vat || 0));
                    setElement('viewTotalAmount', formatMoney(inv.total_amount || 0));
                    setElement('viewDepositAmount', formatMoney(inv.deposit_amount || 0));
                    setElement('viewRemainingAmount', formatMoney(inv.remaining_amount || 0));
                    setElement('viewPaymentMethod', inv.payment_method || '-');
                    setElement('viewStatus', formatStatus(inv.status));
                    setElement('viewPaymentTime', inv.payment_time ? formatDate(inv.payment_time) : '-');

                    const noteEl = document.getElementById('viewNote');
                    if (noteEl) noteEl.textContent = inv.note || '-';

                    // Lưu invoice data để xuất Word và edit
                    window.currentInvoiceData = inv;
                    window.currentInvoiceId = inv.invoice_id; // Lưu invoice_id riêng để dùng cho edit

                    // Show modal
                    const viewModal = new bootstrap.Modal(
                        document.getElementById("viewInvoiceModal")
                    );
                    viewModal.show();
                } else {
                    alert('Không tìm thấy thông tin hóa đơn');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi khi tải thông tin hóa đơn');
            });
    }

    function saveInvoice() {
        // Logic to save invoice
        console.log("Save invoice");
        const addModal = bootstrap.Modal.getInstance(
            document.getElementById("addInvoiceModal")
        );
        addModal.hide();
    }

    function editInvoiceFromView() {
        // Lấy invoice_id từ biến global đã lưu khi view invoice
        let invoiceId = window.currentInvoiceId;
        if (!invoiceId) {
            // Fallback: lấy từ textContent và loại bỏ dấu #
            const invoiceIdText = document.getElementById("viewInvoiceId")?.textContent || '';
            invoiceId = invoiceIdText.replace('#', '').trim();
        }

        if (!invoiceId || invoiceId === '-' || invoiceId === '') {
            alert('Không tìm thấy mã hóa đơn');
            return;
        }

        const viewModal = bootstrap.Modal.getInstance(
            document.getElementById("viewInvoiceModal")
        );
        if (viewModal) {
            viewModal.hide();
        }

        // Chuyển đến trang edit với URL đúng
        window.location.href = 'index.php?page=invoices-manager&action=edit&id=' + invoiceId;
    }

    function exportInvoiceToWord() {
        const invoiceId = document.getElementById("viewInvoiceId").textContent;
        if (!invoiceId || invoiceId === '-') {
            alert('Không tìm thấy mã hóa đơn');
            return;
        }

        // Mở link xuất Word trong tab mới
        window.open(`api/export-invoice.php?id=${invoiceId}`, '_blank');
    }

    function resetInvoiceForm() {
        const form = document.getElementById("invoiceForm");
        if (form) {
            form.reset();
            document.getElementById("invoice_id").value = '';
            document.getElementById("modalTitle").textContent = 'Thêm Hóa Đơn';
            document.getElementById("submitBtn").textContent = 'Thêm Hóa Đơn';
            document.getElementById("submitBtn").name = 'add_invoice';
            // Reset Select2
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                jQuery('#booking_id').val(null).trigger('change');
            }
        }
    }

    // Hàm khởi tạo Select2 cho booking
    function initBookingSelect2() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
            return false;
        }

        const $bookingSelect = jQuery('#booking_id');
        if (!$bookingSelect.length) {
            return false;
        }

        // Destroy nếu đã khởi tạo trước đó
        if ($bookingSelect.hasClass('select2-hidden-accessible')) {
            $bookingSelect.select2('destroy');
        }

        // Lấy modal để set dropdownParent
        const $modal = jQuery('#addInvoiceModal');
        const dropdownParent = $modal.length ? $modal : jQuery('body');

        $bookingSelect.select2({
            theme: 'bootstrap-5',
            placeholder: '-- Chọn booking chưa có hóa đơn --',
            allowClear: true,
            minimumInputLength: 0,
            width: '100%',
            dropdownParent: dropdownParent,
            language: {
                noResults: function() {
                    return "Không tìm thấy booking";
                },
                searching: function() {
                    return "Đang tìm kiếm...";
                }
            }
        });

        // Đảm bảo input tìm kiếm có thể gõ được
        $bookingSelect.on('select2:open', function() {
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

    // Khởi tạo lại Select2 khi modal mở
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('addInvoiceModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', function() {
                setTimeout(initBookingSelect2, 200);
            });
        }

        // Khởi tạo lần đầu nếu không trong modal
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function() {
                setTimeout(initBookingSelect2, 300);
            });
        }
    });

    // Search and filter functionality
    if (document.getElementById("searchInput")) {
        document.getElementById("searchInput").addEventListener("input", function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById("invoiceTable");
            if (!table) return;

            const rows = table.getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? "" : "none";
            }
        });
    }

    if (document.getElementById("statusFilter")) {
        document.getElementById("statusFilter").addEventListener("change", filterInvoiceTable);
    }

    if (document.getElementById("paymentMethodFilter")) {
        document.getElementById("paymentMethodFilter").addEventListener("change", filterInvoiceTable);
    }

    function filterInvoiceTable() {
        const statusFilter = document.getElementById("statusFilter")?.value || "";
        const paymentMethodFilter = document.getElementById("paymentMethodFilter")?.value || "";
        const table = document.getElementById("invoiceTable");

        if (!table) return;

        const rows = table.getElementsByTagName("tr");

        for (let i = 1; i < rows.length; i++) {
            const row = rows[i];
            const statusText = row.cells[7]?.textContent.toLowerCase() || "";
            const paymentMethodText = row.cells[6]?.textContent.toLowerCase() || "";

            let showRow = true;

            // Lọc theo tình trạng thanh toán
            if (statusFilter) {
                if (statusFilter === "paid" && !statusText.includes("đã thanh toán")) {
                    showRow = false;
                } else if (statusFilter === "unpaid" && !statusText.includes("chưa thanh toán")) {
                    showRow = false;
                } else if (statusFilter === "partial" && !statusText.includes("thanh toán một phần")) {
                    showRow = false;
                }
            }

            // Lọc theo hình thức thanh toán
            if (paymentMethodFilter) {
                if (paymentMethodFilter === "cash" && !paymentMethodText.includes("cash")) {
                    showRow = false;
                } else if (paymentMethodFilter === "bank" && !paymentMethodText.includes("bank")) {
                    showRow = false;
                } else if (paymentMethodFilter === "card" && !paymentMethodText.includes("card")) {
                    showRow = false;
                } else if (paymentMethodFilter === "ewallet" && !paymentMethodText.includes("e-wallet")) {
                    showRow = false;
                }
            }

            row.style.display = showRow ? "" : "none";
        }
    }

    if (document.getElementById("resetFilter")) {
        document.getElementById("resetFilter").addEventListener("click", function() {
            const searchInput = document.getElementById("searchInput");
            if (searchInput) searchInput.value = "";

            const statusFilter = document.getElementById("statusFilter");
            if (statusFilter) statusFilter.value = "";

            const paymentMethodFilter = document.getElementById("paymentMethodFilter");
            if (paymentMethodFilter) paymentMethodFilter.value = "";

            const table = document.getElementById("invoiceTable");
            if (!table) return;

            const rows = table.getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) {
                rows[i].style.display = "";
            }
        });
    }

    // Tính tổng tiền khi nhập phí
    function calculateTotal() {
        const room = parseFloat(document.getElementById('room_charge')?.value) || 0;
        const service = parseFloat(document.getElementById('service_charge')?.value) || 0;
        const vat = parseFloat(document.getElementById('vat')?.value) || 0;
        const other = parseFloat(document.getElementById('other_fees')?.value) || 0;
        const total = room + service + vat + other;
        const totalField = document.getElementById('total_amount');
        if (totalField) {
            totalField.value = total.toFixed(2);
        }
        calculateRemaining();
    }

    // Tính số tiền còn lại
    function calculateRemaining() {
        const total = parseFloat(document.getElementById('total_amount')?.value) || 0;
        const deposit = parseFloat(document.getElementById('deposit_amount')?.value) || 0;
        const remaining = total - deposit;
        const remainingField = document.getElementById('remaining_amount');
        if (remainingField) {
            remainingField.value = Math.max(0, remaining).toFixed(2);
        }
    }

    // Gán sự kiện tính toán khi thay đổi các trường
    document.addEventListener('DOMContentLoaded', function() {
        const roomChargeField = document.getElementById('room_charge');
        const serviceChargeField = document.getElementById('service_charge');
        const vatField = document.getElementById('vat');
        const otherFeesField = document.getElementById('other_fees');

        if (roomChargeField) roomChargeField.addEventListener('change', calculateTotal);
        if (serviceChargeField) serviceChargeField.addEventListener('change', calculateTotal);
        if (vatField) vatField.addEventListener('change', calculateTotal);
        if (otherFeesField) otherFeesField.addEventListener('change', calculateTotal);
    });

    <?php if ($editInvoice): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('invoice_id').value = '<?php echo $editInvoice['invoice_id']; ?>';
            // Booking ID đã được set trong HTML với readonly, không cần set lại bằng JS
            // document.getElementById('booking_id').value = '<?php echo $editInvoice['booking_id'] ?? ''; ?>';
            document.getElementById('room_charge').value = '<?php echo $editInvoice['room_charge']; ?>';
            document.getElementById('service_charge').value = '<?php echo $editInvoice['service_charge']; ?>';
            document.getElementById('vat').value = '<?php echo $editInvoice['vat']; ?>';
            document.getElementById('other_fees').value = '<?php echo $editInvoice['other_fees']; ?>';
            document.getElementById('total_amount').value = '<?php echo $editInvoice['total_amount']; ?>';
            document.getElementById('deposit_amount').value = '<?php echo $editInvoice['deposit_amount'] ?? 0; ?>';
            document.getElementById('remaining_amount').value = '<?php echo $editInvoice['remaining_amount'] ?? $editInvoice['total_amount']; ?>';
            document.getElementById('payment_method').value = '<?php echo $editInvoice['payment_method']; ?>';
            document.getElementById('status').value = '<?php echo $editInvoice['status']; ?>';
            document.getElementById('payment_time').value = '<?php echo $editInvoice['payment_time']; ?>';
            document.getElementById('note').value = '<?php echo h($editInvoice['note']); ?>';

            document.getElementById('modalTitle').textContent = 'Sửa Hóa Đơn';
            document.getElementById('submitBtn').textContent = 'Cập Nhật Hóa Đơn';
            document.getElementById('submitBtn').name = 'update_invoice';

            const modal = new bootstrap.Modal(document.getElementById('addInvoiceModal'));
            modal.show();
        });
    <?php endif; ?>
</script>