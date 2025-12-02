 <div class="container main-container">
     <!-- FORM ĐẶT DỊCH VỤ -->
     <div id="bookingForm">
         <div class="card">
             <div class="card-header-custom">
                 <h1><i class="bi bi-star-fill"></i> Đặt Dịch Vụ OceanPearl</h1>
                 <p>Hoàn tất thông tin để xác nhận đặt dịch vụ</p>
             </div>

             <div class="card-body p-4">
                 <!-- Thông tin dịch vụ đã chọn -->
                 <div class="service-highlight">
                     <div class="d-flex justify-content-between align-items-start">
                         <div>
                             <div class="service-name" id="serviceName">-</div>
                             <div class="service-description" id="serviceDescription">
                                 -
                             </div>
                         </div>
                         <div class="service-price" id="servicePrice">-</div>
                     </div>
                 </div>

                 <!-- Thông tin khách hàng -->
                 <div class="mb-4">
                     <div class="section-title">
                         <i class="bi bi-person-fill"></i>
                         Thông Tin Khách Hàng
                     </div>

                     <div class="row g-3">
                         <div class="col-md-6">
                             <label for="customerName" class="form-label">
                                 Họ và tên <span class="required">*</span>
                             </label>
                             <input type="text" class="form-control" id="customerName" placeholder="Nguyễn Văn A"
                                 required />
                         </div>
                         <div class="col-md-6">
                             <label for="customerPhone" class="form-label">
                                 Số điện thoại <span class="required">*</span>
                             </label>
                             <input type="tel" class="form-control" id="customerPhone" placeholder="0912345678"
                                 required />
                         </div>
                         <div class="col-12">
                             <label for="customerEmail" class="form-label">
                                 Email <span class="required">*</span>
                             </label>
                             <input type="email" class="form-control" id="customerEmail" placeholder="example@email.com"
                                 required />
                         </div>
                     </div>
                 </div>

                 <!-- Thời gian sử dụng dịch vụ -->
                 <div class="mb-4">
                     <div class="section-title">
                         <i class="bi bi-calendar-check"></i>
                         Thời Gian Sử Dụng
                     </div>

                     <div class="row g-3">
                         <div class="col-md-6">
                             <label for="serviceDate" class="form-label">
                                 Ngày sử dụng <span class="required">*</span>
                             </label>
                             <input type="date" class="form-control" id="serviceDate" required />
                         </div>
                         <div class="col-md-6">
                             <label for="serviceTime" class="form-label">
                                 Giờ sử dụng <span class="required">*</span>
                             </label>
                             <input type="time" class="form-control" id="serviceTime" required />
                         </div>
                     </div>
                 </div>

                 <!-- Chi tiết dịch vụ (động) -->
                 <div class="mb-4">
                     <div class="section-title">
                         <i class="bi bi-list-check"></i>
                         Chi Tiết Dịch Vụ
                     </div>
                     <div id="serviceDetailsContent"></div>
                 </div>

                 <!-- Tổng kết -->
                 <div class="summary-card">
                     <div class="summary-row">
                         <span><i class="bi bi-tag"></i> Giá dịch vụ:</span>
                         <span id="totalPrice">0 ₫</span>
                     </div>
                     <div class="summary-row">
                         <span><i class="bi bi-receipt"></i> VAT (10%):</span>
                         <span id="vatAmount">0 ₫</span>
                     </div>
                     <div class="summary-row total">
                         <span><i class="bi bi-cash-coin"></i> Tổng thanh toán:</span>
                         <span id="finalTotal">0 ₫</span>
                     </div>
                 </div>

                 <!-- Phương thức thanh toán -->
                 <div class="mb-4">
                     <div class="section-title">
                         <i class="bi bi-credit-card"></i>
                         Phương Thức Thanh Toán
                     </div>
                     <select class="form-select" id="paymentMethod" required>
                         <option value="">-- Chọn phương thức --</option>
                         <option value="Thẻ tín dụng Visa">Thẻ tín dụng Visa</option>
                         <option value="Thẻ tín dụng Mastercard">
                             Thẻ tín dụng Mastercard
                         </option>
                         <option value="Chuyển khoản ngân hàng">
                             Chuyển khoản ngân hàng
                         </option>
                         <option value="Ví điện tử MoMo">Ví điện tử MoMo</option>
                         <option value="Ví điện tử ZaloPay">
                             Ví điện tử ZaloPay
                         </option>
                     </select>
                 </div>

                 <!-- Ghi chú -->
                 <div class="mb-4">
                     <div class="section-title">
                         <i class="bi bi-chat-left-text"></i>
                         Ghi Chú
                     </div>
                     <textarea class="form-control" id="notes" rows="3"
                         placeholder="Vui lòng cho chúng tôi biết nếu bạn có yêu cầu đặc biệt..."></textarea>
                 </div>

                 <div class="alert alert-info-custom">
                     <i class="bi bi-info-circle-fill"></i>
                     <strong>Lưu ý:</strong> Vui lòng đến trước giờ hẹn 10 phút. Quý
                     khách có thể hủy hoặc đổi lịch miễn phí trước 24 giờ.
                 </div>

                 <button class="btn btn-submit" onclick="createBooking()">
                     <i class="bi bi-check-circle"></i> Xác Nhận Đặt Dịch Vụ
                 </button>
             </div>
         </div>
     </div>

     <!-- HÓA ĐƠN -->
     <div id="invoiceContainer" style="display: none">
         <div class="card">
             <div class="card-header-custom">
                 <h1><i class="bi bi-check-circle-fill"></i> OceanPearl Hotel</h1>
                 <p>Xác nhận đặt dịch vụ thành công</p>
             </div>

             <div class="card-body p-4">
                 <div class="booking-code">
                     <div class="booking-code-label">MÃ ĐẶT DỊCH VỤ</div>
                     <div class="booking-code-value" id="invoiceCode">-</div>
                 </div>

                 <!-- Thông tin dịch vụ -->
                 <div class="invoice-section">
                     <div class="invoice-section-title">
                         <i class="bi bi-star-fill"></i>
                         Dịch Vụ Đã Đặt
                     </div>
                     <div class="invoice-row">
                         <span class="invoice-label">Tên dịch vụ:</span>
                         <span class="invoice-value" id="invoiceServiceName">-</span>
                     </div>
                     <div id="invoiceServiceDetails"></div>
                 </div>

                 <!-- Thông tin khách hàng -->
                 <div class="invoice-section">
                     <div class="invoice-section-title">
                         <i class="bi bi-person-fill"></i>
                         Thông Tin Khách Hàng
                     </div>
                     <div class="invoice-row">
                         <span class="invoice-label">Họ tên:</span>
                         <span class="invoice-value" id="invoiceName">-</span>
                     </div>
                     <div class="invoice-row">
                         <span class="invoice-label">Số điện thoại:</span>
                         <span class="invoice-value" id="invoicePhone">-</span>
                     </div>
                     <div class="invoice-row">
                         <span class="invoice-label">Email:</span>
                         <span class="invoice-value" id="invoiceEmail">-</span>
                     </div>
                 </div>

                 <!-- Thời gian -->
                 <div class="invoice-section">
                     <div class="invoice-section-title">
                         <i class="bi bi-calendar-check"></i>
                         Thời Gian Sử Dụng
                     </div>
                     <div class="invoice-row">
                         <span class="invoice-label">Ngày:</span>
                         <span class="invoice-value" id="invoiceDate">-</span>
                     </div>
                     <div class="invoice-row">
                         <span class="invoice-label">Giờ:</span>
                         <span class="invoice-value" id="invoiceTime">-</span>
                     </div>
                 </div>

                 <!-- Thanh toán -->
                 <div class="invoice-section">
                     <div class="invoice-section-title">
                         <i class="bi bi-receipt"></i>
                         Chi Tiết Thanh Toán
                     </div>
                     <div class="invoice-row">
                         <span class="invoice-label">Giá dịch vụ:</span>
                         <span class="invoice-value" id="invoiceSubtotal">-</span>
                     </div>
                     <div class="invoice-row">
                         <span class="invoice-label">VAT (10%):</span>
                         <span class="invoice-value" id="invoiceVAT">-</span>
                     </div>
                     <div class="invoice-row" style="
                  border-top: 2px solid var(--gold);
                  margin-top: 10px;
                  padding-top: 10px;
                ">
                         <span class="invoice-label" style="color: var(--gold); font-weight: 700">Tổng thanh
                             toán:</span>
                         <span class="invoice-value" style="color: var(--gold); font-size: 1.25rem">
                             <span id="invoiceTotal">-</span>
                         </span>
                     </div>
                     <div class="invoice-row" style="margin-top: 10px">
                         <span class="invoice-label">Phương thức:</span>
                         <span class="invoice-value" id="invoicePayment">-</span>
                     </div>
                 </div>

                 <!-- Ghi chú -->
                 <div class="invoice-section" id="invoiceNotesSection" style="display: none">
                     <div class="invoice-section-title">
                         <i class="bi bi-chat-left-text"></i>
                         Ghi Chú
                     </div>
                     <div class="invoice-row">
                         <span class="invoice-value" id="invoiceNotes" style="color: #666">-</span>
                     </div>
                 </div>

                 <div class="row g-3 mt-3">
                     <div class="col-md-4">
                         <button class="btn btn-outline-gold w-100" onclick="editBooking()">
                             <i class="bi bi-pencil"></i> Chỉnh sửa
                         </button>
                     </div>
                     <div class="col-md-4">
                         <button class="btn btn-outline-gold w-100" onclick="saveInvoice()">
                             <i class="bi bi-download"></i> Lưu hóa đơn
                         </button>
                     </div>
                     <div class="col-md-4">
                         <button class="btn btn-gold w-100" onclick="confirmPayment()">
                             <i class="bi bi-check-circle"></i> Xác nhận
                         </button>
                     </div>
                 </div>

                 <div class="alert alert-info-custom mt-3">
                     <i class="bi bi-shield-check"></i>
                     <strong>Chính sách hủy:</strong> Miễn phí hủy trước 24 giờ. Hủy
                     trong vòng 24 giờ sẽ bị tính phí 50%.
                 </div>
             </div>
         </div>
     </div>
 </div>