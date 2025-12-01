<?php
session_start();
require '../includes/connect.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Xem chi tiết voucher
if ($action == 'view' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT v.*, 
        nv.ho_ten as created_by_name,
        (SELECT COUNT(*) FROM voucher_usage vu WHERE vu.voucher_id = v.voucher_id) as usage_count,
        (SELECT COUNT(*) FROM voucher_customer vc WHERE vc.voucher_id = v.voucher_id) as assigned_count
        FROM voucher v
        LEFT JOIN nhan_vien nv ON v.created_by = nv.id_nhan_vien
        WHERE v.voucher_id = ? AND v.deleted IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $voucher = $result->fetch_assoc();
    $stmt->close();
    
    if ($voucher) {
        // Parse các trường dạng string thành array
        $voucher['customer_types_array'] = !empty($voucher['customer_types']) ? explode(',', $voucher['customer_types']) : [];
        $voucher['room_types_array'] = !empty($voucher['room_types']) ? explode(',', $voucher['room_types']) : [];
        $voucher['service_ids_array'] = !empty($voucher['service_ids']) ? explode(',', $voucher['service_ids']) : [];
        $voucher['valid_days_array'] = !empty($voucher['valid_days']) ? explode(',', $voucher['valid_days']) : [];
        $voucher['payment_methods_array'] = !empty($voucher['payment_methods']) ? explode(',', $voucher['payment_methods']) : [];
        
        // Lấy tên room types
        if (!empty($voucher['room_types_array'])) {
            $roomTypeIds = implode(',', array_map('intval', $voucher['room_types_array']));
            $roomTypesResult = $mysqli->query("SELECT room_type_id, room_type_name FROM room_type WHERE room_type_id IN ($roomTypeIds)");
            $voucher['room_types_names'] = $roomTypesResult->fetch_all(MYSQLI_ASSOC);
        }
        
        // Lấy tên services
        if (!empty($voucher['service_ids_array'])) {
            $serviceIds = implode(',', array_map('intval', $voucher['service_ids_array']));
            $servicesResult = $mysqli->query("SELECT service_id, service_name FROM service WHERE service_id IN ($serviceIds)");
            $voucher['services_names'] = $servicesResult->fetch_all(MYSQLI_ASSOC);
        }
        
        // Format HTML
        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary">Thông Tin Cơ Bản</h6>
                <table class="table table-sm">
                    <tr><td><strong>Mã Voucher:</strong></td><td><span class="badge bg-primary"><?php echo h($voucher['code']); ?></span></td></tr>
                    <tr><td><strong>Tên:</strong></td><td><?php echo h($voucher['name']); ?></td></tr>
                    <tr><td><strong>Mô tả:</strong></td><td><?php echo h($voucher['description'] ?: 'Không có'); ?></td></tr>
                    <?php if ($voucher['image']): ?>
                    <tr><td><strong>Hình ảnh:</strong></td><td><img src="<?php echo h($voucher['image']); ?>" alt="Voucher" style="max-width: 200px;"></td></tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary">Thông Tin Giảm Giá</h6>
                <table class="table table-sm">
                    <tr><td><strong>Loại giảm:</strong></td><td><span class="badge bg-info"><?php echo $voucher['discount_type'] == 'percent' ? 'Phần trăm (%)' : 'Số tiền cố định'; ?></span></td></tr>
                    <tr><td><strong>Giá trị:</strong></td><td>
                        <?php if ($voucher['discount_type'] == 'percent'): ?>
                            <?php echo number_format($voucher['discount_value'], 0); ?>%
                            <?php if ($voucher['max_discount']): ?>
                                <br><small>(Tối đa: <?php echo formatCurrency($voucher['max_discount']); ?>)</small>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php echo formatCurrency($voucher['discount_value']); ?>
                        <?php endif; ?>
                    </td></tr>
                    <tr><td><strong>Đơn tối thiểu:</strong></td><td><?php echo $voucher['min_order'] > 0 ? formatCurrency($voucher['min_order']) : 'Không giới hạn'; ?></td></tr>
                </table>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <h6 class="text-primary">Điều Kiện Áp Dụng</h6>
                <table class="table table-sm">
                    <tr><td><strong>Áp dụng cho:</strong></td><td>
                        <?php 
                        $applyToText = ['all' => 'Tất cả', 'room' => 'Chỉ phòng', 'service' => 'Chỉ dịch vụ'];
                        echo $applyToText[$voucher['apply_to']] ?? $voucher['apply_to'];
                        ?>
                    </td></tr>
                    <?php if (!empty($voucher['customer_types_array'])): ?>
                    <tr><td><strong>Loại khách hàng:</strong></td><td><?php echo implode(', ', $voucher['customer_types_array']); ?></td></tr>
                    <?php endif; ?>
                    <?php if ($voucher['min_nights']): ?>
                    <tr><td><strong>Số đêm tối thiểu:</strong></td><td><?php echo $voucher['min_nights']; ?> đêm</td></tr>
                    <?php endif; ?>
                    <?php if ($voucher['min_rooms']): ?>
                    <tr><td><strong>Số phòng tối thiểu:</strong></td><td><?php echo $voucher['min_rooms']; ?> phòng</td></tr>
                    <?php endif; ?>
                    <?php if (!empty($voucher['room_types_names'])): ?>
                    <tr><td><strong>Loại phòng:</strong></td><td>
                        <?php foreach ($voucher['room_types_names'] as $rt): ?>
                            <span class="badge bg-secondary"><?php echo h($rt['room_type_name']); ?></span>
                        <?php endforeach; ?>
                    </td></tr>
                    <?php endif; ?>
                    <?php if (!empty($voucher['services_names'])): ?>
                    <tr><td><strong>Dịch vụ:</strong></td><td>
                        <?php foreach ($voucher['services_names'] as $sv): ?>
                            <span class="badge bg-secondary"><?php echo h($sv['service_name']); ?></span>
                        <?php endforeach; ?>
                    </td></tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary">Thời Hạn & Sử Dụng</h6>
                <table class="table table-sm">
                    <tr><td><strong>Ngày bắt đầu:</strong></td><td><?php echo formatDate($voucher['start_date']); ?></td></tr>
                    <tr><td><strong>Ngày kết thúc:</strong></td><td><?php echo formatDate($voucher['end_date']); ?></td></tr>
                    <?php if (!empty($voucher['valid_days_array'])): ?>
                    <tr><td><strong>Ngày hợp lệ:</strong></td><td><?php echo implode(', ', $voucher['valid_days_array']); ?></td></tr>
                    <?php endif; ?>
                    <?php if ($voucher['valid_hours']): ?>
                    <tr><td><strong>Giờ hợp lệ:</strong></td><td><?php echo h($voucher['valid_hours']); ?></td></tr>
                    <?php endif; ?>
                    <tr><td><strong>Tổng số lần dùng:</strong></td><td><?php echo $voucher['total_uses']; ?></td></tr>
                    <tr><td><strong>Đã sử dụng:</strong></td><td><?php echo $voucher['usage_count']; ?> lần</td></tr>
                    <tr><td><strong>Mỗi khách dùng tối đa:</strong></td><td><?php echo $voucher['per_customer']; ?> lần</td></tr>
                </table>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <h6 class="text-primary">Cài Đặt</h6>
                <table class="table table-sm">
                    <tr><td><strong>Trạng thái:</strong></td><td>
                        <span class="badge <?php echo $voucher['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $voucher['status'] == 'active' ? 'Hoạt động' : 'Tạm dừng'; ?>
                        </span>
                    </td></tr>
                    <tr><td><strong>Nổi bật:</strong></td><td><?php echo $voucher['is_featured'] ? '<span class="badge bg-warning">Có</span>' : 'Không'; ?></td></tr>
                    <tr><td><strong>Công khai:</strong></td><td><?php echo $voucher['is_public'] ? '<span class="badge bg-info">Có</span>' : 'Không'; ?></td></tr>
                    <tr><td><strong>Tự động áp dụng:</strong></td><td><?php echo $voucher['auto_apply'] ? '<span class="badge bg-success">Có</span>' : 'Không'; ?></td></tr>
                    <tr><td><strong>Có thể dùng chung:</strong></td><td><?php echo $voucher['is_stackable'] ? '<span class="badge bg-primary">Có</span>' : 'Không'; ?></td></tr>
                    <tr><td><strong>Độ ưu tiên:</strong></td><td><?php echo $voucher['priority']; ?></td></tr>
                    <?php if (!empty($voucher['payment_methods_array'])): ?>
                    <tr><td><strong>Phương thức thanh toán:</strong></td><td><?php echo implode(', ', $voucher['payment_methods_array']); ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary">Thông Tin Khác</h6>
                <table class="table table-sm">
                    <tr><td><strong>Người tạo:</strong></td><td><?php echo h($voucher['created_by_name'] ?: 'N/A'); ?></td></tr>
                    <tr><td><strong>Ngày tạo:</strong></td><td><?php echo formatDate($voucher['created_at']); ?></td></tr>
                    <?php if (!empty($voucher['updated_at'])): ?>
                    <tr><td><strong>Ngày cập nhật:</strong></td><td><?php echo formatDate($voucher['updated_at']); ?></td></tr>
                    <?php endif; ?>
                    <tr><td><strong>Đã gán cho:</strong></td><td><?php echo $voucher['assigned_count']; ?> khách hàng</td></tr>
                </table>
            </div>
        </div>
        <?php
        $html = ob_get_clean();
        
        echo json_encode(['success' => true, 'html' => $html]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy voucher']);
    }
    exit;
}

// Xem lịch sử sử dụng voucher
if ($action == 'usage' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Lấy thông tin voucher
    $stmt = $mysqli->prepare("SELECT code, name FROM voucher WHERE voucher_id = ? AND deleted IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $voucher = $result->fetch_assoc();
    $stmt->close();
    
    if (!$voucher) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy voucher']);
        exit;
    }
    
    // Lấy lịch sử sử dụng
    $stmt = $mysqli->prepare("SELECT vu.*, 
        c.full_name as customer_name, c.phone as customer_phone,
        i.invoice_id, i.total_amount, i.discount
        FROM voucher_usage vu
        LEFT JOIN customer c ON vu.customer_id = c.customer_id
        LEFT JOIN invoice i ON vu.invoice_id = i.invoice_id
        WHERE vu.voucher_id = ?
        ORDER BY vu.used_at DESC
        LIMIT 100");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Format HTML
    ob_start();
    ?>
    <div class="mb-3">
        <h6>Voucher: <strong><?php echo h($voucher['code']); ?> - <?php echo h($voucher['name']); ?></strong></h6>
        <p class="text-muted">Tổng số lần sử dụng: <strong><?php echo count($usages); ?></strong></p>
    </div>
    
    <?php if (empty($usages)): ?>
        <div class="alert alert-info">Chưa có lịch sử sử dụng.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Khách hàng</th>
                        <th>Hóa đơn</th>
                        <th>Giá trị giảm</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usages as $usage): ?>
                        <tr>
                            <td><?php echo formatDate($usage['used_at'], true); ?></td>
                            <td>
                                <?php if ($usage['customer_name']): ?>
                                    <?php echo h($usage['customer_name']); ?><br>
                                    <small class="text-muted"><?php echo h($usage['customer_phone']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($usage['invoice_id']): ?>
                                    <a href="index.php?page=invoices-manager&action=view&id=<?php echo $usage['invoice_id']; ?>" target="_blank">
                                        #<?php echo $usage['invoice_id']; ?>
                                    </a>
                                    <br><small class="text-muted"><?php echo formatCurrency($usage['total_amount']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-success">-<?php echo formatCurrency($usage['discount_amount']); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php
    $html = ob_get_clean();
    
    echo json_encode(['success' => true, 'html' => $html]);
    exit;
}

// Lấy danh sách khách hàng đã được gán voucher
if ($action == 'assigned_customers' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $mysqli->prepare("SELECT vc.*, 
        c.full_name, c.phone, c.email,
        nv.ho_ten as assigned_by_name
        FROM voucher_customer vc
        LEFT JOIN customer c ON vc.customer_id = c.customer_id
        LEFT JOIN nhan_vien nv ON vc.assigned_by = nv.id_nhan_vien
        WHERE vc.voucher_id = ?
        ORDER BY vc.assigned_at DESC");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assigned = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    echo json_encode(['success' => true, 'data' => $assigned]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);









