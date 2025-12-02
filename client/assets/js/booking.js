<<<<<<< HEAD
=======
(function autoFillBookingData() {
  // Chỉ chạy sau khi DOM load xong
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", fillBookingForm);
  } else {
    fillBookingForm();
  }

  function fillBookingForm() {
    const bookingDataStr = sessionStorage.getItem("bookingData");

    if (!bookingDataStr) return; // Không có dữ liệu từ trang chi tiết

    try {
      const bookingData = JSON.parse(bookingDataStr);

      // Điền thông tin phòng
      const roomNameInput = document.getElementById("roomName");
      const roomTypeSelect = document.getElementById("roomType");
      const guestCountInput = document.getElementById("guestCount");
      const childCountInput = document.getElementById("childCount");
      const roomPriceInput = document.getElementById("roomPrice");
      const checkinInput = document.getElementById("checkinTime");
      const checkoutInput = document.getElementById("checkoutTime");

      if (roomNameInput && bookingData.roomName) {
        roomNameInput.value = bookingData.roomName;
        roomNameInput.readOnly = true;
        roomNameInput.style.backgroundColor = "#f5f5f5";
      }

      if (roomTypeSelect && bookingData.roomType) {
        for (let i = 0; i < roomTypeSelect.options.length; i++) {
          if (roomTypeSelect.options[i].value === bookingData.roomType) {
            roomTypeSelect.selectedIndex = i;
            roomTypeSelect.disabled = true;
            roomTypeSelect.style.backgroundColor = "#f5f5f5";
            break;
          }
        }
      }

      if (guestCountInput && bookingData.adults) {
        guestCountInput.value = bookingData.adults;
      }

      if (childCountInput) {
        childCountInput.value = bookingData.children || 0;
      }

      if (checkinInput && bookingData.checkin) {
        checkinInput.value = bookingData.checkin + "T14:00";
      }

      if (checkoutInput && bookingData.checkout) {
        checkoutInput.value = bookingData.checkout + "T12:00";
      }

      if (roomPriceInput && bookingData.roomPrice) {
        roomPriceInput.value = bookingData.roomPrice;
        roomPriceInput.readOnly = true;
        roomPriceInput.style.backgroundColor = "#f5f5f5";
      }

      // Lưu roomId để sử dụng khi submit
      const form = document.getElementById("roomBookingForm");
      if (form && bookingData.roomId) {
        form.dataset.roomId = bookingData.roomId;
      }

      // Xóa dữ liệu khỏi sessionStorage sau khi đã sử dụng
      sessionStorage.removeItem("bookingData");

      // Trigger update summary sau khi điền xong
      setTimeout(() => {
        updateSummary();
      }, 100);
    } catch (error) {
      console.error("❌ Lỗi khi parse booking data:", error);
    }
  }
})();

>>>>>>> main
// Lưu hóa đơn thành file PDF (dạng đơn giản: lưu HTML thành file)
function saveInvoice() {
  const invoice = document.getElementById("invoiceContainer");
  const opt = {
    margin: 0.5,
    filename: "hoa-don-dat-phong.pdf",
    image: { type: "jpeg", quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: "in", format: "a4", orientation: "portrait" },
  };
  if (window.html2pdf) {
    window.html2pdf().from(invoice).set(opt).save();
  } else {
    alert(
      "Chức năng lưu hóa đơn cần thư viện html2pdf. Vui lòng thêm html2pdf vào trang!"
    );
  }
}
<<<<<<< HEAD
=======

>>>>>>> main
// Dữ liệu mã khuyến mãi
const promoCodes = {
  SUMMER2025: {
    discount: 500000,
    description: "Giảm 500,000đ - Khuyến mãi mùa hè 2025",
  },
  VIP20: {
    discount: 20,
    description: "Giảm 20% cho khách VIP",
    isPercent: true,
  },
  NEWYEAR: {
    discount: 1000000,
    description: "Giảm 1,000,000đ - Chào năm mới",
  },
  WEEKEND: {
    discount: 15,
    description: "Giảm 15% cuối tuần",
    isPercent: true,
  },
  FAMILY: {
    discount: 300000,
    description: "Giảm 300,000đ - Ưu đãi gia đình",
  },
};

