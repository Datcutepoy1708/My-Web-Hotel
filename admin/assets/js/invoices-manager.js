// JavaScript functions for invoice management
function editInvoice(invoiceId) {
  // Logic to edit invoice
  console.log("Edit invoice:", invoiceId);
  window.location.href = 'index.php?page=invoice-manager&action=edit&id=' + invoiceId;
}

function deleteInvoice(invoiceId) {
  if (confirm("Bạn có chắc chắn muốn xóa hóa đơn này?")) {
    // Logic to delete invoice
    console.log("Delete invoice:", invoiceId);
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = '<input type="hidden" name="invoice_id" value="' + invoiceId + '">' +
                   '<input type="hidden" name="delete_invoice" value="1">';
    document.body.appendChild(form);
    form.submit();
  }
}

function viewInvoice(invoiceId) {
  // Lấy dữ liệu từ bảng và đổ vào modal xem chi tiết
  const row = document.querySelector(`tr[data-invoice-id="${invoiceId}"]`);
  
  if (!row) {
    console.error("Không tìm thấy hóa đơn:", invoiceId);
    return;
  }

  // Lấy dữ liệu từ các cell của hàng
  const invoiceId_val = row.cells[0]?.textContent || "-";
  const bookingId = row.cells[1]?.textContent || "-";
  const customerName = row.cells[2]?.textContent || "-";
  const roomCharge = row.cells[3]?.textContent || "0 VNĐ";
  const serviceCharge = row.cells[4]?.textContent || "0 VNĐ";
  const totalAmount = row.cells[5]?.textContent || "0 VNĐ";
  const paymentMethod = row.cells[6]?.textContent || "-";
  const status = row.cells[7]?.textContent || "-";

  // Set text fields trong modal
  const viewInvoiceIdEl = document.getElementById("viewInvoiceId");
  const viewBookingIdEl = document.getElementById("viewBookingId");
  const viewCustomerNameEl = document.getElementById("viewCustomerName");
  const viewRoomChargeEl = document.getElementById("viewRoomCharge");
  const viewServiceChargeEl = document.getElementById("viewServiceCharge");
  const viewTotalAmountEl = document.getElementById("viewTotalAmount");
  const viewPaymentMethodEl = document.getElementById("viewPaymentMethod");
  const viewStatusEl = document.getElementById("viewStatus");

  if (viewInvoiceIdEl) viewInvoiceIdEl.textContent = invoiceId_val || "Chưa có mã";
  if (viewBookingIdEl) viewBookingIdEl.textContent = bookingId || "-";
  if (viewCustomerNameEl) viewCustomerNameEl.textContent = customerName || "-";
  if (viewRoomChargeEl) viewRoomChargeEl.textContent = roomCharge || "0 VNĐ";
  if (viewServiceChargeEl) viewServiceChargeEl.textContent = serviceCharge || "0 VNĐ";
  if (viewTotalAmountEl) viewTotalAmountEl.textContent = totalAmount || "0 VNĐ";
  if (viewPaymentMethodEl) viewPaymentMethodEl.textContent = paymentMethod || "-";
  if (viewStatusEl) viewStatusEl.textContent = status || "-";

  // Show modal
  const viewModal = new bootstrap.Modal(
    document.getElementById("viewInvoiceModal")
  );
  viewModal.show();
}

function saveInvoice() {
  // Logic to save invoice
  console.log("Save invoice");
  const addModal = bootstrap.Modal.getInstance(
    document.getElementById("addInvoiceModal")
  );
  addModal.hide();
}

function editInvoiceFromView() {
  const invoiceId = document.getElementById("viewInvoiceId").textContent;
  const viewModal = bootstrap.Modal.getInstance(
    document.getElementById("viewInvoiceModal")
  );
  viewModal.hide();
  editInvoice(invoiceId);
}

function resetInvoiceForm() {
  const form = document.getElementById("invoiceForm");
  if (form) {
    form.reset();
    document.getElementById("invoice_id").value = '';
    document.getElementById("modalTitle").textContent = 'Thêm Hóa Đơn';
    document.getElementById("submitBtn").textContent = 'Thêm Hóa Đơn';
    document.getElementById("submitBtn").name = 'add_invoice';
  }
}

