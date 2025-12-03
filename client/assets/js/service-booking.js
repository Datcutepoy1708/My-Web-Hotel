// Danh sách dịch vụ
const services = {
  spa: {
    name: "Spa & Massage",
    basePrice: 500000,
    description:
      "Thư giãn toàn thân với các liệu pháp massage chuyên nghiệp, sử dụng tinh dầu thiên nhiên cao cấp",
    details: `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Loại massage <span class="required">*</span></label>
                            <select class="form-select" id="spaType" onchange="updateTotal()">
                                <option value="Swedish Massage">Swedish Massage (60 phút)</option>
                                <option value="Thai Massage">Thai Massage (90 phút)</option>
                                <option value="Hot Stone">Hot Stone (90 phút)</option>
                                <option value="Aromatherapy">Aromatherapy (60 phút)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số người <span class="required">*</span></label>
                            <input type="number" class="form-control" id="spaGuests" min="1" max="4" value="1" onchange="updateTotal()">
                        </div>
                    </div>
                `,
  },
  restaurant: {
    name: "Nhà Hàng & Bar",
    basePrice: 800000,
    description:
      "Thưởng thức các món ăn hải sản tươi sống và các loại cocktail đặc biệt tại nhà hàng sang trọng bên bờ biển",
    details: `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Loại bữa ăn <span class="required">*</span></label>
                            <select class="form-select" id="mealType" onchange="updateTotal()">
                                <option value="Buffet sáng">🌅 Buffet sáng (6:00 - 10:00)</option>
                                <option value="Set lunch">🌤️ Set lunch (11:30 - 14:00)</option>
                                <option value="Set dinner">🌙 Set dinner (18:00 - 22:00)</option>
                                <option value="À la carte">📋 À la carte</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số người <span class="required">*</span></label>
                            <input type="number" class="form-control" id="restaurantGuests" min="1" max="20" value="2" onchange="updateTotal()">
                        </div>
                    </div>
                `,
  },
  airport: {
    name: "Đưa Đón Sân Bay",
    basePrice: 300000,
    description:
      "Dịch vụ đưa đón tận nơi với xe hạng sang, tài xế chuyên nghiệp, đảm bảo an toàn và đúng giờ",
    details: `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Loại xe <span class="required">*</span></label>
                            <select class="form-select" id="carType" onchange="updateTotal()">
                                <option value="Sedan 4 chỗ">🚗 Sedan 4 chỗ</option>
                                <option value="SUV 7 chỗ">🚙 SUV 7 chỗ (+100,000 ₫)</option>
                                <option value="Limousine">🚐 Limousine (+300,000 ₫)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chiều <span class="required">*</span></label>
                            <select class="form-select" id="direction" onchange="updateTotal()">
                                <option value="Đón từ sân bay">✈️ Đón từ sân bay</option>
                                <option value="Đưa ra sân bay">🛫 Đưa ra sân bay</option>
                                <option value="Cả đi và về">🔄 Cả đi và về (x2 giá)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Số chuyến bay</label>
                            <input type="text" class="form-control" id="flightNumber" placeholder="VD: VN123">
                        </div>
                    </div>
                `,
  },
  meeting: {
    name: "Phòng Hội Nghị",
    basePrice: 2000000,
    description:
      "Phòng họp hiện đại với đầy đủ thiết bị: máy chiếu, âm thanh, wifi tốc độ cao, phục vụ trà nước",
    details: `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Loại phòng <span class="required">*</span></label>
                            <select class="form-select" id="meetingType" onchange="updateTotal()">
                                <option value="Phòng nhỏ (10-20 người)">👥 Phòng nhỏ (10-20 người)</option>
                                <option value="Phòng trung (30-50 người)">👨‍👩‍👧‍👦 Phòng trung (30-50 người) (+500,000 ₫)</option>
                                <option value="Hội trường (100+ người)">🏛️ Hội trường (100+ người) (+1,500,000 ₫)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thời gian <span class="required">*</span></label>
                            <select class="form-select" id="meetingDuration" onchange="updateTotal()">
                                <option value="Nửa ngày (4 giờ)">⏰ Nửa ngày (4 giờ)</option>
                                <option value="Cả ngày (8 giờ)">🕐 Cả ngày (8 giờ) (+50%)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Số người dự kiến <span class="required">*</span></label>
                            <input type="number" class="form-control" id="meetingGuests" min="5" max="200" value="10" onchange="updateTotal()">
                        </div>
                    </div>
                `,
  },
  beach: {
    name: "Beach Club & Thể thao nước",
    basePrice: 400000,
    description:
      "Trải nghiệm các hoạt động thể thao nước: lặn biển, lướt ván, kayak, khu vực ghế bãi biển riêng",
    details: `
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Gói dịch vụ <span class="required">*</span></label>
                            <select class="form-select" id="beachPackage" onchange="updateTotal()">
                                <option value="Cơ bản">🏖️ Cơ bản (ghế + dù)</option>
                                <option value="Thể thao nước">🏄 Thể thao nước (+200,000 ₫)</option>
                                <option value="VIP Cabana">⭐ VIP Cabana (+500,000 ₫)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Số người <span class="required">*</span></label>
                            <input type="number" class="form-control" id="beachGuests" min="1" max="10" value="2" onchange="updateTotal()">
                        </div>
                    </div>
                `,
  },
  laundry: {
    name: "Giặt Là & Làm Đẹp",
    basePrice: 150000,
    description:
      "Dịch vụ giặt là chuyên nghiệp, làm tóc, làm nail tại salon tiêu chuẩn 5 sao",
    details: `
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Loại dịch vụ <span class="required">*</span></label>
                            <select class="form-select" id="laundryType" onchange="updateTotal()">
                                <option value="Giặt là thường">👔 Giặt là thường</option>
                                <option value="Giặt hấp cao cấp">✨ Giặt hấp cao cấp (+50,000 ₫)</option>
                                <option value="Làm tóc">💇 Làm tóc (+300,000 ₫)</option>
                                <option value="Làm nail">💅 Làm nail (+200,000 ₫)</option>
                            </select>
                        </div>
                    </div>
                `,
  },
};

