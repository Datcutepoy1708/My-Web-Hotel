<?php
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
        if (empty($customer_id)) {
            $errors[] = "Khách hàng không được để trống";
        }
        if (empty($service_id)) {
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
            $stmt = $mysqli->prepare("INSERT INTO booking_service (customer_id, service_id, quantity, usage_date, usage_time, booking_id, amount, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiissidss", $customer_id, $service_id, $quantity, $usage_date, $usage_time, $booking_id, $amount, $notes, $status);

            if ($stmt->execute()) {
                $message = 'Thêm booking dịch vụ thành công!';
                $messageType = 'success';

                // Nếu không có booking_id (chỉ booking dịch vụ), tự động tạo invoice
                if (!$booking_id) {
                    $booking_service_id = $stmt->insert_id;

                    // Tạo invoice cho dịch vụ này
                    $invoice_stmt = $mysqli->prepare("INSERT INTO invoice (booking_id, customer_id, room_charge, service_charge, vat, other_fees, total_amount, payment_method, status, created_at) VALUES (NULL, ?, 0, ?, 0, 0, ?, 'Cash', 'Unpaid', NOW())");
                    $invoice_stmt->bind_param("idd", $customer_id, $amount, $amount);
                    $invoice_stmt->execute();
                    $invoice_stmt->close();

                    $message .= ' Đã tự động tạo hóa đơn!';
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
    if (isset($_POST['update_booking_service'])) {
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
        if (empty($customer_id)) {
            $errors[] = "Khách hàng không được để trống";
        }
        if (empty($service_id)) {
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
            // UPDATE với booking_id có thể NULL
            $stmt = $mysqli->prepare("UPDATE booking_service SET customer_id=?, service_id=?, quantity=?, usage_date=?, usage_time=?, booking_id=?, amount=?, notes=?, status=? WHERE booking_service_id=? AND deleted IS NULL");
            $stmt->bind_param("iiissidssi", $customer_id, $service_id, $quantity, $usage_date, $usage_time, $booking_id, $amount, $notes, $status, $booking_service_id);

            if ($stmt->execute()) {
                $message = 'Cập nhật booking dịch vụ thành công!';
                $messageType = 'success';
                $action = '';
                header("Location: index.php?page=booking-services-manager&success=1");
                exit;
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
        LEFT JOIN inovice i ON(i.booking_id=bs.booking_id OR i.customer_id=bs.customer_id)
        WHERE bs.booking_service_id = ? AND i.deleted IS NULL
        ");
        $check_stmt->bind_param("i", $booking_service_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $invoice_data = $result->fetch_assoc();
        $check_stmt->close();

        if ($invoice_data && $invoice_data['status'] == 'Paid') {
            $messageType = 'Không thể xóa!Dịch vụ này đã có hóa đơn thanh toán';
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
                ) + i.vat +i.orther_fees
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
$editBookingSerivce = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare(
        "SELECT  "
    );
}

?>

<div class="content-card">
    <div class="card-header-custom">
        <h3 class="card-title">Danh Sách Booking Dịch Vụ</h3>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="fas fa-plus"></i> Thêm Booking Dịch Vụ
        </button>
    </div>

    <!-- Filter -->
    <div class="filter-section">
        <form>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Tìm tên khách hoặc phòng..." />
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option>Tất cả trạng thái</option>
                        <option>Chờ xác nhận</option>
                        <option>Đã xác nhận</option>
                        <option>Hoàn thành</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option>Tất cả dịch vụ</option>
                        <option>Spa & Massage</option>
                        <option>Nhà Hàng</option>
                        <option>Gym & Fitness</option>
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
                    <th>Dịch Vụ</th>
                    <th>Ngày</th>
                    <th>Giờ</th>
                    <th>Số Người</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>#SV001</td>
                    <td><strong>Phạm Thị D</strong><br><small>0934567890</small></td>
                    <td><span class="badge bg-info">Spa & Massage</span></td>
                    <td>16/11/2025</td>
                    <td>14:00</td>
                    <td>1 người</td>
                    <td><strong>1,200,000 VNĐ</strong></td>
                    <td><span class="badge-status badge-approved">Đã xác nhận</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" title="Sửa">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>#SV002</td>
                    <td><strong>Hoàng Văn E</strong><br><small>0945678901</small></td>
                    <td><span class="badge bg-info">Nhà Hàng</span></td>
                    <td>18/11/2025</td>
                    <td>19:00</td>
                    <td>10 người</td>
                    <td><strong>8,500,000 VNĐ</strong></td>
                    <td><span class="badge-status badge-pending">Chờ xác nhận</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" title="Sửa">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>#SV003</td>
                    <td><strong>Đỗ Thị F</strong><br><small>0956789012</small></td>
                    <td><span class="badge bg-info">Tour Du Lịch</span></td>
                    <td>22/11/2025</td>
                    <td>07:00</td>
                    <td>4 người</td>
                    <td><strong>6,800,000 VNĐ</strong></td>
                    <td><span class="badge-status badge-approved">Đã xác nhận</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" title="Sửa">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <li class="page-item disabled"><a class="page-link">Trước</a></li>
            <li class="page-item active"><a class="page-link">1</a></li>
            <li class="page-item"><a class="page-link">2</a></li>
            <li class="page-item"><a class="page-link">3</a></li>
            <li class="page-item"><a class="page-link">Sau</a></li>
        </ul>
    </nav>
</div>
<!-- Modal Thêm Booking Dịch Vụ -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Booking Dịch Vụ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên Khách Hàng *</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Điện Thoại *</label>
                            <input type="tel" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loại Dịch Vụ *</label>
                            <select class="form-select" required>
                                <option>-- Chọn dịch vụ --</option>
                                <option>Spa & Massage</option>
                                <option>Nhà Hàng</option>
                                <option>Gym & Fitness</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Người *</label>
                            <input type="number" class="form-control" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Sử Dụng *</label>
                            <input type="date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giờ Sử Dụng *</label>
                            <input type="time" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"> Số lượng *</label>
                            <input type="number" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tổng Tiền (VNĐ) *</label>
                            <input type="number" class="form-control" step="1000" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng Thái *</label>
                            <select class="form-select" required>
                                <option>Chờ xác nhận</option>
                                <option>Đã xác nhận</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi Chú</label>
                        <textarea class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-primary-custom">Thêm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>