let currentDiscount = 0;
let appliedPromoCode = "";

// Hàm định dạng tiền tệ
function formatCurrency(amount) {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
}

// Hàm định dạng ngày giờ
function formatDateTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleString("vi-VN", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  });
}

// Hàm tính số đêm
function calculateNights(checkin, checkout) {
  const start = new Date(checkin);
  const end = new Date(checkout);
  const diffTime = Math.abs(end - start);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
}

// Hàm tạo mã booking
function generateBookingCode() {
  const date = new Date();
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  const random = Math.floor(Math.random() * 1000)
    .toString()
    .padStart(3, "0");
  return `BK${year}${month}${day}${random}`;
}

// Hàm tính tổng giá dịch vụ
function calculateServiceTotal() {
  const checkboxes = document.querySelectorAll(
    'input[name="services"]:checked'
  );
  let total = 0;
  const checkin = document.getElementById("checkinTime").value;
  const checkout = document.getElementById("checkoutTime").value;
  const nights = checkin && checkout ? calculateNights(checkin, checkout) : 1;

  checkboxes.forEach((cb) => {
    const price = parseInt(cb.dataset.price);
    const serviceValue = cb.value;
    // Buffet sáng và minibar tính theo số đêm
    if (serviceValue === "breakfast" || serviceValue === "minibar") {
      total += price * nights;
    } else {
      total += price;
    }
  });

  return total;
}

// Hàm cập nhật tổng kết
function updateSummary() {
<<<<<<< HEAD
  const roomPrice = parseInt(document.getElementById("roomPrice").value) || 0;
  const serviceTotal = calculateServiceTotal();
  const subtotal = roomPrice + serviceTotal;
  const discount = currentDiscount;
  const total = Math.max(0, subtotal - discount);
  const deposit = Math.round(total * 0.3);

  document.getElementById("summaryRoomPrice").textContent =
    formatCurrency(roomPrice);
=======
  const roomPriceInput = document.getElementById("roomPrice");
  const checkinInput = document.getElementById("checkinTime");
  const checkoutInput = document.getElementById("checkoutTime");

  if (!roomPriceInput || !checkinInput || !checkoutInput) return;

  const roomPricePerNight =
    parseInt(roomPriceInput.value.replace(/[^\d]/g, "")) || 0;

  const checkin = checkinInput.value;
  const checkout = checkoutInput.value;

  // Tính số đêm
  let nights = 1;
  let totalRoomPrice = roomPricePerNight;

  if (checkin && checkout) {
    nights = calculateNights(checkin, checkout);
    totalRoomPrice = roomPricePerNight * nights;

    // Hiển thị số đêm nếu có element
    const nightCountDisplay = document.getElementById("nightCountDisplay");
    if (nightCountDisplay) {
      nightCountDisplay.textContent = nights;
    }
  }

  const serviceTotal = calculateServiceTotal();
  const discount = currentDiscount || 0;
  const subtotal = totalRoomPrice + serviceTotal;
  const total = Math.max(0, subtotal - discount);
  const deposit = Math.round(total * 0.3);

  // Cập nhật giao diện
  document.getElementById("summaryRoomPrice").textContent =
    formatCurrency(totalRoomPrice);
>>>>>>> main
  document.getElementById("summaryServicePrice").textContent =
    formatCurrency(serviceTotal);
  document.getElementById("summaryDiscount").textContent =
    discount > 0 ? "- " + formatCurrency(discount) : "0 ₫";
  document.getElementById("summaryTotal").textContent = formatCurrency(total);
  document.getElementById("summaryDeposit").textContent =
    formatCurrency(deposit);
}

