// Preview avatar khi chọn file
document.getElementById("avatarInput").addEventListener("change", function (e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("avatarPreview").src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
});

// Hàm xóa nhân viên
function deleteEmployee(id) {
  if (confirm("Bạn có chắc chắn muốn xóa nhân viên này?")) {
    alert("Đã xóa nhân viên: " + id);
  }
}

// Hàm lưu quyền
function savePermissions() {
  alert("Đã lưu phân quyền thành công!");
}

// Hàm giao nhiệm vụ
function assignTask() {
  alert("Đã giao nhiệm vụ thành công!");
}
