<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register Page</title>
    <link rel="stylesheet" href="/my-web-hotel/client/assets/css/signUp.css?v=<?php echo time(); ?>" />
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
    <div class="login-box">
        <h2>Tạo Tài Khoản</h2>
        <form>
            <div class="input-box">
                <input type="text" placeholder="Họ và Tên" required />
            </div>
            <div class="input-box">
                <input type="email" placeholder="Email" required />
            </div>
            <div class="input-box">
                <input type="password" id="password" placeholder="Mật khẩu" required />
                <span class="toggle-password" onclick="togglePassword('password', 'eyeIcon1')">
                    <i class="fa-solid fa-eye" id="eyeIcon1"></i>
                </span>
            </div>
            <div class="input-box">
                <input type="password" id="confirmPassword" placeholder="Nhập lại mật khẩu" required />
                <span class="toggle-password" onclick="togglePassword('confirmPassword', 'eyeIcon2')">
                    <i class="fa-solid fa-eye" id="eyeIcon2"></i>
                </span>
            </div>
            <button type="submit" class="btn">Đăng Ký</button>
            <div class="options">
                <p style="color: #fff;">Bạn đã có tài khoản?</p>
                <a href="/my-web-hotel/pages/login.php">Đăng nhập ngay</a>
            </div>
            <div class="social-login">
                <p>ĐĂNG KÝ VỚI</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-google"></i></a>
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </form>
    </div>
    <script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
    </script>
</body>

</html>