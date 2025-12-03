<?php
session_start();
require_once '../includes/connect.php'; // Đường dẫn kết nối DB
require_once '../../admin/includes/cloudinary_helper.php'; // Cloudinary helper

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

    // Xử lý upload avatar
    $avatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $tmpPath = $_FILES['avatar']['tmp_name'];
        $avatar = CloudinaryHelper::upload($tmpPath, 'customer');
        
        if ($avatar === false) {
            header("Location: ../pages/profile.php?error=upload_failed");
            exit();
        }
        
        // Xóa avatar cũ trên Cloudinary nếu có
        $stmtOld = $mysqli->prepare("SELECT avatar FROM customer WHERE customer_id = ?");
        $stmtOld->bind_param("i", $user_id);
        $stmtOld->execute();
        $resultOld = $stmtOld->get_result();
        if ($oldUser = $resultOld->fetch_assoc()) {
            if (!empty($oldUser['avatar'])) {
                CloudinaryHelper::deleteByUrl($oldUser['avatar']);
            }
        }
        $stmtOld->close();
    } else {
        // Giữ avatar cũ nếu không upload mới
        $stmtOld = $mysqli->prepare("SELECT avatar FROM customer WHERE customer_id = ?");
        $stmtOld->bind_param("i", $user_id);
        $stmtOld->execute();
        $resultOld = $stmtOld->get_result();
        if ($oldUser = $resultOld->fetch_assoc()) {
            $avatar = $oldUser['avatar'];
        }
        $stmtOld->close();
    }

    // Cập nhật thông tin người dùng
    if ($avatar !== null) {
        $sql = "UPDATE customer 
                SET full_name = ?, date_of_birth = ?, address = ?, gender = ?, email = ?, phone = ?, avatar = ? 
                WHERE customer_id = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            die("Lỗi prepare: " . $mysqli->error);
        }
        $stmt->bind_param("sssssssi", $username, $ngay_sinh, $dia_chi, $gioi_tinh, $email, $sdt, $avatar, $user_id);
    } else {
        $sql = "UPDATE customer 
                SET full_name = ?, date_of_birth = ?, address = ?, gender = ?, email = ?, phone = ? 
                WHERE customer_id = ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            die("Lỗi prepare: " . $mysqli->error);
        }
        $stmt->bind_param("ssssssi", $username, $ngay_sinh, $dia_chi, $gioi_tinh, $email, $sdt, $user_id);
    }

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