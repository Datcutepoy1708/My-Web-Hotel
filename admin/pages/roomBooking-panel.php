<div class="content-card">
    <div class="card-header-custom">
        <h3 class="card-title">Danh Sách Booking Phòng</h3>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="fas fa-plus"></i> Thêm Booking Phòng
        </button>
    </div>

    <!-- Filter -->
    <div class="filter-section">
        <form>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Tìm tên khách hoặc phòng..." />
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option>Tất cả trạng thái</option>
                        <option>Chờ xác nhận</option>
                        <option>Đã xác nhận</option>
                        <option>Đã nhận phòng</option>
                        <option>Đã trả phòng</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option>Tất cả phòng</option>
                        <option>Phòng Đơn</option>
                        <option>Phòng đôi</option>
                        <option>phòng vip</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Khách</th>
                    <th>Phòng</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Số Khách</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>#BK001</td>
                    <td><strong>Nguyễn Văn A</strong><br><small>0901234567</small></td>
                    <td><span class="badge bg-secondary">101 - Deluxe</span></td>
                    <td>15/11/2025</td>
                    <td>18/11/2025</td>
                    <td>2 người</td>
                    <td><strong>3,500,000 VNĐ</strong></td>
                    <td><span class="badge-status badge-pending">Chờ xác nhận</span></td>
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
                <tr>
                    <td>#BK002</td>
                    <td><strong>Trần Thị B</strong><br><small>0912345678</small></td>
                    <td><span class="badge bg-secondary">205 - Suite</span></td>
                    <td>16/11/2025</td>
                    <td>20/11/2025</td>
                    <td>4 người</td>
                    <td><strong>6,800,000 VNĐ</strong></td>
                    <td><span class="badge-status badge-approved">Đã xác nhận</span></td>
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
                <tr>
                    <td>#BK003</td>
                    <td><strong>Lê Văn C</strong><br><small>0923456789</small></td>
                    <td><span class="badge bg-secondary">310 - Family</span></td>
                    <td>17/11/2025</td>
                    <td>19/11/2025</td>
                    <td>2 người</td>
                    <td><strong>2,800,000 VNĐ</strong></td>
                    <td><span class="badge-status badge-checkedin">Đã nhận phòng</span></td>
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
            <li class="page-item disabled"><a class="page-link">Trước</a></li>
            <li class="page-item active"><a class="page-link">1</a></li>
            <li class="page-item"><a class="page-link">2</a></li>
            <li class="page-item"><a class="page-link">3</a></li>
            <li class="page-item"><a class="page-link">Sau</a></li>
        </ul>
    </nav>
</div>
<!-- Modal Thêm Booking Phòng -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Booking Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên Khách Hàng *</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Điện Thoại *</label>
                            <input type="tel" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Phòng *</label>
                            <select class="form-select" required>
                                <option>-- Chọn phòng --</option>
                                <option>101 - Deluxe Room</option>
                                <option>205 - Suite Room</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Khách *</label>
                            <input type="number" class="form-control" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Check-in *</label>
                            <input type="date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Check-out *</label>
                            <input type="date" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tổng Tiền (VNĐ) *</label>
                            <input type="number" class="form-control" step="1000" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng Thái *</label>
                            <select class="form-select" required>
                                <option>Chờ xác nhận</option>
                                <option>Đã xác nhận</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi Chú</label>
                        <textarea class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-primary-custom">Thêm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>