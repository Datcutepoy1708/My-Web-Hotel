let counts = { adults: 0, children: 0, rooms: 0 };

function changeCount(type, delta) {
  counts[type] = Math.max(0, counts[type] + delta);
  document.getElementById(type).innerText = counts[type];
  updateSummary();
}

function updateSummary() {
  document.getElementById(
    "summary-text"
  ).innerText = `${counts.adults} Người lớn | ${counts.children} Trẻ em | ${counts.rooms} Phòng `;
}
// Lấy ngày hôm nay
const today = new Date().toISOString().split("T")[0];
const checkin = document.getElementById("checkin");
const checkout = document.getElementById("checkout");

// Không cho chọn ngày quá khứ
checkin.min = today;
checkout.min = today;

// Khi người dùng chọn ngày nhận phòng
checkin.addEventListener("change", function () {
  const checkinDate = checkin.value;

  // Gán min cho checkout bằng checkin
  checkout.min = checkinDate;

  // Nếu ngày checkout hiện tại < checkin → tự động cập nhật
  if (checkout.value && checkout.value < checkinDate) {
    checkout.value = checkinDate;
  }
});
