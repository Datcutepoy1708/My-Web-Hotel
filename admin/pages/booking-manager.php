    
    <div class="main-content">
        <div class="content-header">
            <h1>Quản Lý Booking</h1>
            <div class="row m-3">
                <div class="col-md-12">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookingModal">
                        <i class="fas fa-plus"></i> Thêm Booking
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Tìm tên khách hoặc phòng...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="Pending">Chờ xác nhận</option>
                            <option value="Confirmed">Đã xác nhận</option>
                            <option value="CheckedIn">Đã nhận phòng</option>
                            <option value="CheckedOut">Đã trả phòng</option>
                            <option value="Cancelled">Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="type">
                            <option value="">Tất cả loại</option>
                            <option value="room">Phòng</option>
                            <option value="service">Dịch vụ</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Loại</th>
                        <th>Tên Khách</th>
                        <th>Chi Tiết</th>
                        <th>Ngày/Giờ</th>
                        <th>Số Người</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Booking Phòng -->
                    <tr>
                        <td>1</td>
                        <td><span class="badge bg-primary"><i class="fas fa-door-open"></i> Phòng</span></td>
                        <td><strong>Nguyễn Văn A</strong><br><small>0901234567</small></td>
                        <td>
                            <span class="badge bg-secondary">Phòng 101</span><br>
                            <small>Deluxe Room</small>
                        </td>
                        <td>
                            <small>Check-in: 15/11/2025</small><br>
                            <small>Check-out: 18/11/2025</small>
                        </td>
                        <td>2 người</td>
                        <td>3,500,000 VNĐ</td>
                        <td><span class="badge bg-warning">Chờ xác nhận</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" title="Xem chi tiết" data-bs-toggle="modal"
                                data-bs-target="#viewBookingDetailModal" data-booking-id="1">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Booking Dịch Vụ -->
                    <tr>
                        <td>2</td>
                        <td><span class="badge bg-success"><i class="fas fa-concierge-bell"></i> Dịch vụ</span></td>
                        <td><strong>Trần Thị B</strong><br><small>0912345678</small></td>
                        <td>
                            <span class="badge bg-info">Spa & Massage</span><br>
                            <small>Gói VIP 90 phút</small>
                        </td>
                        <td>
                            <small>Ngày: 16/11/2025</small><br>
                            <small>Giờ: 14:00</small>
                        </td>
                        <td>1 người</td>
                        <td>1,200,000 VNĐ</td>
                        <td><span class="badge bg-success">Đã xác nhận</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" title="Xem chi tiết" data-bs-toggle="modal"
                                data-bs-target="#viewBookingDetailModal" data-booking-id="2">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Booking Phòng -->
                    <tr>
                        <td>3</td>
                        <td><span class="badge bg-primary"><i class="fas fa-door-open"></i> Phòng</span></td>
                        <td><strong>Lê Văn C</strong><br><small>0923456789</small></td>
                        <td>
                            <span class="badge bg-secondary">Phòng 310</span><br>
                            <small>Suite Room</small>
                        </td>
                        <td>
                            <small>Check-in: 17/11/2025</small><br>
                            <small>Check-out: 19/11/2025</small>
                        </td>
                        <td>2 người</td>
                        <td>2,800,000 VNĐ</td>
                        <td><span class="badge bg-info">Đã nhận phòng</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Booking Dịch Vụ -->
                    <tr>
                        <td>4</td>
                        <td><span class="badge bg-success"><i class="fas fa-concierge-bell"></i> Dịch vụ</span></td>
                        <td><strong>Phạm Thị D</strong><br><small>0934567890</small></td>
                        <td>
                            <span class="badge bg-info">Nhà Hàng</span><br>
                            <small>Tiệc sinh nhật</small>
                        </td>
                        <td>
                            <small>Ngày: 18/11/2025</small><br>
                            <small>Giờ: 19:00</small>
                        </td>
                        <td>10 người</td>
                        <td>8,500,000 VNĐ</td>
                        <td><span class="badge bg-success">Đã xác nhận</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Booking Phòng -->
                    <tr>
                        <td>5</td>
                        <td><span class="badge bg-primary"><i class="fas fa-door-open"></i> Phòng</span></td>
                        <td><strong>Hoàng Văn E</strong><br><small>0945678901</small></td>
                        <td>
                            <span class="badge bg-secondary">Phòng 202</span><br>
                            <small>Family Room</small>
                        </td>
                        <td>
                            <small>Check-in: 20/11/2025</small><br>
                            <small>Check-out: 25/11/2025</small>
                        </td>
                        <td>3 người</td>
                        <td>5,200,000 VNĐ</td>
                        <td><span class="badge bg-danger">Đã hủy</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Booking Dịch Vụ -->
                    <tr>
                        <td>6</td>
                        <td><span class="badge bg-success"><i class="fas fa-concierge-bell"></i> Dịch vụ</span></td>
                        <td><strong>Đỗ Thị F</strong><br><small>0956789012</small></td>
                        <td>
                            <span class="badge bg-info">Tour Du Lịch</span><br>
                            <small>Hạ Long 2N1Đ</small>
                        </td>
                        <td>
                            <small>Ngày: 22/11/2025</small><br>
                            <small>Giờ: 07:00</small>
                        </td>
                        <td>4 người</td>
                        <td>6,800,000 VNĐ</td>
                        <td><span class="badge bg-warning">Chờ xác nhận</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Booking Phòng -->
                    <tr>
                        <td>7</td>
                        <td><span class="badge bg-primary"><i class="fas fa-door-open"></i> Phòng</span></td>
                        <td><strong>Bùi Văn G</strong><br><small>0967890123</small></td>
                        <td>
                            <span class="badge bg-secondary">Phòng 405</span><br>
                            <small>Standard Room</small>
                        </td>
                        <td>
                            <small>Check-in: 10/11/2025</small><br>
                            <small>Check-out: 12/11/2025</small>
                        </td>
                        <td>1 người</td>
                        <td>1,500,000 VNĐ</td>
                        <td><span class="badge bg-secondary">Đã trả phòng</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Booking Dịch Vụ -->
                    <tr>
                        <td>8</td>
                        <td><span class="badge bg-success"><i class="fas fa-concierge-bell"></i> Dịch vụ</span></td>
                        <td><strong>Võ Thị H</strong><br><small>0978901234</small></td>
                        <td>
                            <span class="badge bg-info">Gym & Fitness</span><br>
                            <small>Gói 1 tháng</small>
                        </td>
                        <td>
                            <small>Bắt đầu: 01/12/2025</small><br>
                            <small>Kết thúc: 31/12/2025</small>
                        </td>
                        <td>1 người</td>
                        <td>2,500,000 VNĐ</td>
                        <td><span class="badge bg-success">Đã xác nhận</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" title="Sửa">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#">Trước</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Sau</a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Modal Thêm/Sửa Booking -->
    <div class="modal fade" id="addBookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form>
                    <div class="modal-body">
                        <!-- Chọn loại booking -->
                        <div class="mb-4">
                            <label class="form-label">Loại Booking *</label>
                            <select class="form-select" id="bookingType" name="booking_type" required
                                onchange="toggleBookingFields()">
                                <option value="">-- Chọn loại booking --</option>
                                <option value="room">Đặt Phòng</option>
                                <option value="service">Đặt Dịch Vụ</option>
                            </select>
                        </div>

                        <!-- Thông tin chung -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tên Khách Hàng *</label>
                                <input type="text" class="form-control" name="guest_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số Điện Thoại *</label>
                                <input type="tel" class="form-control" name="phone" required>
                            </div>
                        </div>

                        <!-- Form đặt phòng -->
                        <div id="roomBookingFields" style="display: none;">
                            <h6 class="text-primary mb-3"><i class="fas fa-door-open"></i> Thông Tin Đặt Phòng</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số Phòng *</label>
                                    <select class="form-select" name="room_number">
                                        <option value="">-- Chọn phòng --</option>
                                        <option value="101">101 - Deluxe Room</option>
                                        <option value="102">102 - Standard Room</option>
                                        <option value="201">201 - Suite Room</option>
                                        <option value="202">202 - Family Room</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số Khách *</label>
                                    <input type="number" class="form-control" name="guests" min="1" value="1">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày Check-in *</label>
                                    <input type="date" class="form-control" name="check_in">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày Check-out *</label>
                                    <input type="date" class="form-control" name="check_out">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Yêu Cầu Đặc Biệt</label>
                                <textarea class="form-control" name="room_notes" rows="2"
                                    placeholder="VD: Tầng cao, view biển, giường đôi..."></textarea>
                            </div>
                        </div>

                        <!-- Form đặt dịch vụ -->
                        <div id="serviceBookingFields" style="display: none;">
                            <h6 class="text-success mb-3"><i class="fas fa-concierge-bell"></i> Thông Tin Đặt Dịch Vụ
                            </h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Loại Dịch Vụ *</label>
                                    <select class="form-select" name="service_type">
                                        <option value="">-- Chọn dịch vụ --</option>
                                        <option value="spa">Spa & Massage</option>
                                        <option value="restaurant">Nhà Hàng</option>
                                        <option value="gym">Gym & Fitness</option>
                                        <option value="tour">Tour Du Lịch</option>
                                        <option value="event">Tổ Chức Sự Kiện</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số Người *</label>
                                    <input type="number" class="form-control" name="service_guests" min="1" value="1">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày Sử Dụng *</label>
                                    <input type="date" class="form-control" name="service_date">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giờ Sử Dụng *</label>
                                    <input type="time" class="form-control" name="service_time">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ghi Chú Dịch Vụ</label>
                                <textarea class="form-control" name="service_notes" rows="2"
                                    placeholder="VD: Yêu cầu đặc biệt, số lượng khách, thời gian..."></textarea>
                            </div>
                        </div>

                        <!-- Thông tin chung cuối -->
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tổng Tiền (VNĐ) *</label>
                                <input type="number" class="form-control" name="total_price" step="1000" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng Thái *</label>
                                <select class="form-select" name="status" required>
                                    <option value="Pending" selected>Chờ xác nhận</option>
                                    <option value="Confirmed">Đã xác nhận</option>
                                    <option value="Completed">Hoàn thành</option>
                                    <option value="Cancelled">Đã hủy</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Thêm Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- modal xem chi tiết -->
    <div class="modal fade" id="viewBookingDetailModal" tabindex="-1" aria-labelledby="viewBookingDetailLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewBookingDetailLabel">Chi Tiết Booking ID: <span
                            id="detail-booking-id"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-secondary mb-3"><i class="fas fa-user"></i> Thông Tin Khách Hàng</h6>
                            <dl class="row detail-info-block">
                                <dt class="col-sm-4">Tên Khách:</dt>
                                <dd class="col-sm-8" id="detail-guest-name"></dd>

                                <dt class="col-sm-4">SĐT:</dt>
                                <dd class="col-sm-8" id="detail-guest-phone"></dd>

                                <dt class="col-sm-4">Tổng Tiền:</dt>
                                <dd class="col-sm-8" id="detail-total-price"></dd>

                                <dt class="col-sm-4">Trạng Thái:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge" id="detail-status-badge"></span>
                                </dd>
                            </dl>
                        </div>

                        <div class="col-md-6">
                            <div id="detail-room-info" style="display: none;">
                                <h6 class="text-primary mb-3"><i class="fas fa-door-open"></i> Chi Tiết Đặt Phòng</h6>
                                <dl class="row detail-info-block">
                                    <dt class="col-sm-5">Số/Loại Phòng:</dt>
                                    <dd class="col-sm-7" id="detail-room-type"></dd>

                                    <dt class="col-sm-5">Check-in:</dt>
                                    <dd class="col-sm-7" id="detail-check-in-date"></dd>

                                    <dt class="col-sm-5">Check-out:</dt>
                                    <dd class="col-sm-7" id="detail-check-out-date"></dd>

                                    <dt class="col-sm-5">Số Người:</dt>
                                    <dd class="col-sm-7" id="detail-room-guests"></dd>

                                    <dt class="col-sm-5">Yêu Cầu:</dt>
                                    <dd class="col-sm-7" id="detail-room-notes"></dd>
                                </dl>
                            </div>

                            <div id="detail-service-info" style="display: none;">
                                <h6 class="text-success mb-3"><i class="fas fa-concierge-bell"></i> Chi Tiết Dịch Vụ
                                </h6>
                                <dl class="row detail-info-block">
                                    <dt class="col-sm-5">Tên Dịch Vụ:</dt>
                                    <dd class="col-sm-7" id="detail-service-name"></dd>

                                    <dt class="col-sm-5">Mô tả:</dt>
                                    <dd class="col-sm-7" id="detail-service-description"></dd>

                                    <dt class="col-sm-5">Ngày/Giờ:</dt>
                                    <dd class="col-sm-7" id="detail-service-datetime"></dd>

                                    <dt class="col-sm-5">Số Người:</dt>
                                    <dd class="col-sm-7" id="detail-service-guests"></dd>

                                    <dt class="col-sm-5">Ghi Chú:</dt>
                                    <dd class="col-sm-7" id="detail-service-notes"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>