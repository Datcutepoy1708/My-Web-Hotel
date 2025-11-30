<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/StaffModel.php';

/**
 * Profile Controller
 */
class ProfileController extends BaseController {
    
    public function index() {
        $staff_id = $this->getCurrentStaffId();
        
        if (!$staff_id) {
            $this->redirect('pages/logIn.php');
            return;
        }
        
        $staffModel = new StaffModel($this->mysqli);
        
        // Handle POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['update_profile'])) {
                $this->handleUpdateProfile($staffModel, $staff_id);
            } elseif (isset($_POST['change_password'])) {
                $this->handleChangePassword($staff_id);
            }
        }
        
        $staff = $staffModel->getById($staff_id);
        
        // Make $mysqli available in included file scope
        $mysqli = $this->mysqli;
        
        // Use old profile.php for now
        include __DIR__ . '/../pages/profile.php';
    }
    
    private function handleUpdateProfile($model, $staff_id) {
        $data = [
            'ho_ten' => $_POST['ho_ten'],
            'email' => $_POST['email'],
            'so_dien_thoai' => $_POST['so_dien_thoai'],
            'dia_chi' => $_POST['dia_chi'] ?? '',
            'ngay_sinh' => !empty($_POST['ngay_sinh']) ? $_POST['ngay_sinh'] : null,
            'gioi_tinh' => $_POST['gioi_tinh'] ?? 'Nam'
        ];
        
        if ($model->update($staff_id, $data)) {
            $_SESSION['message'] = 'Cập nhật thông tin thành công';
            $_SESSION['messageType'] = 'success';
        } else {
            $_SESSION['message'] = 'Lỗi khi cập nhật thông tin';
            $_SESSION['messageType'] = 'danger';
        }
        
        $this->redirect('index.php?page=profile');
    }
    
    private function handleChangePassword($staff_id) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $_SESSION['message'] = 'Mật khẩu mới không khớp';
            $_SESSION['messageType'] = 'danger';
            $this->redirect('index.php?page=profile');
            return;
        }
        
        // Verify current password
        $stmt = $this->mysqli->prepare("SELECT mat_khau FROM nhan_vien WHERE id_nhan_vien = ?");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $staff = $result->fetch_assoc();
        $stmt->close();
        
        if (!password_verify($current_password, $staff['mat_khau'])) {
            $_SESSION['message'] = 'Mật khẩu hiện tại không đúng';
            $_SESSION['messageType'] = 'danger';
            $this->redirect('index.php?page=profile');
            return;
        }
        
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->mysqli->prepare("UPDATE nhan_vien SET mat_khau = ? WHERE id_nhan_vien = ?");
        $stmt->bind_param("si", $hashed_password, $staff_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Đổi mật khẩu thành công';
            $_SESSION['messageType'] = 'success';
        } else {
            $_SESSION['message'] = 'Lỗi khi đổi mật khẩu';
            $_SESSION['messageType'] = 'danger';
        }
        $stmt->close();
        
        $this->redirect('index.php?page=profile');
    }
}