let currentService = null;

// Khởi tạo trang
function initPage() {
  const urlParams = new URLSearchParams(window.location.search);
  const serviceId = urlParams.get("service") || "spa";

  currentService = services[serviceId];

  if (currentService) {
    document.getElementById("serviceName").textContent = currentService.name;
    document.getElementById("servicePrice").textContent = formatCurrency(
      currentService.basePrice
    );
    document.getElementById("serviceDescription").textContent =
      currentService.description;
    document.getElementById("serviceDetailsContent").innerHTML =
      currentService.details;
    updateTotal();
  }

  document.getElementById("serviceDate").min = new Date()
    .toISOString()
    .split("T")[0];
}

// Tính toán giá dịch vụ
function calculateServicePrice() {
  if (!currentService) return 0;

  let price = currentService.basePrice;
  const serviceId = Object.keys(services).find(
    (key) => services[key].name === currentService.name
  );

  switch (serviceId) {
    case "spa":
      const spaGuests =
        parseInt(document.getElementById("spaGuests")?.value) || 1;
      price *= spaGuests;
      break;

    case "restaurant":
      const restaurantGuests =
        parseInt(document.getElementById("restaurantGuests")?.value) || 2;
      price *= restaurantGuests;
      break;

    case "airport":
      const carType = document.getElementById("carType")?.value || "";
      if (carType === "SUV 7 chỗ") price += 100000;
      if (carType === "Limousine") price += 300000;

      const direction = document.getElementById("direction")?.value || "";
      if (direction === "Cả đi và về") price *= 2;
      break;

    case "meeting":
      const meetingType = document.getElementById("meetingType")?.value || "";
      if (meetingType === "Phòng trung (30-50 người)") price += 500000;
      if (meetingType === "Hội trường (100+ người)") price += 1500000;

      const duration = document.getElementById("meetingDuration")?.value || "";
      if (duration === "Cả ngày (8 giờ)") price *= 1.5;
      break;

    case "beach":
      const beachPackage = document.getElementById("beachPackage")?.value || "";
      if (beachPackage === "Thể thao nước") price += 200000;
      if (beachPackage === "VIP Cabana") price += 500000;

      const beachGuests =
        parseInt(document.getElementById("beachGuests")?.value) || 2;
      price *= beachGuests;
      break;

    case "laundry":
      const laundryType = document.getElementById("laundryType")?.value || "";
      if (laundryType === "Giặt hấp cao cấp") price += 50000;
      if (laundryType === "Làm tóc") price += 300000;
      if (laundryType === "Làm nail") price += 200000;
      break;
  }

  return price;
}

// Cập nhật tổng tiền
function updateTotal() {
  const subtotal = calculateServicePrice();
  const vat = subtotal * 0.1;
  const total = subtotal + vat;

  document.getElementById("totalPrice").textContent = formatCurrency(subtotal);
  document.getElementById("vatAmount").textContent = formatCurrency(vat);
  document.getElementById("finalTotal").textContent = formatCurrency(total);
}

// Format tiền tệ
function formatCurrency(amount) {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
}

