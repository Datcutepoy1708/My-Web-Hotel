<<<<<<< HEAD
function showPage(pageId) {
  document
    .querySelectorAll(".page")
    .forEach((p) => p.classList.remove("active"));
  document
    .querySelectorAll(".menu li")
    .forEach((m) => m.classList.remove("active"));
  document.getElementById(pageId).classList.add("active");

  const index = {
    personal: 0,
    inbox: 1,
    history: 2,
  };
  document.querySelectorAll(".menu li")[index[pageId]].classList.add("active");
}

function goHome() {
  window.location.href = "/My-Web-Hotel/index.php?page=home";
}

document.addEventListener("DOMContentLoaded", () => {
  const usernameDisplay = document.getElementById("username-display");
  const logoutBtn = document.getElementById("logout-btn");
  const popup = document.getElementById("logout-popup");
  const enterLogoutBtn = document.getElementById("enter-logout-btn");
  const closePopup = document.getElementById("close-popup");

  // Gán tên người dùng vào sidebar
  const name = window.CURRENT_USER_NAME || "Người dùng";
  usernameDisplay.textContent = name;

  // Hiện nút đăng xuất
  logoutBtn.style.display = "inline-block";
  //gán sự kiện cho nút đăng xuất
  logoutBtn.addEventListener("click", () => {
    popup.style.display = "flex";
  });
  enterLogoutBtn.addEventListener("click", () => {
    window.location.href = "/My-Web-Hotel/pages/logout.php";
  });
  closePopup.addEventListener("click", () => {
    popup.style.display = "none";
  });
});
=======
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
  if (alertBox) {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alertBox);
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
>>>>>>> main
