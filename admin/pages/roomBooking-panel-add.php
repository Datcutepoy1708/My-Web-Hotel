<?php
// Form thêm mới booking phòng - File riêng để tránh lẫn lộn với edit

// LẤY DANH SÁCH CUSTOMER
$customersResult = $mysqli->query(
    "SELECT customer_id, full_name, phone, email 
     FROM customer 
     WHERE deleted IS NULL 
     ORDER BY full_name ASC"
);
$customers = $customersResult->fetch_all(MYSQLI_ASSOC);

// LẤY DANH SÁCH PHÒNG AVAILABLE
$roomsResult = $mysqli->query(
    "SELECT r.room_id, r.room_number, rt.room_type_name, rt.base_price
     FROM room r
     JOIN room_type rt ON r.room_type_id = rt.room_type_id
     WHERE r.deleted IS NULL AND r.status = 'Available'
     ORDER BY r.room_number ASC"
);
$availableRooms = $roomsResult->fetch_all(MYSQLI_ASSOC);
?>

<!-- Modal Thêm Booking -->
<div class="modal fade" id="addRoomBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Booking Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="addBookingForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên Khách Hàng *</label>
                            <select class="form-select customer-search" name="customer_id" required id="customerSelectRoom">
                                <option value="">-- Chọn khách hàng --</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['customer_id']; ?>"
                                        data-phone="<?php echo h($customer['phone']); ?>"
                                        data-email="<?php echo h($customer['email']); ?>">
                                        <?php echo h($customer['full_name']); ?> - <?php echo h($customer['phone']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Điện Thoại *</label>
                            <input type="tel" class="form-control" name="phone" value="" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Chọn Phòng *</label>
                            <select class="form-select" name="room_id[]" id="room_id_select" multiple size="5" required>
                                <?php foreach ($availableRooms as $room): ?>
                                    <option value="<?php echo $room['room_id']; ?>">
                                        <?php echo h($room['room_number']); ?> - <?php echo h($room['room_type_name']); ?>
                                        (<?php echo number_format($room['base_price']); ?> VNĐ)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Giữ Ctrl (Windows) hoặc Cmd (Mac) để chọn nhiều phòng</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Khách *</label>
                            <input type="number" class="form-control" name="quantity" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Check-in *</label>
                            <input type="date" class="form-control" name="check_in_date" value="" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Check-out *</label>
                            <input type="date" class="form-control" name="check_out_date" value="" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng Thái *</label>
                            <select class="form-select" name="status" required>
                                <option value="Pending">Chờ xác nhận</option>
                                <option value="Confirmed">Đã xác nhận</option>
                                <option value="Completed">Đã hoàn thành</option>
                                <option value="Cancelled">Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phương Thức Đặt Phòng</label>
                            <select class="form-select" name="booking_method">
                                <option value="Website">Website</option>
                                <option value="Phone">Điện thoại</option>
                                <option value="Email">Email</option>
                                <option value="Walk-in">Trực tiếp</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tiền Cọc (VNĐ)</label>
                            <input type="number" class="form-control" name="deposit" min="0" step="0.01" value="" placeholder="Nhập số tiền cọc (nếu có)">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi Chú</label>
                        <textarea class="form-control" name="special_request" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="add_booking_room" class="btn-primary-custom">
                        <i class="fas fa-save"></i> Thêm Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .select2-search__field {
        width: 100% !important;
        border: none !important;
        outline: none !important;
        padding: 5px !important;
        margin: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    
    .select2-search__field:focus {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }
    
    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
    }
    
    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field:focus {
        border-color: #86b7fe !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill phone number when selecting customer
    const customerSelect = document.querySelector('#addBookingForm select[name="customer_id"]');
    const phoneInput = document.querySelector('#addBookingForm input[name="phone"]');

    if (customerSelect && phoneInput) {
        customerSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const phone = selectedOption.getAttribute('data-phone') || '';
            if (phone) {
                phoneInput.value = phone;
            }
        });
    }

    // Set minimum date for check-in to today
    const checkInDate = document.querySelector('#addBookingForm input[name="check_in_date"]');
    const checkOutDate = document.querySelector('#addBookingForm input[name="check_out_date"]');

    if (checkInDate) {
        const today = new Date().toISOString().split('T')[0];
        checkInDate.setAttribute('min', today);

        checkInDate.addEventListener('change', function() {
            if (checkOutDate) {
                checkOutDate.setAttribute('min', this.value);
                if (checkOutDate.value && checkOutDate.value <= this.value) {
                    checkOutDate.value = '';
                }
            }
        });
    }

    // Khởi tạo Select2 cho customer
    function initCustomerSelect2Room() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
            return false;
        }
        
        const $customerSelect = jQuery('#customerSelectRoom');
        if (!$customerSelect.length) {
            return false;
        }
        
        // Destroy nếu đã khởi tạo trước đó
        if ($customerSelect.hasClass('select2-hidden-accessible')) {
            $customerSelect.select2('destroy');
        }
        
        // Lấy modal để set dropdownParent
        const $modal = jQuery('#addRoomBookingModal');
        const dropdownParent = $modal.length ? $modal : jQuery('body');
        
        $customerSelect.select2({
            theme: 'bootstrap-5',
            placeholder: '-- Chọn khách hàng --',
            allowClear: true,
            minimumInputLength: 0,
            width: '100%',
            dropdownParent: dropdownParent,
            language: {
                noResults: function() {
                    return "Không tìm thấy khách hàng";
                },
                searching: function() {
                    return "Đang tìm kiếm...";
                }
            }
        });
        
        // Tự động điền số điện thoại khi chọn khách hàng
        $customerSelect.off('change.select2-customer').on('change.select2-customer', function() {
            const selectedOption = jQuery(this).find('option:selected');
            const phone = selectedOption.data('phone') || '';
            jQuery('#addBookingForm input[name="phone"]').val(phone);
        });
        
        // Đảm bảo input tìm kiếm có thể gõ được
        $customerSelect.on('select2:open', function() {
            setTimeout(function() {
                const $searchField = jQuery('.select2-search__field');
                $searchField.attr('placeholder', 'Gõ để tìm kiếm...');
                $searchField.prop('readonly', false);
                $searchField.prop('disabled', false);
                $searchField.focus();
            }, 100);
        });
        
        return true;
    }

    // Khởi tạo Select2 khi modal mở
    const modal = document.getElementById('addRoomBookingModal');
    if (modal) {
        modal.addEventListener('shown.bs.modal', function() {
            setTimeout(initCustomerSelect2Room, 200);
        });
    }
    
    // Khởi tạo lần đầu nếu không trong modal
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function() {
            setTimeout(initCustomerSelect2Room, 300);
        });
    }

    // Reset form khi modal đóng
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            const form = document.getElementById('addBookingForm');
            if (form) {
                form.reset();
                // Reset Select2
                if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                    const $customerSelect = jQuery('#customerSelectRoom');
                    if ($customerSelect.length) {
                        $customerSelect.val(null).trigger('change');
                    }
                }
            }
        });
    }
});
</script>