// Lấy chi tiết dịch vụ đã chọn
function getServiceDetails() {
  const serviceId = Object.keys(services).find(
    (key) => services[key].name === currentService.name
  );
  let details = [];

  switch (serviceId) {
    case "spa":
      details.push({
        label: "Loại massage",
        value: document.getElementById("spaType")?.value || "-",
      });
      details.push({
        label: "Số người",
        value: document.getElementById("spaGuests")?.value || "1",
      });
      break;

    case "restaurant":
      details.push({
        label: "Loại bữa ăn",
        value: document.getElementById("mealType")?.value || "-",
      });
      details.push({
        label: "Số người",
        value: document.getElementById("restaurantGuests")?.value || "2",
      });
      break;

    case "airport":
      details.push({
        label: "Loại xe",
        value: document.getElementById("carType")?.value || "-",
      });
      details.push({
        label: "Chiều",
        value: document.getElementById("direction")?.value || "-",
      });
      const flightNum = document.getElementById("flightNumber")?.value;
      if (flightNum) {
        details.push({
          label: "Số chuyến bay",
          value: flightNum,
        });
      }
      break;

    case "meeting":
      details.push({
        label: "Loại phòng",
        value: document.getElementById("meetingType")?.value || "-",
      });
      details.push({
        label: "Thời gian",
        value: document.getElementById("meetingDuration")?.value || "-",
      });
      details.push({
        label: "Số người",
        value: document.getElementById("meetingGuests")?.value || "10",
      });
      break;

    case "beach":
      details.push({
        label: "Gói dịch vụ",
        value: document.getElementById("beachPackage")?.value || "-",
      });
      details.push({
        label: "Số người",
        value: document.getElementById("beachGuests")?.value || "2",
      });
      break;

    case "laundry":
      details.push({
        label: "Loại dịch vụ",
        value: document.getElementById("laundryType")?.value || "-",
      });
      break;
  }

  return details;
}

// Tạo booking
function createBooking() {
  const name = document.getElementById("customerName").value;
  const phone = document.getElementById("customerPhone").value;
  const email = document.getElementById("customerEmail").value;
  const date = document.getElementById("serviceDate").value;
  const time = document.getElementById("serviceTime").value;
  const payment = document.getElementById("paymentMethod").value;

  if (!name || !phone || !email || !date || !time || !payment) {
    alert("Vui lòng điền đầy đủ thông tin bắt buộc!");
    return;
  }

  const bookingCode = "SV" + Date.now().toString().slice(-8);
  const subtotal = calculateServicePrice();
  const vat = subtotal * 0.1;
  const total = subtotal + vat;

  document.getElementById("invoiceCode").textContent = bookingCode;
  document.getElementById("invoiceServiceName").textContent =
    currentService.name;
  document.getElementById("invoiceName").textContent = name;
  document.getElementById("invoicePhone").textContent = phone;
  document.getElementById("invoiceEmail").textContent = email;
  document.getElementById("invoiceDate").textContent = new Date(
    date
  ).toLocaleDateString("vi-VN");
  document.getElementById("invoiceTime").textContent = time;
  document.getElementById("invoiceSubtotal").textContent =
    formatCurrency(subtotal);
  document.getElementById("invoiceVAT").textContent = formatCurrency(vat);
  document.getElementById("invoiceTotal").textContent = formatCurrency(total);
  document.getElementById("invoicePayment").textContent = payment;

  const serviceDetails = getServiceDetails();
  let detailsHTML = "";
  serviceDetails.forEach((detail) => {
    detailsHTML += `
                    <div class="invoice-row">
                        <span class="invoice-label">${detail.label}:</span>
                        <span class="invoice-value">${detail.value}</span>
                    </div>
                `;
  });
  document.getElementById("invoiceServiceDetails").innerHTML = detailsHTML;

  const notes = document.getElementById("notes").value;
  if (notes) {
    document.getElementById("invoiceNotesSection").style.display = "block";
    document.getElementById("invoiceNotes").textContent = notes;
  } else {
    document.getElementById("invoiceNotesSection").style.display = "none";
  }

  document.getElementById("bookingForm").style.display = "none";
  document.getElementById("invoiceContainer").style.display = "block";
  window.scrollTo(0, 0);
}

// Chỉnh sửa booking
function editBooking() {
  document.getElementById("invoiceContainer").style.display = "none";
  document.getElementById("bookingForm").style.display = "block";
  window.scrollTo(0, 0);
}

// Lưu hóa đơn
function saveInvoice() {
  alert(
    "Hóa đơn đã được lưu! Chúng tôi đã gửi email xác nhận đến địa chỉ của bạn."
  );
}

// Xác nhận thanh toán
function confirmPayment() {
  const payment = document.getElementById("invoicePayment").textContent;
  if (payment === "Thanh toán tại khách sạn") {
    alert(
      "Đặt dịch vụ thành công! Vui lòng thanh toán tại quầy lễ tân khi sử dụng dịch vụ."
    );
  } else {
    alert("Đang chuyển đến cổng thanh toán...");
  }
}

window.addEventListener("DOMContentLoaded", initPage);