// Áp dụng mã khuyến mãi
function applyPromoCode() {
  const promoInput = document.getElementById("promoCode");
  const code = promoInput.value.trim().toUpperCase();
  const discountDisplay = document.getElementById("discountDisplay");
  const discountAmount = document.getElementById("discountAmount");

  if (!code) {
    alert("Vui lòng nhập mã khuyến mãi!");
    return;
  }

  if (promoCodes[code]) {
    const promo = promoCodes[code];
<<<<<<< HEAD
    const roomPrice = parseInt(document.getElementById("roomPrice").value) || 0;
    const serviceTotal = calculateServiceTotal();
    const subtotal = roomPrice + serviceTotal;
=======
    const roomPriceInput = document.getElementById("roomPrice");
    const checkinInput = document.getElementById("checkinTime");
    const checkoutInput = document.getElementById("checkoutTime");

    const roomPricePerNight = parseInt(roomPriceInput.value) || 0;
    const nights =
      checkinInput.value && checkoutInput.value
        ? calculateNights(checkinInput.value, checkoutInput.value)
        : 1;
    const totalRoomPrice = roomPricePerNight * nights;
    const serviceTotal = calculateServiceTotal();
    const subtotal = totalRoomPrice + serviceTotal;
>>>>>>> main

    if (promo.isPercent) {
      currentDiscount = Math.round((subtotal * promo.discount) / 100);
    } else {
      currentDiscount = promo.discount;
    }

    appliedPromoCode = code;
    discountAmount.textContent = formatCurrency(currentDiscount);
    discountDisplay.classList.add("active");
    updateSummary();
    alert(`✅ Áp dụng thành công!\n${promo.description}`);
  } else {
    alert("❌ Mã khuyến mãi không hợp lệ!");
    currentDiscount = 0;
    appliedPromoCode = "";
    discountDisplay.classList.remove("active");
    updateSummary();
  }
}

// Lắng nghe sự kiện thay đổi
<<<<<<< HEAD
document.getElementById("roomPrice").addEventListener("input", updateSummary);
document.querySelectorAll('input[name="services"]').forEach((cb) => {
  cb.addEventListener("change", () => {
    updateSummary();
    // Tính lại discount nếu đã áp dụng mã %
    if (appliedPromoCode && promoCodes[appliedPromoCode].isPercent) {
      applyPromoCode();
    }
  });
});
document
  .getElementById("checkinTime")
  .addEventListener("change", updateSummary);
document
  .getElementById("checkoutTime")
  .addEventListener("change", updateSummary);

// Xử lý submit form
document
  .getElementById("roomBookingForm")
  .addEventListener("submit", function (e) {
    e.preventDefault();

    // Lấy dữ liệu từ form
    const roomPrice = parseInt(document.getElementById("roomPrice").value);
    const serviceTotal = calculateServiceTotal();
    const subtotal = roomPrice + serviceTotal;
    const discount = currentDiscount;
    const total = Math.max(0, subtotal - discount);
    const deposit = Math.round(total * 0.3);

    // Lấy danh sách dịch vụ đã chọn
    const selectedServices = [];
    document
      .querySelectorAll('input[name="services"]:checked')
      .forEach((cb) => {
        const label = document.querySelector(`label[for="${cb.id}"]`);
        const serviceName = label.querySelector(".service-name").textContent;
        const servicePrice = parseInt(cb.dataset.price);
        const serviceValue = cb.value;

        const checkin = document.getElementById("checkinTime").value;
        const checkout = document.getElementById("checkoutTime").value;
        const nights = calculateNights(checkin, checkout);

        let finalPrice = servicePrice;
        if (serviceValue === "breakfast" || serviceValue === "minibar") {
          finalPrice = servicePrice * nights;
        }

        selectedServices.push({
          name: serviceName,
          price: finalPrice,
        });
      });

    const formData = {
      roomName: document.getElementById("roomName").value,
      roomType: document.getElementById("roomType").value,
      guestCount: parseInt(document.getElementById("guestCount").value),
      childCount: parseInt(document.getElementById("childCount").value),
      checkinTime: document.getElementById("checkinTime").value,
      checkoutTime: document.getElementById("checkoutTime").value,
      roomPrice: roomPrice,
      serviceTotal: serviceTotal,
      services: selectedServices,
      discount: discount,
      promoCode: appliedPromoCode,
      promoDescription: appliedPromoCode
        ? promoCodes[appliedPromoCode].description
        : "",
      deposit: deposit,
      total: total,
      paymentMethod: document.getElementById("paymentMethod").value,
      notes: document.getElementById("notes").value,
      bookingCode: generateBookingCode(),
    };

    // Validate ngày checkout phải sau checkin
    if (new Date(formData.checkoutTime) <= new Date(formData.checkinTime)) {
      alert("Ngày check-out phải sau ngày check-in!");
      return;
    }

    // Hiển thị hóa đơn
    showInvoice(formData);
  });