// Search and filter functionality
if (document.getElementById("searchInput")) {
  document.getElementById("searchInput").addEventListener("input", function () {
    const searchTerm = this.value.toLowerCase();
    const table = document.getElementById("invoiceTable");
    if (!table) return;
    
    const rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) {
      const row = rows[i];
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(searchTerm) ? "" : "none";
    }
  });
}

if (document.getElementById("statusFilter")) {
  document.getElementById("statusFilter").addEventListener("change", filterInvoiceTable);
}

if (document.getElementById("paymentMethodFilter")) {
  document.getElementById("paymentMethodFilter").addEventListener("change", filterInvoiceTable);
}

function filterInvoiceTable() {
  const statusFilter = document.getElementById("statusFilter")?.value || "";
  const paymentMethodFilter = document.getElementById("paymentMethodFilter")?.value || "";
  const table = document.getElementById("invoiceTable");
  
  if (!table) return;
  
  const rows = table.getElementsByTagName("tr");

  for (let i = 1; i < rows.length; i++) {
    const row = rows[i];
    const statusText = row.cells[7]?.textContent.toLowerCase() || "";
    const paymentMethodText = row.cells[6]?.textContent.toLowerCase() || "";

    let showRow = true;

    // Lọc theo tình trạng thanh toán
    if (statusFilter) {
      if (statusFilter === "paid" && !statusText.includes("đã thanh toán")) {
        showRow = false;
      } else if (statusFilter === "unpaid" && !statusText.includes("chưa thanh toán")) {
        showRow = false;
      } else if (statusFilter === "partial" && !statusText.includes("thanh toán một phần")) {
        showRow = false;
      }
    }

    // Lọc theo hình thức thanh toán
    if (paymentMethodFilter) {
      if (paymentMethodFilter === "cash" && !paymentMethodText.includes("cash")) {
        showRow = false;
      } else if (paymentMethodFilter === "bank" && !paymentMethodText.includes("bank")) {
        showRow = false;
      } else if (paymentMethodFilter === "card" && !paymentMethodText.includes("card")) {
        showRow = false;
      } else if (paymentMethodFilter === "ewallet" && !paymentMethodText.includes("e-wallet")) {
        showRow = false;
      }
    }

    row.style.display = showRow ? "" : "none";
  }
}

if (document.getElementById("resetFilter")) {
  document.getElementById("resetFilter").addEventListener("click", function () {
    const searchInput = document.getElementById("searchInput");
    if (searchInput) searchInput.value = "";
    
    const statusFilter = document.getElementById("statusFilter");
    if (statusFilter) statusFilter.value = "";
    
    const paymentMethodFilter = document.getElementById("paymentMethodFilter");
    if (paymentMethodFilter) paymentMethodFilter.value = "";

    const table = document.getElementById("invoiceTable");
    if (!table) return;
    
    const rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) {
      rows[i].style.display = "";
    }
  });
}

// Tính tổng tiền khi nhập phí
function calculateTotal() {
  const room = parseFloat(document.getElementById('room_charge')?.value) || 0;
  const service = parseFloat(document.getElementById('service_charge')?.value) || 0;
  const vat = parseFloat(document.getElementById('vat')?.value) || 0;
  const other = parseFloat(document.getElementById('other_fees')?.value) || 0;
  const total = room + service + vat + other;
  const totalField = document.getElementById('total_amount');
  if (totalField) {
    totalField.value = total.toFixed(2);
  }
}

// Gán sự kiện tính toán khi thay đổi các trường
document.addEventListener('DOMContentLoaded', function() {
  const roomChargeField = document.getElementById('room_charge');
  const serviceChargeField = document.getElementById('service_charge');
  const vatField = document.getElementById('vat');
  const otherFeesField = document.getElementById('other_fees');

  if (roomChargeField) roomChargeField.addEventListener('change', calculateTotal);
  if (serviceChargeField) serviceChargeField.addEventListener('change', calculateTotal);
  if (vatField) vatField.addEventListener('change', calculateTotal);
  if (otherFeesField) otherFeesField.addEventListener('change', calculateTotal);
});
