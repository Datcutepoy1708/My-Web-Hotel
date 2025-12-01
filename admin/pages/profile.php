<?php
// Lấy message từ session nếu có
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$messageType = isset($_SESSION['messageType']) ? $_SESSION['messageType'] : '';
unset($_SESSION['message']);
unset($_SESSION['messageType']);

// Lấy thông tin nhân viên hiện tại
$id_nhan_vien = $_SESSION['id_nhan_vien'];
$stmt = $mysqli->prepare("SELECT * FROM nhan_vien WHERE id_nhan_vien = ?");
$stmt->bind_param("i", $id_nhan_vien);
$stmt->execute();
$result = $stmt->get_result();
$nhanVien = $result->fetch_assoc();
$stmt->close();

// Cập nhật thông tin cá nhân
// Chỉ xử lý nếu không được gọi từ ProfileController (ProfileController đã xử lý POST)
$processedByController = isset($_SESSION['message']) && isset($_SESSION['messageType']);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile']) && !$processedByController) {
    $ho_ten = trim($_POST['ho_ten']);
    $dien_thoai = trim($_POST['dien_thoai'] ?? '');
    $ngay_sinh = !empty($_POST['ngay_sinh']) ? $_POST['ngay_sinh'] : null;
    $gioi_tinh = $_POST['gioi_tinh'] ?? 'Nam';
    $cmnd_cccd = trim($_POST['cmnd_cccd'] ?? '');
    $dia_chi = trim($_POST['dia_chi'] ?? '');
    
    // Upload ảnh nếu có
    $anh_dai_dien = $nhanVien['anh_dai_dien'] ?? '';
    $uploadError = '';
    
    if (isset($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/staff/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Kiểm tra lỗi upload
        if ($_FILES['anh_dai_dien']['error'] != UPLOAD_ERR_OK) {
            $uploadError = 'Lỗi upload: ' . $_FILES['anh_dai_dien']['error'];
        } elseif (!in_array($_FILES['anh_dai_dien']['type'], $allowedTypes)) {
            $uploadError = 'Định dạng file không hợp lệ. Chỉ chấp nhận: JPG, PNG, GIF, WEBP';
        } elseif ($_FILES['anh_dai_dien']['size'] > $maxSize) {
            $uploadError = 'File quá lớn. Kích thước tối đa: 5MB';
        } else {
            $extension = strtolower(pathinfo($_FILES['anh_dai_dien']['name'], PATHINFO_EXTENSION));
            $newFileName = 'staff_' . time() . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($_FILES['anh_dai_dien']['tmp_name'], $targetPath)) {
                // Xóa ảnh cũ nếu có
                if (!empty($anh_dai_dien)) {
                    $oldImagePath = '';
                    if (strpos($anh_dai_dien, 'client/') !== false) {
                        // Nếu là đường dẫn cũ từ client
                        $oldImagePath = __DIR__ . '/../../client/' . str_replace('client/', '', $anh_dai_dien);
                    } else if (strpos($anh_dai_dien, 'assets/images/staff/') !== false) {
                        // Nếu là relative path mới
                        $oldImagePath = __DIR__ . '/../' . $anh_dai_dien;
                    } else if (strpos($anh_dai_dien, '/') === 0) {
                        // Nếu là absolute path
                        $oldImagePath = __DIR__ . '/../../client' . $anh_dai_dien;
                    } else {
                        // Nếu chỉ là tên file
                        $oldImagePath = $uploadDir . $anh_dai_dien;
                    }
                    
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }
                $anh_dai_dien = 'assets/images/staff/' . $newFileName;
            } else {
                $uploadError = 'Không thể di chuyển file đã upload';
            }
        }
    } elseif (isset($_FILES['anh_dai_dien']) && $_FILES['anh_dai_dien']['error'] != UPLOAD_ERR_NO_FILE) {
        // Có file nhưng có lỗi
        $uploadError = 'Lỗi upload file. Mã lỗi: ' . $_FILES['anh_dai_dien']['error'];
    }
    
    // Cập nhật mật khẩu nếu có
    $updatePassword = '';
    if (!empty($_POST['mat_khau_moi'])) {
        $mat_khau_moi = password_hash($_POST['mat_khau_moi'], PASSWORD_DEFAULT);
        $updatePassword = ", mat_khau = ?";
    }
    
    if ($updatePassword) {
        $stmt = $mysqli->prepare("UPDATE nhan_vien SET ho_ten=?, dien_thoai=?, ngay_sinh=?, gioi_tinh=?, cmnd_cccd=?, dia_chi=?, anh_dai_dien=? $updatePassword WHERE id_nhan_vien=?");
        $stmt->bind_param("ssssssssi", $ho_ten, $dien_thoai, $ngay_sinh, $gioi_tinh, $cmnd_cccd, $dia_chi, $anh_dai_dien, $mat_khau_moi, $id_nhan_vien);
    } else {
        $stmt = $mysqli->prepare("UPDATE nhan_vien SET ho_ten=?, dien_thoai=?, ngay_sinh=?, gioi_tinh=?, cmnd_cccd=?, dia_chi=?, anh_dai_dien=? WHERE id_nhan_vien=?");
        $stmt->bind_param("sssssssi", $ho_ten, $dien_thoai, $ngay_sinh, $gioi_tinh, $cmnd_cccd, $dia_chi, $anh_dai_dien, $id_nhan_vien);
    }
    
    if ($stmt->execute()) {
        // Cập nhật session
        $_SESSION['ho_ten'] = $ho_ten;
        $_SESSION['anh_dai_dien'] = $anh_dai_dien;
        
        $stmt->close();
        
        // Reload lại thông tin từ database để đảm bảo có dữ liệu mới nhất
        $stmt = $mysqli->prepare("SELECT * FROM nhan_vien WHERE id_nhan_vien = ?");
        $stmt->bind_param("i", $id_nhan_vien);
        $stmt->execute();
        $result = $stmt->get_result();
        $nhanVien = $result->fetch_assoc();
        $stmt->close();
        
        if ($uploadError) {
            $message = 'Cập nhật thông tin thành công! ' . $uploadError;
            $messageType = 'warning';
        } else {
            $message = 'Cập nhật thông tin thành công!';
            $messageType = 'success';
        }
        // Không cần reload trang, chỉ cần cập nhật avatar bằng JavaScript
        $updateAvatar = true;
    } else {
        $message = 'Lỗi: ' . $stmt->error;
        if ($uploadError) {
            $message .= ' | ' . $uploadError;
        }
        $messageType = 'danger';
        $stmt->close();
    }
}
?>