=======
document.addEventListener("DOMContentLoaded", function () {
  const roomPriceInput = document.getElementById("roomPrice");
  const checkinInput = document.getElementById("checkinTime");
  const checkoutInput = document.getElementById("checkoutTime");
  const serviceCheckboxes = document.querySelectorAll('input[name="services"]');

  if (roomPriceInput) {
    roomPriceInput.addEventListener("input", updateSummary);
  }

  if (checkinInput) {
    checkinInput.addEventListener("change", updateSummary);
  }

  if (checkoutInput) {
    checkoutInput.addEventListener("change", updateSummary);
  }

  serviceCheckboxes.forEach((cb) => {
    cb.addEventListener("change", () => {
      updateSummary();
      // Tính lại discount nếu đã áp dụng mã %
      if (appliedPromoCode && promoCodes[appliedPromoCode].isPercent) {
        applyPromoCode();
      }
    });
  });
});

// Xử lý submit form
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("roomBookingForm");

  if (form) {
    form.addEventListener("submit", function (e) {
      e.preventDefault();

      // ========  Lấy dữ liệu cơ bản ========
      const roomPriceInput = document.getElementById("roomPrice");
      const checkinInput = document.getElementById("checkinTime");
      const checkoutInput = document.getElementById("checkoutTime");

      // Ép kiểu từ chuỗi → số (bỏ ký tự ₫, dấu phẩy,...)
      const roomPricePerNight =
        parseInt(String(roomPriceInput.value).replace(/[^\d]/g, "")) || 0;

      const checkin = checkinInput.value;
      const checkout = checkoutInput.value;

      // ========  Tính toán thời gian và giá ========
      const nights = calculateNights(checkin, checkout);
      const totalRoomPrice = roomPricePerNight * nights;

      const serviceTotal = calculateServiceTotal();
      const discount = currentDiscount || 0;
      const subtotal = totalRoomPrice + serviceTotal;
      const total = Math.max(0, subtotal - discount);
      const deposit = Math.round(total * 0.3);

      // ========  Lấy danh sách dịch vụ đã chọn ========
      const selectedServices = [];
      document
        .querySelectorAll('input[name="services"]:checked')
        .forEach((cb) => {
          const label = document.querySelector(`label[for="${cb.id}"]`);
          const serviceName = label
            .querySelector(".service-name")
            .textContent.trim();
          const servicePrice =
            parseInt(String(cb.dataset.price).replace(/[^\d]/g, "")) || 0;
          const serviceValue = cb.value;

          let finalPrice = servicePrice;
          if (serviceValue === "breakfast" || serviceValue === "minibar") {
            finalPrice = servicePrice * nights;
          }

          selectedServices.push({
            name: serviceName,
            price: finalPrice,
          });
        });

      // ========  Gom dữ liệu để hiển thị hoá đơn ========
      const formData = {
        roomId: form.dataset.roomId || null,
        roomName: document.getElementById("roomName").value,
        roomType: document.getElementById("roomType").value,
        guestCount: parseInt(document.getElementById("guestCount").value) || 0,
        childCount: parseInt(document.getElementById("childCount").value) || 0,
        checkinTime: checkin,
        checkoutTime: checkout,
        roomPrice: totalRoomPrice, // Tổng giá phòng (đã nhân số đêm)
        roomPricePerNight: roomPricePerNight, // Giá/đêm
        serviceTotal: serviceTotal,
        services: selectedServices,
        discount: discount,
        promoCode: appliedPromoCode,
        promoDescription: appliedPromoCode
          ? promoCodes[appliedPromoCode].description
          : "",
        deposit: deposit,
        total: total,
        paymentMethod: document.getElementById("paymentMethod").value,
        notes: document.getElementById("notes").value,
        bookingCode: generateBookingCode(),
      };

      // ========  Kiểm tra ngày hợp lệ ========
      if (new Date(formData.checkoutTime) <= new Date(formData.checkinTime)) {
        alert("Ngày check-out phải sau ngày check-in!");
        return;
      }

      // ========  Hiển thị hóa đơn ========
      showInvoice(formData);
    });
  }
});
>>>>>>> main

