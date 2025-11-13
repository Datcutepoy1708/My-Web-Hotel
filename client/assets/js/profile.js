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
