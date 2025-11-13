<?php
session_start();
// Kiểm tra trạng thái đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
// Ngăn trình duyệt lưu cache
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Nếu chưa đăng nhập, chuyển hướng về trang đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: /My-Web-Hotel/client/pages/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="/My-Web-Hotel/client/assets/images/favicon.png" type="image/x-icon" />
    <title>Profile</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="/my-web-hotel/client/assets/css/profile.css?v=<?php echo time(); ?>" />
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="profile">
                <img src="/My-Web-Hotel/client/assets/images/275f99923b080b18e7b474ed6155a17f.jpg" alt="Default Avatar"
                    class="profile-img" />
                <span id="username-display">Người dùng</span>
            </div>

            <ul class="menu">
                <li class="active" onclick="showPage('personal')">
                    <i class="fa fa-id-card"></i> Thông tin cá nhân
                </li>
                <li onclick="showPage('inbox')">
                    <i class="fa fa-envelope"></i> Tin nhắn
                </li>
                <li onclick="showPage('history')">
                    <i class="fa fa-history"></i> Lịch sử đặt phòng
                </li>
            </ul>
            <!-- đăng xuất -->
            <div class="logout-section">
                <span class="username-label" id="sidebar-username"></span>
                <button id="logout-btn" class="logout-btn" style="display:none;">
                    <i class="fa fa-sign-out-alt"></i> Đăng xuất
                </button>
            </div>
        </aside>
        <!-- Popup thông báo -->
        <div id="logout-popup" class="popup">
            <div class="popup-content">
                <p>Bạn có chắc chắn muốn đăng xuất không?</p>
                <div class="popup-actions">
                    <button id="enter-logout-btn">Đăng xuất</button>
                    <button id="close-popup">Đóng</button>
                </div>
            </div>
        </div>
        <!-- Main Content -->
        <main class="content">
            <!-- Personal Info -->
            <div id="personal" class="page active">
                <div class="form-container">
                    <h2>Thông tin cá nhân</h2>
                    <form>
                        <div class="form-row">
                            <input type="text" placeholder="Họ" />
                            <input type="text" placeholder="Tên" />
                        </div>
                        <div class="form-row">
                            <input type="date" placeholder="Ngày sinh" />
                            <select>
                                <option>Giới tính</option>
                                <option>Nam</option>
                                <option>Nữ</option>
                                <option>Khác</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <input type="email" placeholder="Email" />
                            <input type="text" placeholder="Số điện thoại" />
                        </div>
                        <div class="form-row">
                            <input type="text" placeholder="Địa chỉ" />
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-save">Cập nhật</button>
                            <button type="reset" class="btn btn-cancel">Hủy</button>
                        </div>
                    </form>
                    <button class="btn btn-home" onclick="goHome()">⬅ Quay về trang chủ</button>
                </div>
            </div>

            <!-- Inbox -->
            <div id="inbox" class="page">
                <div class="form-container">
                    <h2>Hộp thư đến</h2>
                    <div class="inbox-empty">
                        <i class="fa fa-envelope-open-text"></i>
                        <p>Hiện tại bạn chưa có tin nhắn nào.</p>
                    </div>
                    <button class="btn btn-home" onclick="goHome()">⬅ Quay về trang chủ</button>
                </div>
            </div>

            <!-- Booking History -->
            <div id="history" class="page">
                <div class="form-container">
                    <h2>Lịch sử đặt phòng</h2>
                    <div class="history-empty">
                        <!-- <i class="fa fa-calendar-times"></i>
                        <p>Bạn chưa có lịch sử đặt phòng nào.</p> -->
                        <div class="table-container">
                            <table class="table table-hover" id="tableRoomInvoice">
                                <thead>
                                    <tr>
                                        <th>Mã HĐ</th>
                                        <th>Phòng</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Tổng Tiền</th>
                                        <th>Tình Trạng</th>
                                        <th>Hành Động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>HDR001</strong></td>
                                        <td>Phòng A101</td>
                                        <td>15/10/2025</td>
                                        <td>18/10/2025</td>
                                        <td>1,500,000 VNĐ</td>
                                        <td><span class="badge bg-waning">chờ xác nhận</span></td>
                                        <td> <button class="btn btn-sm btn-outline-danger" onclick="deleteRoom(102)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info" onclick="viewRoom(102)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <button class="btn btn-home" onclick="goHome()">⬅ Quay về trang chủ</button>
                </div>

            </div>
    </div>
    </main>
    </div>
    <script src="/My-Web-Hotel/client/assets/js/profile.js"></script>
    <script>
    // Gán biến từ PHP sang JS
    window.IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
    window.CURRENT_USER_NAME = <?= $username ? json_encode($username) : 'null' ?>;
    </script>
</body>

</html>