<div class="main-content">
    <div class="content-header">
        <h1>Trang Cá Nhân</h1>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo h($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php if (isset($updateAvatar) && $updateAvatar && !empty($nhanVien['anh_dai_dien'])): ?>
        <script>
            // Cập nhật avatar sau khi upload thành công
            document.addEventListener('DOMContentLoaded', function() {
                const timestamp = new Date().getTime();
                const avatarImg = document.getElementById('profileAvatar');
                if (avatarImg) {
                    // Lấy URL từ attribute data hoặc src hiện tại
                    let currentSrc = avatarImg.getAttribute('data-original-src') || avatarImg.src;
                    
                    // Bỏ qua nếu là data URL (từ FileReader preview)
                    if (currentSrc && currentSrc.startsWith('data:')) {
                        return;
                    }
                    
                    // Lấy URL gốc không có query string
                    let baseUrl = currentSrc;
                    const queryIndex = baseUrl.indexOf('?');
                    if (queryIndex !== -1) {
                        baseUrl = baseUrl.substring(0, queryIndex);
                    }
                    
                    // Chỉ cập nhật nếu URL hợp lệ và không rỗng
                    if (baseUrl && baseUrl.length > 0 && baseUrl !== '?v' && !baseUrl.startsWith('data:')) {
                        avatarImg.src = baseUrl + '?v=' + timestamp;
                    }
                }
                
                // Cập nhật avatar trong sidebar
                const sidebarAvatar = document.querySelector('.sidebar .header img');
                if (sidebarAvatar) {
                    let sidebarSrc = sidebarAvatar.src;
                    // Bỏ qua nếu là data URL
                    if (sidebarSrc && !sidebarSrc.startsWith('data:')) {
                        const sidebarQueryIndex = sidebarSrc.indexOf('?');
                        if (sidebarQueryIndex !== -1) {
                            sidebarSrc = sidebarSrc.substring(0, sidebarQueryIndex);
                        }
                        if (sidebarSrc && sidebarSrc.length > 0 && sidebarSrc !== '?v') {
                            sidebarAvatar.src = sidebarSrc + '?v=' + timestamp;
                        }
                    }
                }
            });
        </script>
        <?php endif; ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <?php 
                    $avatarUrl = '';
                    if (!empty($nhanVien['anh_dai_dien'])) {
                        // Check if path is relative or absolute
                        if (strpos($nhanVien['anh_dai_dien'], 'assets/images/staff/') !== false) {
                            // Use absolute path like CSS files
                            $avatarUrl = '/My-Web-Hotel/admin/' . $nhanVien['anh_dai_dien'];
                        } elseif (strpos($nhanVien['anh_dai_dien'], '/') === 0) {
                            $avatarUrl = $nhanVien['anh_dai_dien'];
                        } else {
                            // Assume it's just filename, use absolute path
                            $avatarUrl = '/My-Web-Hotel/admin/assets/images/staff/' . $nhanVien['anh_dai_dien'];
                        }
                        // Chỉ thêm cache-busting nếu URL hợp lệ
                        if (!empty($avatarUrl) && $avatarUrl !== '?v=') {
                            $avatarUrl .= '?v=' . (isset($_GET['t']) ? $_GET['t'] : time());
                        }
                    } else {
                        $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($nhanVien['ho_ten']) . '&background=d4b896&color=fff&size=200';
                    }
                    ?>
                    <img src="<?php echo h($avatarUrl); ?>" alt="Avatar" id="profileAvatar" 
                         data-original-src="<?php echo h($avatarUrl); ?>"
                         class="rounded-circle mb-3" style="width: 200px; height: 200px; object-fit: cover;" 
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($nhanVien['ho_ten']); ?>&background=d4b896&color=fff&size=200';">
                    <h4><?php echo h($nhanVien['ho_ten']); ?></h4>
                    <p class="text-muted"><?php echo h($nhanVien['ma_nhan_vien']); ?></p>
                    <p class="badge bg-primary"><?php echo h($nhanVien['chuc_vu']); ?></p>
                    <p class="text-muted"><?php echo h($nhanVien['phong_ban'] ?: '-'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Thông Tin Cá Nhân</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Ảnh Đại Diện</label>
                            <input type="file" class="form-control" name="anh_dai_dien" accept="image/*" id="avatarInput">
                            <small class="text-muted">Chọn ảnh mới để cập nhật (tối đa 5MB)</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Họ và Tên *</label>
                                <input type="text" class="form-control" name="ho_ten" 
                                    value="<?php echo h($nhanVien['ho_ten']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mã Nhân Viên</label>
                                <input type="text" class="form-control" 
                                    value="<?php echo h($nhanVien['ma_nhan_vien']); ?>" disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" 
                                    value="<?php echo h($nhanVien['email']); ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số Điện Thoại</label>
                                <input type="text" class="form-control" name="dien_thoai" 
                                    value="<?php echo h($nhanVien['dien_thoai']); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ngày Sinh</label>
                                <input type="date" class="form-control" name="ngay_sinh" 
                                    value="<?php echo $nhanVien['ngay_sinh'] ? h($nhanVien['ngay_sinh']) : ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giới Tính</label>
                                <select class="form-select" name="gioi_tinh">
                                    <option value="Nam" <?php echo $nhanVien['gioi_tinh'] == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo $nhanVien['gioi_tinh'] == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="Khác" <?php echo $nhanVien['gioi_tinh'] == 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">CMND/CCCD</label>
                                <input type="text" class="form-control" name="cmnd_cccd" 
                                    value="<?php echo h($nhanVien['cmnd_cccd']); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa Chỉ</label>
                            <textarea class="form-control" name="dia_chi" rows="2"><?php echo h($nhanVien['dia_chi']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Chức Vụ</label>
                                <input type="text" class="form-control" 
                                    value="<?php echo h($nhanVien['chuc_vu']); ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ngày Vào Làm</label>
                                <input type="text" class="form-control" 
                                    value="<?php echo formatDate($nhanVien['ngay_vao_lam']); ?>" disabled>
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3">Đổi Mật Khẩu</h6>
                        <div class="mb-3">
                            <label class="form-label">Mật Khẩu Mới</label>
                            <input type="password" class="form-control" name="mat_khau_moi" 
                                placeholder="Để trống nếu không đổi">
                            <small class="text-muted">Chỉ nhập nếu muốn đổi mật khẩu</small>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập Nhật Thông Tin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatarInput');
    if (avatarInput) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('profileAvatar');
                    if (img) {
                        img.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Xóa tham số t khỏi URL nếu có (từ lần reload trước)
    if (window.location.search.includes('t=')) {
        const url = new URL(window.location);
        url.searchParams.delete('t');
        window.history.replaceState({}, '', url);
    }
});
</script>

