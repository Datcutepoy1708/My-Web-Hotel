<?php
session_start();
require_once '../includes/connect.php';
require_once '../../admin/includes/cloudinary_helper.php';

// Kiểm tra người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: /My-Web-Hotel/client/pages/login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

// Kiểm tra form gửi bằng POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    
    // Validation
    if ($rating < 1 || $rating > 5) {
        header("Location: ../pages/danhGia.php?error=invalid_rating");
        exit();
    }
    
    if (empty($comment)) {
        header("Location: ../pages/danhGia.php?error=empty_comment");
        exit();
    }
    
    // Insert review vào database
    $sql = "INSERT INTO review (customer_id, rating, comment, status, created_at) 
            VALUES (?, ?, ?, 'Approved', NOW())";
    
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        error_log("Error preparing review insert: " . $mysqli->error);
        header("Location: ../pages/danhGia.php?error=db_error");
        exit();
    }
    
    $stmt->bind_param("iis", $customer_id, $rating, $comment);
    
    if (!$stmt->execute()) {
        error_log("Error executing review insert: " . $stmt->error);
        $stmt->close();
        header("Location: ../pages/danhGia.php?error=db_error");
        exit();
    }
    
    $review_id = $mysqli->insert_id;
    $stmt->close();
    
    // Xử lý upload nhiều ảnh
    if (isset($_FILES['review_images']) && !empty($_FILES['review_images']['name'][0])) {
        $uploadedImages = [];
        $files = $_FILES['review_images'];
        $fileCount = count($files['name']);
        
        // Giới hạn tối đa 6 ảnh
        $maxFiles = min($fileCount, 6);
        
        for ($i = 0; $i < $maxFiles; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmpPath = $files['tmp_name'][$i];
                
                // Upload lên Cloudinary
                $imageUrl = CloudinaryHelper::upload($tmpPath, 'review');
                
                if ($imageUrl !== false) {
                    $uploadedImages[] = $imageUrl;
                }
            }
        }
        
        // Lưu các ảnh vào bảng review_images
        if (!empty($uploadedImages)) {
            $insertImageSql = "INSERT INTO review_images (review_id, image_url, display_order, created_at) VALUES (?, ?, ?, NOW())";
            $imageStmt = $mysqli->prepare($insertImageSql);
            
            if ($imageStmt) {
                foreach ($uploadedImages as $index => $imageUrl) {
                    $displayOrder = $index;
                    $imageStmt->bind_param("isi", $review_id, $imageUrl, $displayOrder);
                    $imageStmt->execute();
                }
                $imageStmt->close();
            } else {
                error_log("Error preparing review_images insert: " . $mysqli->error);
            }
        }
    }
    
    // Thành công
    header("Location: ../index.php?page=danhGia");
    exit();
} else {
    header("Location: ../pages/danhGia.php");
    exit();
}
?>