// Hiển thị hóa đơn
function showInvoice(data) {
  // Ẩn form, hiện hóa đơn
  document.getElementById("bookingForm").style.display = "none";
  document.getElementById("invoiceContainer").classList.add("active");

  // Điền dữ liệu vào hóa đơn
  document.getElementById("invoiceRoomName").textContent = data.roomName;
  document.getElementById("invoiceRoomType").textContent = data.roomType;
  document.getElementById("invoiceGuestCount").textContent =
    data.guestCount + " người";
  document.getElementById("invoiceChildCount").textContent =
    data.childCount + " người";

  document.getElementById("invoiceCheckinTime").textContent = formatDateTime(
    data.checkinTime
  );
  document.getElementById("invoiceCheckoutTime").textContent = formatDateTime(
    data.checkoutTime
  );
  const nights = calculateNights(data.checkinTime, data.checkoutTime);
  document.getElementById("invoiceNightCount").textContent = nights + " đêm";

  // Dịch vụ
  if (data.services.length > 0) {
    document.getElementById("invoiceServicesSection").style.display = "block";
    const servicesList = document.getElementById("invoiceServicesList");
    servicesList.innerHTML = "";
    data.services.forEach((service) => {
      const item = document.createElement("div");
      item.className = "service-list-item";
      item.innerHTML = `
                        <span>${service.name}</span>
<<<<<<< HEAD
                        <span style="color: #deb666; font-weight: 600;">${formatCurrency(
=======
                        <span style="font-weight: 600;">${formatCurrency(
>>>>>>> main
                          service.price
                        )}</span>
                    `;
      servicesList.appendChild(item);
    });
  } else {
    document.getElementById("invoiceServicesSection").style.display = "none";
  }

  // Khuyến mãi
  if (data.promoCode) {
    document.getElementById("invoicePromotionSection").style.display = "block";
    document.getElementById(
      "invoicePromotion"
    ).textContent = `${data.promoCode} - ${data.promoDescription}`;
  } else {
    document.getElementById("invoicePromotionSection").style.display = "none";
  }

  // Ghi chú
  if (data.notes) {
    document.getElementById("invoiceNotesSection").style.display = "block";
    document.getElementById("invoiceNotes").textContent = data.notes;
  } else {
    document.getElementById("invoiceNotesSection").style.display = "none";
  }

  // Thanh toán
  document.getElementById("invoiceRoomPrice").textContent = formatCurrency(
    data.roomPrice
  );

  if (data.serviceTotal > 0) {
    document.getElementById("invoiceServicePriceRow").style.display = "flex";
    document.getElementById("invoiceServicePrice").textContent = formatCurrency(
      data.serviceTotal
    );
  } else {
    document.getElementById("invoiceServicePriceRow").style.display = "none";
  }

  if (data.discount > 0) {
    document.getElementById("invoiceDiscountRow").style.display = "flex";
    document.getElementById("invoiceDiscount").textContent =
      "- " + formatCurrency(data.discount);
  } else {
    document.getElementById("invoiceDiscountRow").style.display = "none";
  }

  document.getElementById("invoiceTotalAmount").textContent = formatCurrency(
    data.total
  );
  document.getElementById("invoiceDeposit").textContent = formatCurrency(
    data.deposit
  );
  document.getElementById("invoicePaymentMethod").textContent =
    data.paymentMethod;
  document.getElementById("bookingCode").textContent = data.bookingCode;

  // Cuộn lên đầu trang
  window.scrollTo({ top: 0, behavior: "smooth" });
}

