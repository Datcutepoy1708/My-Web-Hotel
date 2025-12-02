<?php
session_start();
require_once '../includes/connect.php'; // Đường dẫn kết nối DB

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: /My-Web-Hotel/client/pages/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Kiểm tra form gửi bằng POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form (và loại bỏ ký tự đặc biệt để tránh lỗi)
    $username    = trim($_POST['full_name'] ?? '');
    $ngay_sinh    = trim($_POST['birth_day'] ?? '');
    $dia_chi      = trim($_POST['address'] ?? '');
    $gioi_tinh    = trim($_POST['gender'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $sdt          = trim($_POST['phone'] ?? '');

    // Kiểm tra các trường bắt buộc
    if (empty($username) || empty($email)) {
        header("Location: ../pages/profile.php?error=missing_fields");
        exit();
    }

    // Cập nhật thông tin người dùng
    $sql = "UPDATE customer 
            SET full_name = ?, date_of_birth = ?, address = ?, gender = ?, email = ?, phone = ? 
            WHERE customer_id = ?";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("Lỗi prepare: " . $mysqli->error);
    }

    $stmt->bind_param("ssssssi", $username, $ngay_sinh, $dia_chi, $gioi_tinh, $email, $sdt, $user_id);

    if ($stmt->execute()) {
        // Thành công → quay lại trang profile với thông báo
        header("Location: ../pages/profile.php?success=1");
        exit();
    } else {
        // Lỗi khi cập nhật
        header("Location: ../pages/profile.php?error=update_failed");
        exit();
    }
}
?>