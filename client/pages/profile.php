<?php
session_start();
require_once '../includes/connect.php';
$isLoggedIn = isset($_SESSION['user_id']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header("Location: /My-Web-Hotel/client/pages/login.php");
    exit();
}

$stmt = $mysqli->prepare("SELECT * FROM customer WHERE customer_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$avatarPath = !empty($user['avatar']) ? $user['avatar'] : '/My-Web-Hotel/client/assets/images/275f99923b080b18e7b474ed6155a17f.jpg';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="/My-Web-Hotel/client/assets/images/favicon.png" type="image/x-icon" />
    <title>Profile - OceanPearl Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="/My-Web-Hotel/client/assets/css/profile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/My-Web-Hotel/client/assets/css/loading.css?v=<?php echo time(); ?>">
</head>

<body>
    <?php include '../includes/loading.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="profile-section">
                    <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Profile Avatar" class="profile-img"
                        id="sidebar-avatar" />
                    <div class="profile-name" id="username-display"><?= htmlspecialchars($user['full_name']) ?></div>
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
                    <button id="logout-btn" class="btn logout-btn">
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
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php elseif (isset($_GET['error'])): ?>
                                <?php 
                                    $error_messages = [
                                        '1' => 'Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.',
                                        'missing' => 'Vui lòng nhập đầy đủ thông tin bắt buộc!',
                                        'avatar' => 'Lỗi khi tải ảnh lên. Vui lòng thử lại!',
                                        'invalid_file_type' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF)!',
                                        'file_too_large' => 'Kích thước file quá lớn (tối đa 5MB)!'
                                    ];
                                    $error = $_GET['error'];
                                    $message = $error_messages[$error] ?? 'Có lỗi xảy ra!';
                                    ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fa fa-times-circle me-2"></i> <?= $message ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php endif; ?>

                                <form method="post" action="../controller/update-profile.php"
                                    enctype="multipart/form-data" id="profile-form">
                                    <!-- Avatar Upload Section -->
                                    <div class="avatar-upload-section">
                                        <div class="avatar-preview-container"
                                            onclick="document.getElementById('avatar-input').click()">
                                            <img src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar Preview"
                                                class="avatar-preview" id="avatar-preview">
                                            <div class="camera-overlay">
                                                <i class="fas fa-camera"></i>
                                            </div>
                                            <input type="file" id="avatar-input" name="avatar"
                                                accept="image/jpeg,image/png,image/jpg,image/gif"
                                                style="display: none;">
                                        </div>
                                        <div class="avatar-upload-controls">
                                            <h5><i class="fas fa-user-circle me-2"></i>Ảnh đại diện</h5>
                                            <p class="text-muted mb-2">Nhấn vào ảnh để thay đổi</p>
                                            <p class="text-muted small mb-3">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Định dạng: JPG, PNG, GIF | Tối đa: 5MB
                                            </p>
                                            <span class="file-name-display" id="file-name">
                                                <i class="fas fa-file-image me-1"></i>Chưa chọn file
                                            </span>
                                            <button type="button" class="btn-remove-avatar" id="remove-avatar"
                                                style="display:none;">
                                                <i class="fas fa-trash-alt"></i> Xóa ảnh đã chọn
                                            </button>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tên người dùng <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="full_name"
                                                placeholder="Nhập họ và tên"
                                                value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Ngày sinh</label>
                                            <input type="date" class="form-control" name="birth_day"
                                                value="<?= !empty($user['date_of_birth']) ? date('Y-m-d', strtotime($user['date_of_birth'])) : '' ?>">
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
                                                    Nam</option>
                                                <option value="Female"
                                                    <?= $user['gender'] == 'Female' ? 'selected' : '' ?>>Nữ</option>
                                                <option value="Other"
                                                    <?= $user['gender'] == 'Other' ? 'selected' : '' ?>>Khác</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control"
                                                placeholder="example@email.com"
                                                value="<?= htmlspecialchars($user['email']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Số điện thoại</label>
                                            <input type="tel" class="form-control" name="phone"
                                                placeholder="Nhập số điện thoại"
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
                                    </div>
                                </form>

                                <button class="btn btn-home" onclick="goHome()">
                                    <i class="fa fa-arrow-left me-2"></i>Quay về trang chủ
                                </button>
                            </div>
                        </div>

                        <!-- Change Password Tab -->
                        <div class="tab-pane fade" id="changePassword" role="tabpanel">
                            <div class="content-card">
                                <h2 class="content-title">Đổi mật khẩu</h2>

                                <?php if (isset($_GET['pw_success'])): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fa fa-check-circle me-2"></i> Mật khẩu đã được thay đổi thành công!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php elseif (isset($_GET['pw_error'])): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fa fa-times-circle me-2"></i> <?= htmlspecialchars($_GET['pw_error']) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php endif; ?>

                                <form method="post" action="../controller/update-password.php">
                                    <div class="row mb-4">
                                        <div class="col-md-6 position-relative">
                                            <label class="form-label">Mật khẩu hiện tại <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" class="form-control password-field"
                                                name="current_password" placeholder="Nhập mật khẩu cũ" required>
                                            <i class="fa fa-eye toggle-password"
                                                style="position:absolute; right:25px; top:43px; cursor:pointer;"></i>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6 position-relative">
                                            <label class="form-label">Mật khẩu mới <span
                                                    class="text-danger">*</span></label>
                                            <input type="password" class="form-control password-field"
                                                name="new_password" placeholder="Nhập mật khẩu mới" required>
                                            <i class="fa fa-eye toggle-password"
                                                style="position:absolute; right:25px; top:43px; cursor:pointer;"></i>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6 position-relative">
                                            <label class="form-label">Xác nhận mật khẩu mới <span
                                                    class="text-danger">*</span></label>
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
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteRoom(102)">
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
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteRoom(103)">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/My-Web-Hotel/client/assets/js/loading.js?v=<?php echo time(); ?>"></script>
    <script>
    window.IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
    window.CURRENT_USER_NAME = <?= $username ? json_encode($username) : 'null' ?>;

    // Check URL parameters and switch to correct tab
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');

        if (tab) {
            // Remove active class from all tabs and panes
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });

            // Activate the target tab
            const targetTab = document.querySelector(`a[href="#${tab}"]`);
            const targetPane = document.querySelector(`#${tab}`);

            if (targetTab && targetPane) {
                targetTab.classList.add('active');
                targetPane.classList.add('show', 'active');
            }
        }

        // Auto hide alerts after 3 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 3000);
        });
    });

    // Avatar Upload Preview
    const avatarInput = document.getElementById('avatar-input');
    const avatarPreview = document.getElementById('avatar-preview');
    const sidebarAvatar = document.getElementById('sidebar-avatar');
    const fileNameDisplay = document.getElementById('file-name');
    const removeAvatarBtn = document.getElementById('remove-avatar');

    avatarInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Chỉ chấp nhận file ảnh (JPG, PNG, GIF)!');
                avatarInput.value = '';
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Kích thước file quá lớn (tối đa 5MB)!');
                avatarInput.value = '';
                return;
            }

            // Preview image
            const reader = new FileReader();
            reader.onload = function(event) {
                avatarPreview.src = event.target.result;
                sidebarAvatar.src = event.target.result;
            };
            reader.readAsDataURL(file);

            fileNameDisplay.textContent = file.name;
            removeAvatarBtn.style.display = 'inline-block';
        }
    });

    removeAvatarBtn.addEventListener('click', function() {
        avatarInput.value = '';
        avatarPreview.src = '<?= htmlspecialchars($avatarPath) ?>';
        sidebarAvatar.src = '<?= htmlspecialchars($avatarPath) ?>';
        fileNameDisplay.textContent = 'Chưa chọn file';
        removeAvatarBtn.style.display = 'none';
    });

    // Toggle Password Visibility
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    });

    // Logout functionality
    const logoutBtn = document.getElementById('logout-btn');
    const confirmLogout = document.getElementById('confirmLogout');
    const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));

    logoutBtn.addEventListener('click', function() {
        logoutModal.show();
    });

    confirmLogout.addEventListener('click', function() {
        window.location.href = '../controller/logout.php';
    });

    // Go Home function
    function goHome() {
        window.location.href = '/My-Web-Hotel/client/index.php';
    }

    // Delete Room function
    function deleteRoom(id) {
        if (confirm('Bạn có chắc chắn muốn xóa đặt phòng này?')) {
            console.log('Deleting room booking:', id);
            // Add your delete logic here
        }
    }

    // View Room function
    function viewRoom(id) {
        console.log('Viewing room booking:', id);
        // Add your view logic here
    }
    </script>
</body>

</html>