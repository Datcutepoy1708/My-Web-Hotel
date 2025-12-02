<?php
session_start();
header('Content-Type: application/json');

require_once '../includes/connect.php';

// Google Client ID - Phải giống với frontend
define('GOOGLE_CLIENT_ID', '868208581571-lih6bdqrvbj0b4a7oqbtq9bnnf4lhiep.apps.googleusercontent.com');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['credential'])) {
    echo json_encode(['success' => false, 'message' => 'Missing credential']);
    exit;
}

$credential = $_POST['credential'];

try {
    // Xác thực ID Token với Google
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $credential;
    $response = file_get_contents($url);
    
    if ($response === false) {
        throw new Exception('Không thể xác thực với Google');
    }
    
    $payload = json_decode($response, true);
    
    // Kiểm tra tính hợp lệ
    if (!isset($payload['email']) || $payload['aud'] !== GOOGLE_CLIENT_ID) {
        throw new Exception('Token không hợp lệ');
    }
    
    // Lấy thông tin người dùng
    $google_id = $payload['sub'];
    $email = $payload['email'];
    $name = isset($payload['name']) ? $payload['name'] : '';
    $picture = isset($payload['picture']) ? $payload['picture'] : '';
    $email_verified = isset($payload['email_verified']) ? $payload['email_verified'] : false;
    
    // Chỉ cho phép email đã xác thực
    if (!$email_verified) {
        throw new Exception('Email chưa được xác thực');
    }
    
    // Kiểm tra xem email đã tồn tại trong database chưa
    $stmt = $mysqli->prepare("SELECT id, username, username, google_id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User đã tồn tại
        $user = $result->fetch_assoc();
        
        // Cập nhật google_id nếu chưa có
        if (empty($user['google_id'])) {
            $updateStmt = $mysqli->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $updateStmt->bind_param('si', $google_id, $user['id']);
            $updateStmt->execute();
            $updateStmt->close();
        }
        
        $user_id = $user['id'];
        $username = !empty($user['username']) ? $user['username'] : $user['username'];
        
    } else {
        // Tạo user mới
        $username = !empty($name) ? $name : explode('@', $email)[0];
        
        // Tạo password ngẫu nhiên (không sử dụng cho Google login)
        $random_password = bin2hex(random_bytes(16));
        $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
        
        $insertStmt = $mysqli->prepare("INSERT INTO users (username, email, password, username, google_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $insertStmt->bind_param('sssss', $username, $email, $hashed_password, $name, $google_id);
        
        if ($insertStmt->execute()) {
            $user_id = $insertStmt->insert_id;
        } else {
            throw new Exception("Không thể tạo tài khoản: " . $insertStmt->error);
        }
        $insertStmt->close();
    }
    
    $stmt->close();
    
    // Lưu thông tin vào session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['username'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['logged_in_at'] = time();
    $_SESSION['login_method'] = 'google';
    
    // Tạo remember token
    $remember_token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + (86400 * 30));
    
    $tokenStmt = $mysqli->prepare("INSERT INTO login_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    if ($tokenStmt) {
        $tokenStmt->bind_param("iss", $user_id, $remember_token, $expires);
        $tokenStmt->execute();
        $tokenStmt->close();
        
        setcookie("remember_token", $remember_token, time() + (86400 * 30), "/", "", true, true);
    }
    
    $mysqli->close();
    
    // Trả về thành công
    echo json_encode([
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'redirect' => '/My-Web-Hotel/client/index.php?page=home'
    ]);
    
} catch (Exception $e) {
    error_log('Google Login Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>