<?php
$action = isset($_GET['action']) ? $_GET['action'] : '';
$message = '';
$messageType = '';

// thêm booking dịch vụ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_booking_service'])) {
        $customer_id = intval($_POST['customer_id']);
        $service_id = intval($_POST['service_id']);
        $quantity = intval($_POST['quantity']);
    }
}
?>

<div class="content-card">
    <div class="card-header-custom">
        <h3 class="card-title">Danh Sách Booking Dịch Vụ</h3>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="fas fa-plus"></i> Thêm Booking Dịch Vụ
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
                    <select class="form-select">
                        <option>Tất cả trạng thái</option>
                        <option>Chờ xác nhận</option>
                        <option>Đã xác nhận</option>
                        <option>Hoàn thành</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select">
                        <option>Tất cả dịch vụ</option>
                        <option>Spa & Massage</option>
                        <option>Nhà Hàng</option>
                        <option>Gym & Fitness</option>
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
                    <th>Dịch Vụ</th>
                    <th>Ngày</th>
                    <th>Giờ</th>
                    <th>Số Người</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>#SV001</td>
                    <td><strong>Phạm Thị D</strong><br><small>0934567890</small></td>
                    <td><span class="badge bg-info">Spa & Massage</span></td>
                    <td>16/11/2025</td>
                    <td>14:00</td>
                    <td>1 người</td>
                    <td><strong>1,200,000 VNĐ</strong></td>
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
                    <td>#SV002</td>
                    <td><strong>Hoàng Văn E</strong><br><small>0945678901</small></td>
                    <td><span class="badge bg-info">Nhà Hàng</span></td>
                    <td>18/11/2025</td>
                    <td>19:00</td>
                    <td>10 người</td>
                    <td><strong>8,500,000 VNĐ</strong></td>
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
                    <td>#SV003</td>
                    <td><strong>Đỗ Thị F</strong><br><small>0956789012</small></td>
                    <td><span class="badge bg-info">Tour Du Lịch</span></td>
                    <td>22/11/2025</td>
                    <td>07:00</td>
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
<!-- Modal Thêm Booking Dịch Vụ -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Booking Dịch Vụ</h5>
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
                            <label class="form-label">Loại Dịch Vụ *</label>
                            <select class="form-select" required>
                                <option>-- Chọn dịch vụ --</option>
                                <option>Spa & Massage</option>
                                <option>Nhà Hàng</option>
                                <option>Gym & Fitness</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số Người *</label>
                            <input type="number" class="form-control" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày Sử Dụng *</label>
                            <input type="date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giờ Sử Dụng *</label>
                            <input type="time" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"> Số lượng *</label>
                            <input type="number" class="form-control" required>
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