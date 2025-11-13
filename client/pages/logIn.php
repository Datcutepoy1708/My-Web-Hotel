<?php
session_start();

// Cấu hình DB
require_once '../includes/connect.php';

// Khởi tạo biến error
$error = '';

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']);
    if ($email === '' || $password === '') {
        $error = 'Vui lòng nhập email và mật khẩu.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } else {
        $sql = 'SELECT id, username, email, password, full_name FROM users WHERE email = ? LIMIT 1';
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('s', $email);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result && $result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    $stored = $user['password'];
                    $loginOk = false;

                    // Thử verify hash trước
                    if (password_verify($password, $stored)) {
                        $loginOk = true;
                        // rehash nếu cần
                        if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            if ($newHash !== false) {
                                $upd = $mysqli->prepare('UPDATE users SET password = ? WHERE id = ?');
                                if ($upd) {
                                    $upd->bind_param('si', $newHash, $user['id']);
                                    $upd->execute();
                                    $upd->close();
                                }
                            }
                        }
                    } else {
                        // fallback plaintext (nếu DB đang lưu plaintext)
                        if ($password === $stored) {
                            $loginOk = true;
                            // migrate lên hash
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            if ($newHash !== false) {
                                $upd = $mysqli->prepare('UPDATE users SET password = ? WHERE id = ?');
                                if ($upd) {
                                    $upd->bind_param('si', $newHash, $user['id']);
                                    $upd->execute();
                                    $upd->close();
                                }
                            }
                        }
                    }

                    if ($loginOk) {
                        // set session
                        $_SESSION['user_id'] = $user['id'];
                        if(isset($user['full_name']) && trim($user['full_name']) !== '') {
                            $_SESSION['full_name'] = $user['full_name'];
                            $_SESSION['username'] = $user['full_name'];
                        } else {
                            $_SESSION['username'] = $user['username'];
                        }
                        $_SESSION['logged_in_at'] = time();

                        // Nếu người dùng chọn "Lưu phiên đăng nhập"
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', time() + (86400 * 7));

                        $stmtToken = $mysqli->prepare("INSERT INTO login_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                        if ($stmtToken) {
                            $stmtToken->bind_param("iss", $user['id'], $token, $expires);
                            $stmtToken->execute();
                            $stmtToken->close();

                            setcookie("remember_token", $token, time() + (86400 * 30), "/", "", true, true);
                        }
                    }

                    // xử lý redirect an toàn
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/My-Web-Hotel/client/index.php?page=home';
                    $parsed = parse_url($redirect);
                    if (isset($parsed['host']) && $parsed['host'] !== $_SERVER['SERVER_NAME']) {
                        $redirect = '/My-Web-Hotel/client/index.php?page=home';
                    }

                    header("Location: $redirect");
                    $stmt->close();
                    $mysqli->close();
                    exit;
                }else {
                    // THÊM DÒNG NÀY - xử lý khi mật khẩu sai
                    $error = 'Email hoặc mật khẩu không đúng.';
                }
                } else {
                    $error = 'Email hoặc mật khẩu không đúng.';
                }
            } else {
                error_log('Execute failed: ' . $stmt->error);
                $error = 'Có lỗi. Vui lòng thử lại.';
            }
            $stmt->close();
        } else {
            error_log('Prepare failed: ' . $mysqli->error);
            $error = 'Có lỗi. Vui lòng thử lại.';
        }
    }
}

// Đóng kết nối (nếu chưa đóng khi redirect)
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Page</title>
    <link rel="stylesheet" href="/My-Web-Hotel/client/assets/css/login.css?v=<?php echo time(); ?>" />
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
    <div class="login-box">
        <h2>Chào Mừng</h2>
        <?php if ($error !== ''): ?>
        <div class="error" style="color:red; margin-bottom:12px;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required autocomplete="email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
            </div>
            <div class="input-box">
                <input type="password" id="password" name="password" placeholder="Mật khẩu" required
                    autocomplete="current-password" />
                <span class="toggle-password" onclick="togglePassword()">
                    <i class="fa-solid fa-eye" id="eyeIcon"></i>
                </span>
            </div>
            <button type="submit" class="btn">Đăng Nhập</button>
            <label class="remember-login">
                Lưu thông tin đăng nhập
                <input type="checkbox" name="remember">
                <span class="checkmark"></span>
            </label>
            <div class="options">
                <a href="/my-web-hotel/client/pages/forgotPass.php">Quên mật khẩu ?</a>
                <a href="/my-web-hotel/client/pages/signup.php">Đăng ký</a>
            </div>
            <div class="social-login">
                <p>ĐĂNG NHẬP VỚI</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-google"></i></a>
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </form>
    </div>
    <script>
    function togglePassword() {
        const passwordInput = document.getElementById("password");
        const eyeIcon = document.getElementById("eyeIcon");

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            eyeIcon.classList.remove("fa-eye");
            eyeIcon.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            eyeIcon.classList.remove("fa-eye-slash");
            eyeIcon.classList.add("fa-eye");
        }
    }
    </script>
</body>

</html>