// Quay lại chỉnh sửa
function editBooking() {
  document.getElementById("bookingForm").style.display = "block";
  document.getElementById("invoiceContainer").classList.remove("active");
  window.scrollTo({ top: 0, behavior: "smooth" });
}

// Xác nhận thanh toán
function confirmPayment() {
  const bookingCode = document.getElementById("bookingCode").textContent;
  alert(
    `Thanh toán thành công!\n\nMã đặt phòng: ${bookingCode}\n\nCảm ơn quý khách đã sử dụng dịch vụ.`
  );

  // Reset form và quay lại trang đặt phòng
  document.getElementById("roomBookingForm").reset();
  currentDiscount = 0;
  appliedPromoCode = "";
  document.getElementById("discountDisplay").classList.remove("active");
  updateSummary();
  editBooking();
}

<<<<<<< HEAD
// Set giá trị mặc định cho datetime
window.addEventListener("DOMContentLoaded", function () {
  const now = new Date();
  const tomorrow = new Date(now);
  tomorrow.setDate(tomorrow.getDate() + 1);

  // Set checkin là ngày mai lúc 14:00
  const checkinDate = new Date(tomorrow);
  checkinDate.setHours(14, 0, 0);
  document.getElementById("checkinTime").value = checkinDate
    .toISOString()
    .slice(0, 16);

  // Set checkout là ngày kia lúc 12:00
  const checkoutDate = new Date(tomorrow);
  checkoutDate.setDate(checkoutDate.getDate() + 1);
  checkoutDate.setHours(12, 0, 0);
  document.getElementById("checkoutTime").value = checkoutDate
    .toISOString()
    .slice(0, 16);
=======
// Set giá trị mặc định cho datetime (chỉ khi KHÔNG có dữ liệu từ trang chi tiết)
window.addEventListener("DOMContentLoaded", function () {
  // Kiểm tra xem có dữ liệu booking từ trang chi tiết không
  const hasBookingData = sessionStorage.getItem("bookingData");

  if (!hasBookingData) {
    // Chỉ set giá trị mặc định khi không có dữ liệu từ trang chi tiết
    const now = new Date();
    const tomorrow = new Date(now);
    tomorrow.setDate(tomorrow.getDate() + 1);

    // Set checkin là ngày mai lúc 14:00
    const checkinDate = new Date(tomorrow);
    checkinDate.setHours(14, 0, 0);
    const checkinInput = document.getElementById("checkinTime");
    if (checkinInput && !checkinInput.value) {
      checkinInput.value = checkinDate.toISOString().slice(0, 16);
    }

    // Set checkout là ngày kia lúc 12:00
    const checkoutDate = new Date(tomorrow);
    checkoutDate.setDate(checkoutDate.getDate() + 1);
    checkoutDate.setHours(12, 0, 0);
    const checkoutInput = document.getElementById("checkoutTime");
    if (checkoutInput && !checkoutInput.value) {
      checkoutInput.value = checkoutDate.toISOString().slice(0, 16);
    }
  }
>>>>>>> main

  // Cập nhật tổng kết ban đầu
  updateSummary();
});
