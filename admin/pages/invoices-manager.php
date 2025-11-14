<?php
// Xử lý CRUD trong invoice-manager
 $action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Thêm hóa đơn
    if (isset($_POST['add_invoice'])) {
        $booking_id = intval($_POST['booking_id']);
        $room_charge = floatval($_POST['room_charge']);
        $service_charge = floatval($_POST['service_charge']);
        $vat = floatval($_POST['vat']);
        $other_fees = floatval($_POST['other_fees']);
        $total_amount = $room_charge + $service_charge + $vat + $other_fees;
        $payment_method = trim($_POST['payment_method']);
        $status = $_POST['status'] ?? 'Unpaid';
        $payment_time = !empty($_POST['payment_time']) ? $_POST['payment_time'] : null;
        $note = trim($_POST['note'] ?? '');
        
        $stmt = $mysqli->prepare("INSERT INTO invoice (booking_id, room_charge, service_charge, vat, other_fees, total_amount, payment_method, status, payment_time, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idddddssts", $booking_id, $room_charge, $service_charge, $vat, $other_fees, $total_amount, $payment_method, $status, $payment_time, $note);
        
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
    
    // Cập nhật hóa đơn
    if (isset($_POST['update_invoice'])) {
        $invoice_id = intval($_POST['invoice_id']);
        $booking_id = intval($_POST['booking_id']);
        $room_charge = floatval($_POST['room_charge']);
        $service_charge = floatval($_POST['service_charge']);
        $vat = floatval($_POST['vat']);
        $other_fees = floatval($_POST['other_fees']);
        $total_amount = $room_charge + $service_charge + $vat + $other_fees;
        $payment_method = trim($_POST['payment_method']);
        $status = $_POST['status'] ?? 'Unpaid';
        $payment_time = !empty($_POST['payment_time']) ? $_POST['payment_time'] : null;
        $note = trim($_POST['note'] ?? '');
        
        $stmt = $mysqli->prepare("UPDATE invoice SET booking_id=?, room_charge=?, service_charge=?, vat=?, other_fees=?, total_amount=?, payment_method=?, status=?, payment_time=?, note=? WHERE invoice_id=? AND deleted IS NULL");
        $stmt->bind_param("idddddssstsi", $booking_id, $room_charge, $service_charge, $vat, $other_fees, $total_amount, $payment_method, $status, $payment_time, $note, $invoice_id);
        
        if ($stmt->execute()) {
            $message = 'Cập nhật hóa đơn thành công!';
            $messageType = 'success';
            $action = '';
            header("Location: index.php?page=invoice-manager&success=1");
            exit;
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
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
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM invoice WHERE invoice_id = ? AND deleted IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editInvoice = $result->fetch_assoc();
    $stmt->close();
}

// Phân trang và tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

if ($page < 1) $page = 1;
$offset = ($page - 1) * $perPage;

// Xây dựng WHERE clause
$where = "WHERE i.deleted IS NULL";

if ($search) {
    $search = $mysqli->real_escape_string($search);
    $where .= " AND (i.invoice_id LIKE '%$search%' OR b.booking_id LIKE '%$search%')";
}

if ($status_filter) {
    $status_filter = $mysqli->real_escape_string($status_filter);
    $where .= " AND i.status = '$status_filter'";
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
$countQuery = "SELECT COUNT(*) as total FROM invoice i LEFT JOIN booking b ON i.booking_id = b.booking_id $where";
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
    $query = "SELECT i.*, b.booking_id, b.customer_id, c.full_name
        FROM invoice i 
        LEFT JOIN booking b ON i.booking_id = b.booking_id
        LEFT JOIN customer c ON b.customer_id = c.customer_id
        $where 
        $orderBy 
        LIMIT $perPage OFFSET $offset";
    
    $result = $mysqli->query($query);
    if (!$result) {
        error_log("Query Error: " . $mysqli->error);
        $message = "Lỗi truy vấn: " . $mysqli->error;
        $messageType = 'danger';
    } else {
        $invoices = $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Build base URL for pagination
$baseUrl = "index.php?page=invoice-manager";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urlencode($status_filter);
if ($sort) $baseUrl .= "&sort=" . urlencode($sort);
?>

?>

<div class="main-content">
    <div class="content-header">
        <h1>Quản Lý Hóa Đơn</h1>
        <!-- Tabs loại hóa đơn -->
        <ul class="nav nav-pills mb-3" id="invoiceTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-room" data-bs-toggle="pill" data-bs-target="#panel-room"
                    type="button" role="tab">
                    Hóa Đơn Phòng
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-service" data-bs-toggle="pill" data-bs-target="#panel-service"
                    type="button" role="tab">
                    Hóa Đơn Dịch Vụ
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <!-- Bảng: Hóa đơn phòng -->
        <div class="tab-pane fade show active" id="panel-room" role="tabpanel">
            <div class="filter-section">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchRoomInvoice" placeholder="Tìm hóa đơn phòng..." />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusRoomInvoice">
                            <option value="">Tất cả tình trạng</option>
                            <option value="completed">Hoàn tất</option>
                            <option value="deposit">Đã cọc</option>
                            <option value="unpaid">Chưa thanh toán</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="roomFilter">
                            <option value="">Tất cả phòng</option>
                            <option value="101">Phòng VIP</option>
                            <option value="102">Phòng Đơn</option>
                            <option value="201">Phòng Đôi</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table table-hover" id="tableRoomInvoice">
                    <thead>
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Phòng</th>
                            <th>Khách Hàng</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Tổng Tiền</th>
                            <th>Tình Trạng</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>HDR001</strong></td>
                            <td>Phòng 101</td>
                            <td>Nguyễn Văn A</td>
                            <td>15/10/2025</td>
                            <td>18/10/2025</td>
                            <td>1,500,000 VNĐ</td>
                            <td><span class="badge bg-success">Hoàn tất</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editRoomInvoice('HDR001')"
                                    title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice('HDR001')"
                                    title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                    data-bs-target="#viewRoomInvoiceModal" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>HDR002</strong></td>
                            <td>Phòng 102</td>
                            <td>Trần Thị B</td>
                            <td>20/10/2025</td>
                            <td>22/10/2025</td>
                            <td>2,200,000 VNĐ</td>
                            <td><span class="badge bg-warning">Đã cọc</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editRoomInvoice('HDR002')"
                                    title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice('HDR002')"
                                    title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>HDR003</strong></td>
                            <td>Phòng 201</td>
                            <td>Lê Văn C</td>
                            <td>25/10/2025</td>
                            <td>28/10/2025</td>
                            <td>3,500,000 VNĐ</td>
                            <td><span class="badge bg-danger">Chưa thanh toán</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editRoomInvoice('HDR003')"
                                    title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice('HDR003')"
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
        </div>

        

        <!-- Bảng: Hóa đơn dịch vụ -->
        <div class="tab-pane fade" id="panel-service" role="tabpanel">
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
        </div>
    </div>
</div>

<!-- Modal Thêm/Sửa Hóa Đơn Phòng -->
<div class="modal fade" id="invoiceRoomModal" tabindex="-1">
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
</div>

<!-- Modal Thêm/Sửa Hóa Đơn Dịch Vụ -->
<div class="modal fade" id="invoiceServiceModal" tabindex="-1">
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
</div>

<!-- Modal Xem Chi Tiết Hóa Đơn Phòng -->
<div class="modal fade" id="viewRoomInvoiceModal" tabindex="-1">
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
</div>

<!-- Modal Xem Chi Tiết Hóa Đơn Dịch Vụ -->
<div class="modal fade" id="viewServiceInvoiceModal" tabindex="-1">
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
</div>