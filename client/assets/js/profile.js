// Hiển thị username
document.addEventListener("DOMContentLoaded", function () {
  if (window.CURRENT_USER_NAME) {
    document.getElementById("username-display").textContent =
      window.CURRENT_USER_NAME;
    document.getElementById("logout-btn").style.display = "block";
  }
});
// Tự động ẩn alert sau 3 giây
document.addEventListener("DOMContentLoaded", function () {
  const alertEl = document.querySelector(".alert");
  if (alertEl) {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alertEl);
      bsAlert.close();
    }, 3000);
  }
});
// Mở tab tương ứng nếu có ?tab=... trong URL
const params = new URLSearchParams(window.location.search);
const activeTab = params.get("tab");
if (activeTab) {
  const triggerEl = document.querySelector(`[href="#${activeTab}"]`);
  if (triggerEl) {
    const tab = new bootstrap.Tab(triggerEl);
    tab.show();
  }
}
document.addEventListener("DOMContentLoaded", function () {
  // Lấy tất cả các icon mắt
  document.querySelectorAll(".toggle-password").forEach((icon) => {
    icon.addEventListener("click", function () {
      const input = this.previousElementSibling; // input ngay trước icon
      const isPassword = input.type === "password";
      input.type = isPassword ? "text" : "password";
      // Đổi icon tương ứng
      this.classList.toggle("fa-eye");
      this.classList.toggle("fa-eye-slash");
    });
  });
});

// Xử lý nút đăng xuất
const logoutBtn = document.getElementById("logout-btn");
const logoutModal = new bootstrap.Modal(document.getElementById("logoutModal"));

logoutBtn?.addEventListener("click", function () {
  logoutModal.show();
});

// Xác nhận đăng xuất
document
  .getElementById("confirmLogout")
  ?.addEventListener("click", function () {
    window.location.href = "/My-Web-Hotel/client/controller/logout.php";
  });

// Hàm quay về trang chủ
function goHome() {
  window.location.href = "/My-Web-Hotel/client/index.php?page=home";
}

// Hàm xóa booking
function deleteRoom(id) {
  if (confirm("Bạn có chắc chắn muốn hủy đặt phòng này?")) {
    console.log("Deleting room:", id);
    // Thêm logic xóa ở đây
  }
}

// Hàm xem chi tiết booking
function viewRoom(id) {
  console.log("Viewing room:", id);
  // Thêm logic xem chi tiết ở đây
}
