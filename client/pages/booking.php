<?php
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['logged_in']);
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

if (isset($_GET['id'])) {
    $roomId = $_GET['id'];

    // Lấy thông tin phòng
    $stmt = $mysqli->prepare("SELECT r.room_id, r.status, r.room_number,
               rt.room_type_name AS room_type, 
               rt.base_price AS room_price, 
               rt.description AS `desc`, 
               rt.capacity,
               (SELECT url_image 
                FROM room_images 
                WHERE room_images.room_id = r.room_id 
                LIMIT 1) AS room_image 
        FROM room r
        JOIN room_type rt ON rt.room_type_id = r.room_type_id
        WHERE r.room_id = ?");
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $roomResult = $stmt->get_result();
    $room = $roomResult->fetch_assoc();
    if (!$room) {
        header("Location: /My-Web-Hotel/client/index.php?page=404");
        exit;
    }
}
?>
<main>
    <div class="container">
        <!-- FORM ĐẶT PHÒNG -->
        <div class="booking-form" id="bookingForm">
            <div class="header">
                <h1>Booking OceanPearl</h1>
                <p>Điền thông tin để hoàn tất đặt phòng</p>
            </div>

            <form autocomplete="off" id="roomBookingForm" style="padding: 40px">
                <!-- Thông tin phòng -->
                <div class="form-section">
                    <div class="section-title">Thông Tin Phòng</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="roomName">Tên phòng</label>
                            <input type="text" id="roomName" name="roomName" value="<?= $room['room_number'] ?>"
                                readonly style="background-color: #f5f5f5;" />
                        </div>
                        <div class="form-group">
                            <label for="roomType">Loại phòng</label>
                            <input type="text" id="roomType" name="roomType" value="<?= $room['room_type'] ?>" readonly
                                style="background-color: #f5f5f5;" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="guestCount">Số người lớn<span class="required">*</span></label>
                            <input type="number" id="guestCount" name="guestCount" min="1" max="10" required
                                placeholder="Nhập số người" />
                        </div>
                        <div class="form-group">
                            <label for="childCount">Số trẻ em <span class="required">*</span></label>
                            <input type="number" id="childCount" name="childCount" min="0" max="10" required
                                placeholder="Nhập số người" />
                        </div>
                    </div>
                </div>

                <!-- Thời gian -->
                <div class="form-section">
                    <div class="section-title">Thời Gian Lưu Trú</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="checkinTime">Check-in <span class="required">*</span></label>
                            <input type="datetime-local" id="checkinTime" name="checkinTime" required />
                        </div>
                        <div class="form-group">
                            <label for="checkoutTime">Check-out <span class="required">*</span></label>
                            <input type="datetime-local" id="checkoutTime" name="checkoutTime" required />
                        </div>
                    </div>
                    <div class="info-box"
                        style="margin-top: 15px; padding: 10px; background: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 4px;">
                        <i class="fa-solid fa-moon" style="color: #4caf50; margin-right: 8px;"></i>
                        <span style="color: #2e7d32; font-weight: 500;">Số đêm: <span id="nightCountDisplay">0</span>
                            đêm</span>
                    </div>
                </div>

                <!-- Giá và dịch vụ -->
                <div class="form-section">
                    <div class="section-title">Giá Phòng & Dịch Vụ</div>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="roomPrice">Giá phòng/đêm (VNĐ)</label>
                            <input type="text" id="roomPrice" name="roomPrice" min="0"
                                value="<?= number_format($room["room_price"]) ?>" readonly
                                style="background-color: #f5f5f5;" />
                        </div>
                    </div>

                    <!-- Dịch vụ kèm theo -->
                    <div style="margin-top: 20px">
                        <label style="font-weight: 600; color: #333; margin-bottom: 15px; display: block;">
                            Dịch vụ kèm theo (tùy chọn)
                        </label>
                        <div class="service-item">
                            <input type="checkbox" id="service1" name="services" value="breakfast"
                                data-price="200000" />
                            <label for="service1">
                                <span class="service-name">Buffet sáng</span>
                                <span class="service-price">200,000 ₫/ngày</span>
                            </label>
                        </div>
                        <div class="service-item">
                            <input type="checkbox" id="service2" name="services" value="spa" data-price="500000" />
                            <label for="service2">
                                <span class="service-name">Spa & Massage</span>
                                <span class="service-price">500,000 ₫</span>
                            </label>
                        </div>
                        <div class="service-item">
                            <input type="checkbox" id="service3" name="services" value="airport" data-price="300000" />
                            <label for="service3">
                                <span class="service-name">Đưa đón sân bay</span>
                                <span class="service-price">300,000 ₫</span>
                            </label>
                        </div>
                        <div class="service-item">
                            <input type="checkbox" id="service4" name="services" value="laundry" data-price="150000" />
                            <label for="service4">
                                <span class="service-name">Giặt là</span>
                                <span class="service-price">150,000 ₫</span>
                            </label>
                        </div>
                        <div class="service-item">
                            <input type="checkbox" id="service5" name="services" value="minibar" data-price="100000" />
                            <label for="service5">
                                <span class="service-name">Minibar (nước ngọt, snack)</span>
                                <span class="service-price">100,000 ₫/ngày</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Mã khuyến mãi -->
                <div class="form-section">
                    <div class="section-title">Mã Khuyến Mãi</div>
                    <div class="form-group full-width">
                        <label for="promoCode">Nhập mã khuyến mãi</label>
                        <div class="promo-input-group">
                            <input type="text" id="promoCode" name="promoCode" placeholder="VD: SUMMER2025, VIP20" />
                            <button type="button" class="btn-apply-promo" onclick="applyPromoCode()">Áp
                                dụng</button>
                        </div>
                        <div class="discount-display" id="discountDisplay">
                            Giảm giá: <span id="discountAmount">0 ₫</span>
                        </div>
                    </div>
                </div>

                <!-- Tổng kết thanh toán -->
                <div class="total-summary">
                    <div class="summary-row">
                        <span>Giá phòng:</span>
                        <span id="summaryRoomPrice">0 ₫</span>
                    </div>
                    <div class="summary-row">
                        <span>Dịch vụ kèm:</span>
                        <span id="summaryServicePrice">0 ₫</span>
                    </div>
                    <div class="summary-row">
                        <span>Giảm giá:</span>
                        <span id="summaryDiscount">0 ₫</span>
                    </div>
                    <div class="summary-row highlight">
                        <span>Tổng cộng:</span>
                        <span id="summaryTotal">0 ₫</span>
                    </div>
                    <div class="summary-row"
                        style="border-top: 1px solid rgba(255, 255, 255, 0.2); margin-top: 10px; padding-top: 10px;">
                        <span>Tiền cọc (30%):</span>
                        <span id="summaryDeposit">0 ₫</span>
                    </div>
                </div>

                <!-- Thanh toán -->
                <div class="form-section">
                    <div class="section-title">Phương Thức Thanh Toán</div>
                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="paymentMethod">Chọn phương thức <span class="required">*</span></label>
                            <select id="paymentMethod" name="paymentMethod" required>
                                <option value="">-- Chọn phương thức --</option>
                                <option value="Thẻ tín dụng Visa">Thẻ tín dụng Visa</option>
                                <option value="Thẻ tín dụng Mastercard">Thẻ tín dụng Mastercard</option>
                                <option value="Chuyển khoản ngân hàng">Chuyển khoản ngân hàng</option>
                                <option value="Ví điện tử MoMo">Ví điện tử MoMo</option>
                                <option value="Ví điện tử ZaloPay">Ví điện tử ZaloPay</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Ghi chú -->
                <div class="form-section">
                    <div class="section-title">Ghi Chú</div>
                    <div class="form-group full-width">
                        <label for="notes">Ghi chú đặc biệt</label>
                        <textarea id="notes" name="notes"
                            placeholder="VD: Yêu cầu phòng tầng cao, không hút thuốc..."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Tạo Hóa Đơn</button>
            </form>
        </div>

        <!-- HÓA ĐƠN -->
        <div class="invoice-container" id="invoiceContainer">
            <div class="header">
                <h1>Booking OceanPearl</h1>
                <p>Cảm ơn quý khách đã tin tưởng sử dụng dịch vụ</p>
            </div>

            <div class="content">
                <!-- Thông tin phòng -->
                <div class="section">
                    <div class="invoice-section-title">Thông Tin Phòng</div>
                    <div class="info-row">
                        <div class="room-name" id="invoiceRoomName">-</div>
                        <span class="room-type" id="invoiceRoomType">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số người lớn:</span>
                        <span class="info-value" id="invoiceGuestCount">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số trẻ em:</span>
                        <span class="info-value" id="invoiceChildCount">-</span>
                    </div>
                </div>

                <!-- Thời gian -->
                <div class="section">
                    <div class="invoice-section-title">Thời Gian Lưu Trú</div>
                    <div class="info-row">
                        <span class="info-label">Check-in:</span>
                        <span class="info-value" id="invoiceCheckinTime">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Check-out:</span>
                        <span class="info-value" id="invoiceCheckoutTime">-</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số đêm:</span>
                        <span class="info-value" id="invoiceNightCount">-</span>
                    </div>
                </div>

                <!-- Dịch vụ -->
                <div class="section" id="invoiceServicesSection" style="display: none">
                    <div class="invoice-section-title">Dịch Vụ Kèm Theo</div>
                    <div class="service-list" id="invoiceServicesList"></div>
                </div>

                <!-- Khuyến mãi -->
                <div class="section" id="invoicePromotionSection" style="display: none">
                    <div class="invoice-section-title">Khuyến Mãi</div>
                    <span class="promotion-tag" id="invoicePromotion">-</span>
                </div>

                <!-- Ghi chú -->
                <div class="section" id="invoiceNotesSection" style="display: none">
                    <div class="invoice-section-title">Ghi Chú</div>
                    <div class="notes-box" id="invoiceNotes">-</div>
                </div>

                <!-- Chi tiết thanh toán -->
                <div class="section">
                    <div class="invoice-section-title">Chi Tiết Thanh Toán</div>
                    <div class="payment-summary">
                        <div class="payment-row">
                            <span>Giá phòng:</span>
                            <span id="invoiceRoomPrice">-</span>
                        </div>
                        <div class="payment-row" id="invoiceServicePriceRow" style="display: none">
                            <span>Dịch vụ kèm:</span>
                            <span id="invoiceServicePrice">-</span>
                        </div>
                        <div class="payment-row" id="invoiceDiscountRow" style="display: none">
                            <span>Giảm giá:</span>
                            <span style="color: #ff6b6b" id="invoiceDiscount">-</span>
                        </div>
                        <div class="total-row">
                            <span>Tổng thanh toán:</span>
                            <span id="invoiceTotalAmount">-</span>
                        </div>
                        <div class="payment-row"
                            style="border-top: 2px solid #e0e0e0; margin-top: 10px; padding-top: 10px;">
                            <span>Tiền cọc (30%):</span>
                            <span style="color: #deb666; font-weight: 700" id="invoiceDeposit">-</span>
                        </div>
                    </div>
                    <div style="margin-top: 20px">
                        <div class="info-label" style="margin-bottom: 10px">Phương thức thanh toán:</div>
                        <span class="payment-method" id="invoicePaymentMethod">-</span>
                    </div>
                </div>

                <div class="button-group">
                    <button class="btn btn-secondary" onclick="editBooking()">Chỉnh sửa</button>
                    <button class="btn btn-secondary" onclick="saveInvoice()">Lưu hóa đơn</button>
                    <button class="btn btn-primary" onclick="confirmPayment()">Xác nhận thanh toán</button>
                </div>
            </div>

            <div class="footer">
                <p>Mã đặt phòng: <strong id="bookingCode">-</strong></p>
                <p style="margin-top: 5px">Vui lòng giữ lại hóa đơn này để làm thủ tục nhận phòng</p>
            </div>
        </div>
    </div>
</main>