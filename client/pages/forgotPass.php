<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password</title>
    <link rel="stylesheet" href="/my-web-hotel/client/assets/css/forgotPass.css?v=<?php echo time(); ?>" />
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
    <div class="login-box">
        <h2>Quên Mật Khẩu</h2>
        <form>
            <div class="input-box">
                <input type="email" placeholder="Nhập email của bạn" required />
            </div>
            <button type="submit" class="btn">Khôi Phục Mật Khẩu</button>
            <div class="options">
                <a href="/my-web-hotel/pages/login.php">Quay lại đăng nhập</a>
                <a href="/my-web-hotel/pages/signup.php">Đăng ký</a>
            </div>
            <div class="social-login">
                <p>HOẶC KHÔI PHỤC BẰNG</p>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-google"></i></a>
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </form>
    </div>
</body>

</html>