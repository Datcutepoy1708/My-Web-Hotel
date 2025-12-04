<?php
session_start();
require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/email_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? ''; // 'room' hoặc 'service'
    
    if ($type === 'room') {
        // Xử lý booking phòng
        $customer_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
        $room_id = intval($_POST['room_id'] ?? 0);
        $check_in_date = trim($_POST['check_in_date'] ?? '');
        $check_out_date = trim($_POST['check_out_date'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 1);
        $special_request = trim($_POST['special_request'] ?? '');
        $booking_method = 'Website';
        $deposit = floatval($_POST['deposit'] ?? 0);
        
        if (empty($check_in_date) || empty($check_out_date) || $room_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
            exit;
        }
        
        // Lấy thông tin phòng
        $roomStmt = $mysqli->prepare("SELECT r.room_id, r.room_number, rt.room_type_name 
                                      FROM room r 
                                      JOIN room_type rt ON r.room_type_id = rt.room_type_id 
                                      WHERE r.room_id = ?");
        $roomStmt->bind_param("i", $room_id);
        $roomStmt->execute();
        $roomResult = $roomStmt->get_result();
        $room = $roomResult->fetch_assoc();
        $roomStmt->close();
        
        if (!$room) {
            echo json_encode(['success' => false, 'message' => 'Phòng không tồn tại']);
            exit;
        }
        
        // Lấy thông tin khách hàng
        $customer = null;
        if ($customer_id > 0) {
            $customerStmt = $mysqli->prepare("SELECT customer_id, full_name, email FROM customer WHERE customer_id = ?");
            $customerStmt->bind_param("i", $customer_id);
            $customerStmt->execute();
            $customerResult = $customerStmt->get_result();
            $customer = $customerResult->fetch_assoc();
            $customerStmt->close();
        } else {
            // Nếu chưa đăng nhập, lấy từ form
            $customer_email = trim($_POST['customer_email'] ?? '');
            $customer_name = trim($_POST['customer_name'] ?? '');
            if (!empty($customer_email)) {
                $customer = ['email' => $customer_email, 'full_name' => $customer_name];
            }
        }
        
        // Insert booking
        $bookingDate = date('Y-m-d H:i:s');
        $insertStmt = $mysqli->prepare("INSERT INTO booking (booking_date, check_in_date, check_out_date, quantity, special_request, booking_method, deposit, status, customer_id, room_id, created_at) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?, NOW())");
        $insertStmt->bind_param("sssisssii", $bookingDate, $check_in_date, $check_out_date, $quantity, $special_request, $booking_method, $deposit, $customer_id, $room_id);
        
        if ($insertStmt->execute()) {
            $booking_id = $mysqli->insert_id;
            $insertStmt->close();
            
            // Gửi email xác nhận
            if ($customer && !empty($customer['email'])) {
                $bookingData = [
                    'booking_id' => $booking_id,
                    'room_number' => $room['room_number'],
                    'check_in_date' => $check_in_date,
                    'check_out_date' => $check_out_date,
                    'quantity' => $quantity,
                    'booking_date' => $bookingDate
                ];
                
                EmailHelper::sendRoomBookingConfirmation(
                    $customer['email'],
                    $customer['full_name'] ?? 'Khách hàng',
                    $bookingData
                );
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Đặt phòng thành công! Email xác nhận đã được gửi.',
                'booking_id' => $booking_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi đặt phòng: ' . $insertStmt->error]);
            $insertStmt->close();
        }
        exit;
    }
    
    if ($type === 'service') {
        // Xử lý booking dịch vụ
        $service_id = intval($_POST['service_id'] ?? 0);
        $customer_name = trim($_POST['customer_name'] ?? '');
        $customer_phone = trim($_POST['customer_phone'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $booking_date = trim($_POST['booking_date'] ?? '');
        $booking_time = trim($_POST['booking_time'] ?? '');
        $number_of_people = intval($_POST['number_of_people'] ?? 1);
        $special_request = trim($_POST['special_request'] ?? '');
        
        if (empty($customer_name) || empty($customer_phone) || empty($customer_email) || 
            empty($booking_date) || empty($booking_time) || $service_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
            exit;
        }
        
        // Lấy thông tin dịch vụ
        $serviceStmt = $mysqli->prepare("SELECT service_id, service_name, price FROM service WHERE service_id = ? AND deleted IS NULL");
        $serviceStmt->bind_param("i", $service_id);
        $serviceStmt->execute();
        $serviceResult = $serviceStmt->get_result();
        $service = $serviceResult->fetch_assoc();
        $serviceStmt->close();
        
        if (!$service) {
            echo json_encode(['success' => false, 'message' => 'Dịch vụ không tồn tại']);
            exit;
        }
        
        // Tìm hoặc tạo customer
        $customer_id = 0;
        $customerStmt = $mysqli->prepare("SELECT customer_id FROM customer WHERE email = ? AND deleted IS NULL LIMIT 1");
        $customerStmt->bind_param("s", $customer_email);
        $customerStmt->execute();
        $customerResult = $customerStmt->get_result();
        $existingCustomer = $customerResult->fetch_assoc();
        $customerStmt->close();
        
        if ($existingCustomer) {
            $customer_id = $existingCustomer['customer_id'];
        }
        
        // Insert booking service (giả sử có bảng booking_service với các trường này)
        // Nếu chưa có booking_id, cần tạo booking trước hoặc lưu trực tiếp vào booking_service
        $bookingServiceDate = date('Y-m-d H:i:s');
        $unitPrice = $service['price'];
        
        // Giả sử có bảng booking_service với cấu trúc: booking_service_id, booking_id, service_id, quantity, unit_price
        // Nếu chưa có booking, tạo booking trước
        if ($customer_id > 0) {
            // Tạo booking tạm (có thể cần điều chỉnh theo schema thực tế)
            $bookingStmt = $mysqli->prepare("INSERT INTO booking (booking_date, check_in_date, check_out_date, quantity, customer_id, room_id, status, created_at) 
                                            VALUES (?, ?, ?, 1, ?, 0, 'Pending', NOW())");
            $tempCheckIn = $booking_date;
            $tempCheckOut = $booking_date;
            $bookingStmt->bind_param("sssi", $bookingServiceDate, $tempCheckIn, $tempCheckOut, $customer_id);
            $bookingStmt->execute();
            $booking_id = $mysqli->insert_id;
            $bookingStmt->close();
        } else {
            $booking_id = 0; // Hoặc tạo booking với customer_id = 0
        }
        
        // Insert vào booking_service
        $insertStmt = $mysqli->prepare("INSERT INTO booking_service (booking_id, service_id, quantity, unit_price, created_at) 
                                       VALUES (?, ?, ?, ?, NOW())");
        $insertStmt->bind_param("iiid", $booking_id, $service_id, $number_of_people, $unitPrice);
        
        if ($insertStmt->execute()) {
            $booking_service_id = $mysqli->insert_id;
            $insertStmt->close();
            
            // Gửi email xác nhận
            $bookingData = [
                'booking_service_id' => $booking_service_id,
                'service_name' => $service['service_name'],
                'quantity' => $number_of_people,
                'unit_price' => $unitPrice,
                'booking_date' => $bookingServiceDate
            ];
            
            EmailHelper::sendServiceBookingConfirmation(
                $customer_email,
                $customer_name,
                $bookingData
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Đặt dịch vụ thành công! Email xác nhận đã được gửi.',
                'booking_service_id' => $booking_service_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi đặt dịch vụ: ' . $insertStmt->error]);
            $insertStmt->close();
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>


