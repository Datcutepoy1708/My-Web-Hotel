// JavaScript functions for room management
function editRoom(roomId) {
  // Logic to edit room
  console.log("Edit room:", roomId);
  // You can implement edit functionality here
}

function deleteRoom(roomId) {
  if (confirm("Bạn có chắc chắn muốn xóa phòng này?")) {
    // Logic to delete room
    console.log("Delete room:", roomId);
    // You can implement delete functionality here
  }
}

function viewRoom(roomId) {
  // Lấy dữ liệu từ form Thêm/Sửa phòng và đổ vào modal xem chi tiết
  const code = (document.getElementById("roomCode")?.value || "").trim();
  const typeValue = document.getElementById("roomType")?.value || "";
  const typeText =
    document.getElementById("roomType")?.options[
      document.getElementById("roomType").selectedIndex
    ]?.text || "";
  const area = document.getElementById("roomArea")?.value || "";
  const capacity = document.getElementById("roomCapacity")?.value || "";
  const description = (
    document.getElementById("roomDescription")?.value || ""
  ).trim();

  // Set text fields
  const viewRoomCodeEl = document.getElementById("viewRoomCode");
  const viewRoomTypeEl = document.getElementById("viewRoomType");
  const viewRoomAreaEl = document.getElementById("viewRoomArea");
  const viewRoomCapacityEl = document.getElementById("viewRoomCapacity");
  const viewRoomDescriptionEl = document.getElementById("viewRoomDescription");

  if (viewRoomCodeEl) viewRoomCodeEl.textContent = code || "Chưa có mã phòng";
  if (viewRoomTypeEl) viewRoomTypeEl.textContent = typeText || "-";
  if (viewRoomAreaEl) viewRoomAreaEl.textContent = area ? `${area} m²` : "-";
  if (viewRoomCapacityEl)
    viewRoomCapacityEl.textContent = capacity ? `${capacity} người` : "-";
  if (viewRoomDescriptionEl)
    viewRoomDescriptionEl.textContent = description || "-";

  // Xử lý hình ảnh xem chi tiết
  const viewImages = document.getElementById("viewImages");
  const files = document.getElementById("roomImages")?.files || [];
  if (viewImages) {
    viewImages.innerHTML = "";
    if (files.length > 0) {
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type.startsWith("image/")) {
          const url = URL.createObjectURL(file);
          const img = document.createElement("img");
          img.src = url;
          img.alt = "Room Image";
          img.className = "img-fluid rounded";
          img.style.maxWidth = "48%";
          viewImages.appendChild(img);
        }
      }
    } else {
      const fallback = document.createElement("img");
      fallback.src = "https://via.placeholder.com/400x300";
      fallback.alt = "Room Image";
      fallback.className = "img-fluid rounded";
      viewImages.appendChild(fallback);
    }
  }

  const viewModal = new bootstrap.Modal(
    document.getElementById("viewRoomModal")
  );
  viewModal.show();
}

function saveRoom() {
  // Logic to save room
  console.log("Save room");
  // You can implement save functionality here
  const addModal = bootstrap.Modal.getInstance(
    document.getElementById("addRoomModal")
  );
  addModal.hide();
}

function editRoomFromView() {
  const viewModal = bootstrap.Modal.getInstance(
    document.getElementById("viewRoomModal")
  );
  viewModal.hide();
  const addModal = new bootstrap.Modal(document.getElementById("addRoomModal"));
  addModal.show();
}

// Search and filter functionality
document.getElementById("searchInput").addEventListener("input", function () {
  const searchTerm = this.value.toLowerCase();
  const table = document.getElementById("roomsTable");
  const rows = table.getElementsByTagName("tr");

  for (let i = 1; i < rows.length; i++) {
    const row = rows[i];
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(searchTerm) ? "" : "none";
  }
});

document.getElementById("statusFilter").addEventListener("change", filterTable);
document.getElementById("typeFilter").addEventListener("change", filterTable);

function filterTable() {
  const statusFilter = document.getElementById("statusFilter").value;
  const typeFilter = document.getElementById("typeFilter").value;
  const table = document.getElementById("roomsTable");
  const rows = table.getElementsByTagName("tr");

  for (let i = 1; i < rows.length; i++) {
    const row = rows[i];
    const statusText = row.cells[4].textContent.toLowerCase();
    const typeText = row.cells[2].textContent.toLowerCase();

    let showRow = true;

    // Lọc theo trạng thái
    if (statusFilter) {
      if (statusFilter === "available" && !statusText.includes("có sẵn")) {
        showRow = false;
      } else if (
        statusFilter === "occupied" &&
        !statusText.includes("đã thuê")
      ) {
        showRow = false;
      } else if (
        statusFilter === "maintenance" &&
        !statusText.includes("bảo trì")
      ) {
        showRow = false;
      }
    }

    // Lọc theo loại phòng
    if (typeFilter) {
      if (typeFilter === "standard" && !typeText.includes("standard")) {
        showRow = false;
      } else if (typeFilter === "deluxe" && !typeText.includes("deluxe")) {
        showRow = false;
      } else if (typeFilter === "suite" && !typeText.includes("suite")) {
        showRow = false;
      }
    }

    row.style.display = showRow ? "" : "none";
  }
}

document.getElementById("resetFilter").addEventListener("click", function () {
  document.getElementById("searchInput").value = "";
  document.getElementById("statusFilter").value = "";
  document.getElementById("typeFilter").value = "";

  const table = document.getElementById("roomsTable");
  const rows = table.getElementsByTagName("tr");

  for (let i = 1; i < rows.length; i++) {
    rows[i].style.display = "";
  }
});

// Xử lý preview hình ảnh
document.getElementById("roomImages").addEventListener("change", function (e) {
  const files = e.target.files;
  const previewContainer = document.getElementById("imagePreview");
  previewContainer.innerHTML = "";

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    if (file.type.startsWith("image/")) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const previewItem = document.createElement("div");
        previewItem.className = "image-preview-item";
        previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-btn" onclick="removeImage(this)">×</button>
                `;
        previewContainer.appendChild(previewItem);
      };
      reader.readAsDataURL(file);
    }
  }
});

function removeImage(btn) {
  btn.parentElement.remove();
  // Cập nhật lại file input
  updateFileInput();
}

function updateFileInput() {
  const fileInput = document.getElementById("roomImages");
  const previewItems = document.querySelectorAll(".image-preview-item");

  // Tạo DataTransfer object để cập nhật file list
  const dt = new DataTransfer();

  // Lưu lại các file còn lại (này chỉ là demo, thực tế cần lưu file gốc)
  // Trong thực tế, bạn cần lưu file gốc trong một array để quản lý
  fileInput.files = dt.files;
}
