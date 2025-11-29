<?php
// Include helper function để tự động tạo hóa đơn
require_once __DIR__ . '/../includes/invoice_helper.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$messageType = '';

// Thêm booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_booking_room'])) {
        $customer_id = intval($_POST['customer_id']);
        $phone = trim($_POST['phone']);
        $room_id = intval($_POST['room_id']);
        $quantity = intval($_POST['quantity']);
        $check_in_date = $_POST['check_in_date'];
        $check_out_date = $_POST['check_out_date'];
        $status = $_POST['status'] ?? 'Pending';
        $special_request = trim($_POST['special_request'] ?? '');

        // Validate dữ liệu
        $errors = [];

        if (empty($customer_id)) {
            $errors[] = "Mã khách hàng không được để trống";
        }

        if (empty($phone)) {
            $errors[] = "Số điện thoại không được để trống";
        }

        if ($room_id <= 0) {
            $errors[] = "Vui lòng chọn phòng";
        }

        if ($quantity <= 0) {
            $errors[] = "Số khách phải lớn hơn 0";
        }

        if (empty($check_in_date) || empty($check_out_date)) {
            $errors[] = "Vui lòng chọn ngày check-in và check-out";
        }

        // Kiểm tra ngày check-out phải sau check-in
        if (strtotime($check_out_date) <= strtotime($check_in_date)) {
            $errors[] = "Ngày check-out phải sau ngày check-in";
        }

        // Kiểm tra phòng có sẵn không
        $checkAvailability = $mysqli->prepare(
            "SELECT COUNT(*) as count FROM booking
             WHERE room_id = ? 
             AND status NOT IN ('Cancelled', 'Completed')
             AND deleted IS NULL
             AND (
                 (check_in_date <= ? AND check_out_date >= ?) OR
                 (check_in_date <= ? AND check_out_date >= ?) OR
                 (check_in_date >= ? AND check_out_date <= ?)
             )"
        );
        $checkAvailability->bind_param(
            "issssss",
            $room_id,
            $check_in_date,
            $check_in_date,
            $check_out_date,
            $check_out_date,
            $check_in_date,
            $check_out_date
        );
        $checkAvailability->execute();
        $result = $checkAvailability->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            $errors[] = "Phòng đã được đặt trong khoảng thời gian này";
        }
        $checkAvailability->close();

        // Nếu không có lỗi thì thêm booking
        if (empty($errors)) {
            $booking_date = date('Y-m-d H:i:s');
            $stmt = $mysqli->prepare(
                "INSERT INTO booking(booking_date, check_in_date, check_out_date, quantity, 
                special_request, status, customer_id, room_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->bind_param(
                "sssissii",
                $booking_date,
                $check_in_date,
                $check_out_date,
                $quantity,
                $special_request,
                $status,
                $customer_id,
                $room_id
            );
            if ($stmt->execute()) {
                $booking_id = $stmt->insert_id;
                $message = "Thêm booking phòng thành công";
                $messageType = "success";
                
                // Tự động tạo hóa đơn nếu status = Confirmed
                if ($status === 'Confirmed') {
                    $invoice_id = createInvoiceForRoomBooking($mysqli, $booking_id);
                    if ($invoice_id) {
                        $message .= ' Hóa đơn đã được tạo tự động (ID: ' . $invoice_id . ')';
                    }
                }
                
                $action = '';
                header("Location: index.php?page=booking-manager&panel=roomBooking-panel");
                exit;
            } else {
                $message = 'Lỗi: ' . $stmt->error;
                $messageType = 'danger';
            }
            $stmt->close();
        } else {
            $message = implode("<br>", $errors);
            $messageType = "danger";
        }
    }

    if (isset($_POST['update_room_booking'])) {
        $booking_id = intval($_POST['booking_id']);
        $customer_id = intval($_POST['customer_id']);
        $phone = trim($_POST['phone']);
        $room_id = intval($_POST['room_id']);
        $quantity = intval($_POST['quantity']);
        $check_in_date = $_POST['check_in_date'];
        $check_out_date = $_POST['check_out_date'];
        $status = $_POST['status'] ?? 'Pending';
        $special_request = trim($_POST['special_request'] ?? '');

        $stmt = $mysqli->prepare(
            "UPDATE booking SET customer_id=?, phone=?, room_id=?, quantity=?, 
            check_in_date=?, check_out_date=?, status=?, special_request=? 
            WHERE booking_id=? AND deleted IS NULL"
        );
        $stmt->bind_param(
            "isiissssi",
            $customer_id,
            $phone,
            $room_id,
            $quantity,
            $check_in_date,
            $check_out_date,
            $status,
            $special_request,
            $booking_id
        );

        if ($stmt->execute()) {
            $message = 'Cập nhật booking phòng thành công';
            $messageType = 'success';
            
            // Tự động tạo hóa đơn nếu status = Confirmed và chưa có hóa đơn
            if ($status === 'Confirmed') {
                $invoice_id = createInvoiceForRoomBooking($mysqli, $booking_id);
                if ($invoice_id) {
                    $message .= ' Hóa đơn đã được tạo tự động (ID: ' . $invoice_id . ')';
                }
            }
            
            header("Location: index.php?page=booking-manager&panel=roomBooking-panel");
            exit;
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }

    // Xóa booking phòng
    if (isset($_POST['delete_booking_room'])) {
        $booking_id = intval($_POST['booking_id']);
        $stmt = $mysqli->prepare("UPDATE booking SET deleted=NOW() WHERE booking_id=?");
        $stmt->bind_param("i", $booking_id);
        if ($stmt->execute()) {
            $message = 'Xóa booking thành công';
            $messageType = 'success';
            header("Location: index.php?page=booking-manager&panel=roomBooking-panel");
            exit;
        } else {
            $message = 'Xóa booking thất bại';
            $messageType = 'danger';
        }
        $stmt->close();
    }
}

