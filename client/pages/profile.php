<?php
session_start();
<<<<<<< HEAD
=======
require_once '../includes/connect.php';
>>>>>>> main
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
<<<<<<< HEAD
?>

<!DOCTYPE html>
<html lang="en">
=======
// Lấy dữ liệu người dùng từ DB
$stmt = $mysqli->prepare("SELECT * FROM customer WHERE customer_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="vi">
>>>>>>> main

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="/My-Web-Hotel/client/assets/images/favicon.png" type="image/x-icon" />
<<<<<<< HEAD
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
=======
    <title>Profile - OceanPearl Hotel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="/My-Web-Hotel/client/assets/css/profile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/My-Web-Hotel/client/assets/css/loading.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php
    include '../includes/loading.php';
    ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="profile-section">
                    <img src="/My-Web-Hotel/client/assets/images/275f99923b080b18e7b474ed6155a17f.jpg"
                        alt="Profile Avatar" class="profile-img" />
                    <div class="profile-name" id="username-display">Người dùng</div>
                </div>

                <nav class="nav flex-column nav-pills px-3 mt-3" role="tablist">
                    <a class="nav-link active" data-bs-toggle="pill" href="#personal" role="tab">
                        <i class="fa fa-id-card"></i> Thông tin cá nhân
                    </a>
                    <a class="nav-link" data-bs-toggle="pill" href="#changePassword" role="tab">
                        <i class="fa fa-key"></i> Đổi mật khẩu
                    </a>
                    <a class="nav-link" data-bs-toggle="pill" href="#history" role="tab">
                        <i class="fa fa-history"></i> Lịch sử đặt phòng
                    </a>
                </nav>

                <div class="logout-section">
                    <div class="username-label fw-semibold mb-2" id="sidebar-username"></div>
                    <button id="logout-btn" class="btn logout-btn" style="display:none;">
                        <i class="fa fa-sign-out-alt"></i> Đăng xuất
                    </button>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="content-area">
                    <div class="tab-content">
                        <!-- Personal Info Tab -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <div class="content-card">
                                <h2 class="content-title">Thông tin cá nhân</h2>
                                <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fa fa-check-circle me-2"></i> Cập nhật thông tin thành công!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Đóng"></button>
                                </div>
                                <?php elseif (isset($_GET['error']) && $_GET['error'] == '1'): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fa fa-times-circle me-2"></i> Có lỗi xảy ra khi cập nhật. Vui lòng thử
                                    lại.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Đóng"></button>
                                </div>
                                <?php elseif (isset($_GET['error']) && $_GET['error'] == 'missing'): ?>
                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <i class="fa fa-exclamation-circle me-2"></i> Vui lòng nhập đầy đủ thông tin bắt
                                    buộc!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Đóng"></button>
                                </div>
                                <?php endif; ?>
                                <form method="post" action="../controller/update-profile.php">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tên người dùng</label>
                                            <input type="text" class="form-control" name="full_name"
                                                placeholder="Nhập họ và tên"
                                                value="<?= htmlspecialchars($user['full_name']) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Ngày sinh</label>
                                            <input type="date" class="form-control" name="birth_day"
                                                value="<?=date('Y-m-d', strtotime($user['date_of_birth']))?>">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Địa chỉ</label>
                                            <input type="text" class="form-control" name="address"
                                                placeholder="Nhập địa chỉ đầy đủ"
                                                value="<?= htmlspecialchars($user['address']) ?>">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Giới tính</label>
                                            <select class="form-select" name="gender">
                                                <option value="">Chọn giới tính</option>
                                                <option value="Male" <?= $user['gender'] == 'Male' ? 'selected' : '' ?>>
                                                    Nam
                                                </option>
                                                <option value="Female"
                                                    <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>
                                                    Nữ</option>
                                                <option value="Other"
                                                    <?= $user['gender'] == 'Other' ? 'selected' : '' ?>>Khác</option>
                                            </select>
                                        </div>

                                    </div>

                                    <div class="row mb-5">
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control"
                                                value="<?= htmlspecialchars($user['email']) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Số điện thoại</label>
                                            <input type="tel" class="form-control" name="phone" placeholder="Nhập sđt"
                                                value="<?= htmlspecialchars($user['phone']) ?>">
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-save">
                                            <i class="fa fa-save me-2"></i>Cập nhật
                                        </button>
                                        <button type="reset" class="btn btn-cancel">
                                            <i class="fa fa-times me-2"></i>Hủy
                                        </button>
                                </form>
                            </div>
                            <button class="btn btn-home" onclick="goHome()">
                                <i class="fa fa-arrow-left me-2"></i>Quay về trang chủ
                            </button>
                        </div>
                    </div>

                    <!-- change pass tab -->
                    <div class="tab-pane fade" id="changePassword" role="tabpanel">
                        <div class="content-card">
                            <h2 class="content-title">Đổi mật khẩu</h2>

                            <?php if (isset($_GET['pw_success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fa fa-check-circle me-2"></i> Mật khẩu đã được thay đổi thành công!
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Đóng"></button>
                            </div>
                            <?php elseif (isset($_GET['pw_error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa fa-times-circle me-2"></i> <?= htmlspecialchars($_GET['pw_error']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Đóng"></button>
                            </div>
                            <?php endif; ?>

                            <form method="post" action="../controller/update-password.php">
                                <div class="row mb-4">
                                    <div class="col-md-6 position-relative">
                                        <label class="form-label">Mật khẩu hiện tại</label>
                                        <input type="password" class="form-control password-field"
                                            name="current_password" placeholder="Nhập mật khẩu cũ" required>
                                        <i class="fa fa-eye toggle-password"
                                            style="position:absolute; right:25px; top:43px; cursor:pointer;"></i>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6 position-relative">
                                        <label class="form-label">Mật khẩu mới</label>
                                        <input type="password" class="form-control password-field" name="new_password"
                                            placeholder="Nhập mật khẩu mới" required>
                                        <i class="fa fa-eye toggle-password"
                                            style="position:absolute; right:25px; top:43px; cursor:pointer;"></i>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6 position-relative">
                                        <label class="form-label">Xác nhận mật khẩu mới</label>
                                        <input type="password" class="form-control password-field"
                                            name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
                                        <i class="fa fa-eye toggle-password"
                                            style="position:absolute; right:25px; top:43px; cursor:pointer;"></i>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-save">
                                        <i class="fa fa-key me-2"></i>Lưu thay đổi
                                    </button>
                                    <button type="reset" class="btn btn-cancel">
                                        <i class="fa fa-times me-2"></i>Hủy
                                    </button>
                                </div>
                            </form>


                            <button class="btn btn-home mt-4" onclick="goHome()">
                                <i class="fa fa-arrow-left me-2"></i>Quay về trang chủ
                            </button>
                        </div>
                    </div>

                    <!-- History Tab -->
                    <div class="tab-pane fade" id="history" role="tabpanel">
                        <div class="content-card">
                            <h2 class="content-title">Lịch sử đặt phòng</h2>
                            <div class="table-container">
                                <table class="table table-hover align-middle" id="tableRoomInvoice">
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
                                            <td><span class="badge bg-warning text-dark">Chờ xác nhận</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteRoom(102)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" onclick="viewRoom(102)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>HDR002</strong></td>
                                            <td>Phòng B205</td>
                                            <td>20/10/2025</td>
                                            <td>22/10/2025</td>
                                            <td>2,000,000 VNĐ</td>
                                            <td><span class="badge bg-success">Đã xác nhận</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteRoom(103)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" onclick="viewRoom(103)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <button class="btn btn-home" onclick="goHome()">
                                <i class="fa fa-arrow-left me-2"></i>Quay về trang chủ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Xác nhận đăng xuất</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Bạn có chắc chắn muốn đăng xuất không?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="confirmLogout">
                        <i class="fa fa-sign-out-alt me-2"></i>Đăng xuất
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/My-Web-Hotel/client/assets/js/profile.js"></script>
    <script src="/My-Web-Hotel/client/assets/js/loading.js?v=<?php echo time(); ?>"></script>
>>>>>>> main
    <script>
    // Gán biến từ PHP sang JS
    window.IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
    window.CURRENT_USER_NAME = <?= $username ? json_encode($username) : 'null' ?>;
    </script>
</body>

</html>