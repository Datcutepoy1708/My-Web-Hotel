<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quên mật khẩu - Quản lý khách sạn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
    :root {
        --primary: #deb666;
        --dark: #1e1e2f;
        --light: #ffffff;
    }

    body {
        margin: 0;
        padding: 0;
        background: url("https://images.unsplash.com/photo-1532274402911-5a369e4c4bb5?auto=format&fit=crop&q=60&w=900") no-repeat center center fixed;
        background-size: cover;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: "Poppins", sans-serif;
    }

    .forgot-card {
        background-color: rgba(30, 30, 47, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        width: 380px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        color: var(--light);
    }

    .forgot-card h2 {
        text-align: center;
        color: var(--primary);
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .forgot-card p {
        text-align: center;
        font-size: 0.95rem;
        color: #ddd;
        margin-bottom: 1.5rem;
    }

    .form-control {
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: #fff;
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 0.25);
        box-shadow: 0 0 0 0.2rem rgba(222, 182, 102, 0.3);
        color: #fff;
    }

    ::placeholder {
        color: #ddd;
    }

    .btn-primary {
        background-color: var(--primary);
        border: none;
        font-weight: 500;
        transition: 0.3s;
    }

    .btn-primary:hover {
        background-color: #cfa955;
        transform: translateY(-2px);
    }

    .forgot-footer {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.9rem;
        color: #ccc;
    }

    .forgot-footer a {
        color: var(--primary);
        text-decoration: none;
    }

    .forgot-footer a:hover {
        text-decoration: underline;
    }

    @media (max-width: 576px) {
        .forgot-card {
            width: 90%;
            padding: 1.5rem;
        }
    }
    </style>
</head>

<body>
    <div class="forgot-card">
        <h2>Khôi phục mật khẩu tài khoản</h2>
        <form>
            <div class="mb-3">
                <label for="email" class="form-label">Địa chỉ Email</label>
                <input type="email" class="form-control" id="email" placeholder="Nhập email của bạn" required />
            </div>
            <div class="mb-3">
                <label for="new-password" class="form-label">Mật khẩu mới</label>
                <input type="password" class="form-control" id="new-password" placeholder="Nhập mật khẩu mới"
                    required />
            </div>
            <div class="mb-3">
                <label for="confirm-password" class="form-label">Nhập lại mật khẩu mới</label>
                <input type="password" class="form-control" id="confirm-password" placeholder="Nhập lại mật khẩu mới"
                    required />
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">
                Khôi phục mật khẩu
            </button>
        </form>

        <div class="forgot-footer">
            <a href="/My-Web-Hotel/admin/pages/logIn.php">← Quay lại đăng nhập</a>
        </div>
    </div>
</body>

</html>