// Lấy thông tin booking ra để edit
$editBookingRoom = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare(
        "SELECT b.*, c.phone, c.full_name, c.email, 
                r.room_number, rt.room_type_name
         FROM booking b
         LEFT JOIN customer c ON b.customer_id = c.customer_id
         LEFT JOIN room r ON b.room_id = r.room_id
         LEFT JOIN room_type rt ON r.room_type_id = rt.room_type_id
         WHERE b.booking_id=? AND b.deleted IS NULL"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editBookingRoom = $result->fetch_assoc();
    $stmt->close();
}

// LẤY DANH SÁCH CUSTOMER
$customersResult = $mysqli->query(
    "SELECT customer_id, full_name, phone, email 
     FROM customer 
     WHERE deleted IS NULL 
     ORDER BY full_name ASC"
);
$customers = $customersResult->fetch_all(MYSQLI_ASSOC);

// LẤY DANH SÁCH PHÒNG AVAILABLE
$roomsResult = $mysqli->query(
    "SELECT r.room_id, r.room_number, rt.room_type_name, rt.base_price
     FROM room r
     JOIN room_type rt ON r.room_type_id = rt.room_type_id
     WHERE r.deleted IS NULL AND r.status = 'Available'
     ORDER BY r.room_number ASC"
);
$availableRooms = $roomsResult->fetch_all(MYSQLI_ASSOC);

// Phân trang và tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$type_filter = intval($_GET['type'] ?? 0);
$pageNum = isset($_GET['pageNum']) ? intval($_GET['pageNum']) : 1;
$pageNum = max(1, $pageNum);
$perPage = 5;
$offset = ($pageNum - 1) * $perPage;

// Xây dựng query
$where = "WHERE b.deleted IS NULL";
$params = [];
$types = '';

if ($search) {
    $where .= " AND (b.booking_id LIKE ? OR c.full_name LIKE ? OR r.room_number LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    $types .= 'sss';
}

if ($status_filter) {
    $where .= " AND b.status=?";
    $params[] = $status_filter;
    $types .= 's';
}
if ($type_filter) {
    $where .= " AND rt.room_type_id = ?";
    $params[] = $type_filter;
    $types .= 'i';
}

// Đếm tổng số booking
$countSql = "SELECT COUNT(*) as total 
             FROM booking b
             LEFT JOIN customer c ON b.customer_id = c.customer_id
             LEFT JOIN room r ON b.room_id = r.room_id
             LEFT JOIN room_type rt ON r.room_type_id = rt.room_type_id
             $where";

$countStmt = $mysqli->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalResult = $countStmt->get_result();
$totalBookingRooms = $totalResult->fetch_assoc()['total'];
$countStmt->close();

// Get bookings data
$sql = "SELECT 
    b.booking_id,
    b.booking_date,
    b.check_in_date,
    b.check_out_date,
    b.quantity,
    b.special_request,
    b.status,
    c.customer_id,
    c.full_name,
    c.phone,
    c.email,
    r.room_id,
    r.room_number,
    rt.room_type_id,
    rt.room_type_name,
    rt.base_price
FROM booking b
LEFT JOIN customer c ON b.customer_id = c.customer_id
LEFT JOIN room r ON b.room_id = r.room_id
LEFT JOIN room_type rt ON r.room_type_id = rt.room_type_id
$where
ORDER BY b.created_at DESC
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
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Lấy ra tên room type
$roomTypesResult = $mysqli->query("SELECT * FROM room_type WHERE deleted IS NULL");
$roomTypes = $roomTypesResult->fetch_all(MYSQLI_ASSOC);

// Build base URL for pagination
$baseUrl = "index.php?page=booking-manager&panel=roomBooking-panel";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urlencode($status_filter);
if ($type_filter) $baseUrl .= "&type=" . $type_filter;
?>

<div class="content-card">
    <div class="card-header-custom">
        <h3 class="card-title">Danh Sách Booking Phòng</h3>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addRoomBookingModal">
            <i class="fas fa-plus"></i> Thêm Booking Phòng
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
            <input type="hidden" name="panel" value="roomBooking-panel">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" value="<?php echo h($search); ?>" name="search" placeholder="Tìm tên khách hoặc phòng..." />
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status" id="statusFilter">
                        <option value="">Tất cả tình trạng</option>
                        <option value="Confirmed" <?php echo $status_filter == 'Confirmed' ? 'selected' : ''; ?>>Đã xác nhận</option>
                        <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Chưa thanh toán</option>
                        <option value="Completed" <?php echo $status_filter == 'Completed' ? 'selected' : ''; ?>>Đã hoàn thành</option>
                        <option value="Cancelled" <?php echo $status_filter == 'Cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="type" id="typeFilter">
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

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover" id="roomsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Khách</th>
                    <th>Phòng</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Số Khách</th>
                    <th>Ghi chú</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="9" class="text-center">Không có dữ liệu</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo h($booking['booking_id']); ?></td>
                            <td>
                                <strong><?php echo h($booking['full_name']); ?></strong><br>
                                <small><?php echo h($booking['phone']); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo h($booking['room_number']); ?> - <?php echo h($booking['room_type_name']); ?>
                                </span>
                            </td>
                            <td><?php echo h($booking['check_in_date']); ?></td>
                            <td><?php echo h($booking['check_out_date']); ?></td>
                            <td><?php echo h($booking['quantity']); ?> người</td>
                            <td><?php echo h($booking['special_request']); ?></td>
                            <td>
                                <?php
                                $statusClass = 'bg-secondary';
                                $statusText = $booking['status'];
                                switch ($booking['status']) {
                                    case 'Confirmed':
                                        $statusClass = 'bg-primary';
                                        $statusText = 'Đã xác nhận';
                                        break;
                                    case 'Completed':
                                        $statusClass = 'bg-success';
                                        $statusText = 'Đã hoàn thành';
                                        break;
                                    case 'Pending':
                                        $statusClass = 'bg-warning';
                                        $statusText = 'Chưa thanh toán';
                                        break;
                                    case 'Cancelled':
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Đã hủy';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewBookingModal<?php echo $booking['booking_id']; ?>"
                                    title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning"
                                    onclick="editRoomBooking(<?php echo $booking['booking_id']; ?>)" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="deleteRoomBooking(<?php echo $booking['booking_id']; ?>)" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- View Detail Modal -->
                        <div class="modal fade" id="viewBookingModal<?php echo $booking['booking_id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Chi tiết booking #<?php echo $booking['booking_id']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6"><strong>Mã Booking:</strong> <?php echo h($booking['booking_id']); ?></div>
                                            <div class="col-md-6"><strong>Ngày tạo:</strong> <?php echo h($booking['booking_date']); ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6"><strong>Tên khách hàng:</strong> <?php echo h($booking['full_name']); ?></div>
                                            <div class="col-md-6"><strong>Số điện thoại:</strong> <?php echo h($booking['phone']); ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6"><strong>Check-in:</strong> <?php echo h($booking['check_in_date']); ?></div>
                                            <div class="col-md-6"><strong>Check-out:</strong> <?php echo h($booking['check_out_date']); ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6"><strong>Số lượng:</strong> <?php echo h($booking['quantity']); ?> người</div>
                                            <div class="col-md-6"><strong>Phòng:</strong> <?php echo h($booking['room_number']); ?> - <?php echo h($booking['room_type_name']); ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-12"><strong>Yêu cầu đặc biệt:</strong> <?php echo h($booking['special_request']); ?></div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <strong>Trạng thái:</strong>
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                        <a href="index.php?page=booking-manager&panel=roomBooking-panel&action=edit&id=<?php echo $booking['booking_id']; ?>"
                                            class="btn btn-primary">
                                            Chỉnh sửa
                                        </a>
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
    <?php echo getPagination($totalBookingRooms, $perPage, $pageNum, $baseUrl); ?>
</div>

<!-- Modal Thêm/Sửa Booking -->
<div class="modal fade" id="addRoomBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $editBookingRoom ? 'Sửa' : 'Thêm'; ?> Booking Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="reset" onclick="clearEditMode()">
                </button>
            </div>
            <form method="POST" id="bookingForm">
                <?php if ($editBookingRoom): ?>
                    <input type="hidden" name="booking_id" value="<?php echo $editBookingRoom['booking_id']; ?>">
                <?php endif; ?>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên Khách Hàng *</label>
                            <select class="form-select customer-search" name="customer_id" required id="customerSelectRoom">
                                <option value="">-- Chọn khách hàng --</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['customer_id']; ?>"
                                        <?php echo ($editBookingRoom && $editBookingRoom['customer_id'] == $customer['customer_id']) ? 'selected' : ''; ?> data-phone="<?php echo h($customer['phone']); ?>"
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
                                value="<?php echo $editBookingRoom ? $editBookingRoom['phone'] : ''; ?>"
                                required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Phòng *</label>
                            <select class="form-select" name="room_id" required>
                                <option value="">-- Chọn phòng --</option>
                                <?php foreach ($availableRooms as $room): ?>
                                    <option value="<?php echo $room['room_id']; ?>"
                                        <?php echo ($editBookingRoom && $editBookingRoom['room_id'] == $room['room_id']) ? 'selected' : ''; ?>>
                                        <?php echo h($room['room_number']); ?> - <?php echo h($room['room_type_name']); ?>
                                        (<?php echo number_format($room['base_price']); ?> VNĐ)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Khách *</label>
                            <input type="number"
                                class="form-control"
                                name="quantity"
                                min="1"
                                value="<?php echo $editBookingRoom ? h($editBookingRoom['quantity']) : '1'; ?>"
                                required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Check-in *</label>
                            <input type="date"
                                class="form-control"
                                name="check_in_date"
                                value="<?php echo $editBookingRoom ? h($editBookingRoom['check_in_date']) : ''; ?>"
                                required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Check-out *</label>
                            <input type="date"
                                class="form-control"
                                name="check_out_date"
                                value="<?php echo $editBookingRoom ? h($editBookingRoom['check_out_date']) : ''; ?>"
                                required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng Thái *</label>
                            <select class="form-select" name="status" required>
                                <option value="Pending" <?php echo ($editBookingRoom && $editBookingRoom['status'] == 'Pending') ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                <option value="Confirmed" <?php echo ($editBookingRoom && $editBookingRoom['status'] == 'Confirmed') ? 'selected' : ''; ?>>Đã xác nhận</option>
                                <option value="Completed" <?php echo ($editBookingRoom && $editBookingRoom['status'] == 'Completed') ? 'selected' : ''; ?>>Đã hoàn thành</option>
                                <option value="Cancelled" <?php echo ($editBookingRoom && $editBookingRoom['status'] == 'Cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi Chú</label>
                        <textarea class="form-control"
                            name="special_request"
                            rows="3"><?php echo $editBookingRoom ? h($editBookingRoom['special_request']) : ''; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit"
                        name="<?php echo $editBookingRoom ? 'update_room_booking' : 'add_booking_room'; ?>"
                        class="btn-primary-custom">
                        <?php echo $editBookingRoom ? 'Cập nhật' : 'Thêm'; ?> Booking
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
    // Auto-fill phone number when selecting customer
    document.addEventListener('DOMContentLoaded', function() {
        const customerSelect = document.querySelector('select[name="customer_id"]');
        const phoneInput = document.querySelector('input[name="phone"]');

        if (customerSelect && phoneInput) {
            customerSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const phoneText = selectedOption.text.split(' - ')[1];
                if (phoneText) {
                    phoneInput.value = phoneText;
                }
            });
        }

        // Set minimum date for check-in to today
        const checkInDate = document.querySelector('input[name="check_in_date"]');
        const checkOutDate = document.querySelector('input[name="check_out_date"]');

        if (checkInDate) {
            const today = new Date().toISOString().split('T')[0];
            checkInDate.setAttribute('min', today);

            checkInDate.addEventListener('change', function() {
                checkOutDate.setAttribute('min', this.value);
                if (checkOutDate.value && checkOutDate.value <= this.value) {
                    checkOutDate.value = '';
                }
            });
        }
    });

    function editRoomBooking(id) {
        window.location.href = 'index.php?page=booking-manager&panel=roomBooking-panel&action=edit&id=' + id;
    }

    function deleteRoomBooking(id) {
        if (confirm('Bạn có chắc chắn muốn xóa phòng này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="room_id" value="' + id + '">' +
                '<input type="hidden" name="delete_room" value="1">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    <?php if ($editBookingRoom): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('addRoomBookingModal'));
            modal.show();
            // Khởi tạo lại Select2 sau khi modal mở hoàn toàn
            const modalEl = document.getElementById('addRoomBookingModal');
            modalEl.addEventListener('shown.bs.modal', function() {
                setTimeout(initCustomerSelect2Room, 200);
            }, { once: true });
        });
    <?php endif; ?>
    
    // Khởi tạo lại Select2 khi modal mở
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('addRoomBookingModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', function() {
                setTimeout(initCustomerSelect2Room, 200);
            });
        }
        
        // Khởi tạo lần đầu nếu không trong modal
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(function() {
                setTimeout(initCustomerSelect2Room, 300);
            });
        }
    });

    function resetForm() {
        const form = document.getElementById('bookingForm');
        if (form) { // ✅ Thêm check null để an toàn
            form.reset();

            // Xóa booking_id hidden input nếu có
            const bookingIdInput = form.querySelector('input[name="booking_id"]');
            if (bookingIdInput) {
                bookingIdInput.remove();
            }

            // Reset modal title
            const modalTitle = document.querySelector('#addRoomBookingModal .modal-title');
            if (modalTitle) {
                modalTitle.textContent = 'Thêm Booking Phòng';
            }

            // Reset submit button
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.textContent = 'Thêm Booking';
                submitBtn.name = 'add_booking_room';
            }
        }
    }

    function clearEditMode() {
        const url = new URL(window.location);
        url.searchParams.delete('action');
        url.searchParams.delete('id');
        window.history.replaceState({}, '', url);
        resetForm();
    }
    
    // Hàm khởi tạo Select2 cho customer
    function initCustomerSelect2Room() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
            return false;
        }
        
        const $customerSelect = jQuery('#customerSelectRoom');
        if (!$customerSelect.length) {
            return false;
        }
        
        // Destroy nếu đã khởi tạo trước đó
        if ($customerSelect.hasClass('select2-hidden-accessible')) {
            $customerSelect.select2('destroy');
        }
        
        // Lấy modal để set dropdownParent
        const $modal = jQuery('#addRoomBookingModal');
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
            jQuery('input[name="phone"]').val(phone);
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
</script>