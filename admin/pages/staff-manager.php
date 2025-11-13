<div class="main-content">
    <div class="content-header">
        <h1>Quản Lý Nhân Viên</h1>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Tìm kiếm nhân viên...">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option value="">Tất cả chức vụ</option>
                    <option value="admin">Quản trị viên</option>
                    <option value="manager">Quản lý</option>
                    <option value="staff">Nhân viên</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select">
                    <option value="">Bộ phận</option>
                    <option value="reception">Lễ tân</option>
                    <option value="housekeeping">Buồng phòng</option>
                    <option value="restaurant">Nhà hàng</option>
                    <option value="maintenance">Bảo trì</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn-add w-100" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                    <i class="fas fa-plus"></i> Thêm Nhân Viên
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nhân Viên</th>
                    <th>Chức Vụ</th>
                    <th>Bộ Phận</th>
                    <th>Số Điện Thoại</th>
                    <th>Email</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="employee-info">
                            <img src="https://ui-avatars.com/api/?name=Nguyen+Van+A&background=d4b896&color=fff"
                                alt="Avatar" class="employee-avatar">
                            <div>
                                <p class="employee-name">Nguyễn Văn A</p>
                                <p class="employee-id">NV001</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge badge-admin">Quản trị viên</span></td>
                    <td>Quản lý</td>
                    <td>0912345678</td>
                    <td>nguyenvana@hotel.com</td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                            data-bs-target="#viewEmployeeModal" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                            data-bs-target="#permissionModal" title="Phân quyền">
                            <i class="fas fa-key"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#taskModal" title="Giao nhiệm vụ">
                            <i class="fas fa-tasks"></i>
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
                    <td>
                        <div class="employee-info">
                            <img src="https://ui-avatars.com/api/?name=Tran+Thi+B&background=d4b896&color=fff"
                                alt="Avatar" class="employee-avatar">
                            <div>
                                <p class="employee-name">Trần Thị B</p>
                                <p class="employee-id">NV002</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge badge-manager">Quản lý</span></td>
                    <td>Lễ tân</td>
                    <td>0923456789</td>
                    <td>tranthib@hotel.com</td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" title="Phân quyền">
                            <i class="fas fa-key"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" title="Giao nhiệm vụ">
                            <i class="fas fa-tasks"></i>
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
                    <td>
                        <div class="employee-info">
                            <img src="https://ui-avatars.com/api/?name=Le+Van+C&background=d4b896&color=fff"
                                alt="Avatar" class="employee-avatar">
                            <div>
                                <p class="employee-name">Lê Văn C</p>
                                <p class="employee-id">NV003</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge badge-staff">Nhân viên</span></td>
                    <td>Buồng phòng</td>
                    <td>0934567890</td>
                    <td>levanc@hotel.com</td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" title="Phân quyền">
                            <i class="fas fa-key"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" title="Giao nhiệm vụ">
                            <i class="fas fa-tasks"></i>
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
                    <td>
                        <div class="employee-info">
                            <img src="https://ui-avatars.com/api/?name=Pham+Thi+D&background=d4b896&color=fff"
                                alt="Avatar" class="employee-avatar">
                            <div>
                                <p class="employee-name">Phạm Thị D</p>
                                <p class="employee-id">NV004</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge badge-staff">Nhân viên</span></td>
                    <td>Nhà hàng</td>
                    <td>0945678901</td>
                    <td>phamthid@hotel.com</td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-success" title="Phân quyền">
                            <i class="fas fa-key"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" title="Giao nhiệm vụ">
                            <i class="fas fa-tasks"></i>
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

    <!-- Modal: Thêm Nhân Viên -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus"></i> Thêm Nhân Viên Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="avatar-upload">
                            <img src="https://ui-avatars.com/api/?name=User&background=d4b896&color=fff&size=120"
                                alt="Avatar" class="avatar-preview" id="avatarPreview">
                            <label for="avatarInput" style="color: #000;">
                                <i class="fas fa-camera"></i> Chọn ảnh đại diện
                            </label>
                            <input type="file" id="avatarInput" accept="image/*">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mã Nhân Viên *</label>
                                <input type="text" class="form-control" placeholder="NV005" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Họ và Tên *</label>
                                <input type="text" class="form-control" placeholder="Nguyễn Văn A" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số Điện Thoại *</label>
                                <input type="text" class="form-control" placeholder="0912345678" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" placeholder="email@hotel.com" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ngày Sinh</label>
                                <input type="date" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Giới Tính</label>
                                <select class="form-select">
                                    <option>Nam</option>
                                    <option>Nữ</option>
                                    <option>Khác</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">CMND/CCCD *</label>
                                <input type="text" class="form-control" placeholder="001234567890" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Địa Chỉ</label>
                            <textarea class="form-control" rows="2" placeholder="Nhập địa chỉ..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Chức Vụ *</label>
                                <select class="form-select" required>
                                    <option>Quản trị viên</option>
                                    <option>Quản lý</option>
                                    <option selected>Nhân viên</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Bộ Phận *</label>
                                <select class="form-select" required>
                                    <option>Lễ tân</option>
                                    <option>Buồng phòng</option>
                                    <option>Nhà hàng</option>
                                    <option>Bảo trì</option>
                                    <option>Kế toán</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Ngày Vào Làm *</label>
                                <input type="date" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Lương Cơ Bản</label>
                                <input type="number" class="form-control" placeholder="5000000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng Thái *</label>
                                <select class="form-select" required>
                                    <option value="active" selected>Đang làm việc</option>
                                    <option value="inactive">Nghỉ việc</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ghi Chú</label>
                            <textarea class="form-control" rows="2" placeholder="Nhập ghi chú..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary">Thêm Nhân Viên</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Xem Chi Tiết Nhân Viên -->
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user"></i> Thông Tin Nhân Viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <img src="https://ui-avatars.com/api/?name=Nguyen+Van+A&background=d4b896&color=fff&size=120"
                            alt="Avatar" class="avatar-preview">
                        <h5 class="mt-2">Nguyễn Văn A</h5>
                        <p class="text-muted">NV001</p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Chức vụ:</strong> <span class="badge badge-admin">Quản trị viên</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Bộ phận:</strong> Quản lý
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Số điện thoại:</strong> 0912345678
                        </div>
                        <div class="col-md-6">
                            <strong>Email:</strong> nguyenvana@hotel.com
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Ngày sinh:</strong> 15/05/1990
                        </div>
                        <div class="col-md-6">
                            <strong>Giới tính:</strong> Nam
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>CMND/CCCD:</strong> 001234567890
                        </div>
                        <div class="col-md-6">
                            <strong>Ngày vào làm:</strong> 01/01/2020
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Địa chỉ:</strong> 123 Đường ABC, Quận 1, TP.HCM
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Lương cơ bản:</strong> 15,000,000 VNĐ
                        </div>
                        <div class="col-md-6">
                            <strong>Trạng thái:</strong> <span class="badge badge-active">Đang làm việc</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Ghi chú:</strong> Nhân viên xuất sắc, có kinh nghiệm quản lý
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary"><i class="fas fa-edit"></i> Chỉnh Sửa</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Phân Quyền -->
    <div class="modal fade" id="permissionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key"></i> Phân Quyền Nhân Viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h6>Nhân viên: <strong>Nguyễn Văn A (NV001)</strong></h6>
                        <p class="text-muted">Chức vụ: Quản trị viên</p>
                    </div>

                    <div class="permission-group">
                        <h6><i class="fas fa-bed"></i> Quản Lý Phòng</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm1" checked>
                            <label class="form-check-label" for="perm1">Xem danh sách phòng</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm2" checked>
                            <label class="form-check-label" for="perm2">Thêm/Sửa/Xóa phòng</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm3" checked>
                            <label class="form-check-label" for="perm3">Thay đổi trạng thái phòng</label>
                        </div>
                    </div>

                    <div class="permission-group">
                        <h6><i class="fas fa-calendar-check"></i> Quản Lý Đặt Phòng</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm4" checked>
                            <label class="form-check-label" for="perm4">Xem đặt phòng</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm5" checked>
                            <label class="form-check-label" for="perm5">Tạo đặt phòng mới</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm6">
                            <label class="form-check-label" for="perm6">Hủy đặt phòng</label>
                        </div>
                    </div>

                    <div class="permission-group">
                        <h6><i class="fas fa-file-invoice"></i> Quản Lý Hóa Đơn</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm7" checked>
                            <label class="form-check-label" for="perm7">Xem hóa đơn</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm8" checked>
                            <label class="form-check-label" for="perm8">Tạo hóa đơn</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm9">
                            <label class="form-check-label" for="perm9">Xóa hóa đơn</label>
                        </div>
                    </div>

                    <div class="permission-group">
                        <h6><i class="fas fa-users"></i> Quản Lý Khách Hàng</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm10" checked>
                            <label class="form-check-label" for="perm10">Xem thông tin khách hàng</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm11" checked>
                            <label class="form-check-label" for="perm11">Thêm/Sửa khách hàng</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm12">
                            <label class="form-check-label" for="perm12">Xóa khách hàng</label>
                        </div>
                    </div>

                    <div class="permission-group">
                        <h6><i class="fas fa-user-tie"></i> Quản Lý Nhân Viên</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm13" checked>
                            <label class="form-check-label" for="perm13">Xem danh sách nhân viên</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm14" checked>
                            <label class="form-check-label" for="perm14">Thêm/Sửa nhân viên</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm15" checked>
                            <label class="form-check-label" for="perm15">Phân quyền nhân viên</label>
                        </div>
                    </div>

                    <div class="permission-group">
                        <h6><i class="fas fa-chart-line"></i> Báo Cáo & Thống Kê</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm16" checked>
                            <label class="form-check-label" for="perm16">Xem báo cáo doanh thu</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="perm17" checked>
                            <label class="form-check-label" for="perm17">Xuất báo cáo</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary">Lưu Quyền</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Giao Nhiệm Vụ -->
    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-tasks"></i> Giao Nhiệm Vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h6>Nhân viên: <strong>Nguyễn Văn A (NV001)</strong></h6>
                        <p class="text-muted">Chức vụ: Quản trị viên - Bộ phận: Quản lý</p>
                    </div>

                    <div class="mb-4">
                        <h6>Danh Sách Nhiệm Vụ Hiện Tại</h6>
                        <div class="task-item">
                            <div>
                                <div class="task-name">Kiểm tra báo cáo tháng 10</div>
                                <div class="task-date">Hạn: 31/10/2025</div>
                            </div>
                            <span class="badge badge-active">Đang thực hiện</span>
                        </div>
                        <div class="task-item">
                            <div>
                                <div class="task-name">Đào tạo nhân viên mới</div>
                                <div class="task-date">Hạn: 05/11/2025</div>
                            </div>
                            <span class="badge badge-active">Đang thực hiện</span>
                        </div>
                        <div class="task-item">
                            <div>
                                <div class="task-name">Họp quý 4 với ban lãnh đạo</div>
                                <div class="task-date">Hoàn thành: 15/10/2025</div>
                            </div>
                            <span class="badge bg-success">Hoàn thành</span>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <h6>Giao Nhiệm Vụ Mới</h6>
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Tên Nhiệm Vụ *</label>
                                <input type="text" class="form-control" placeholder="Nhập tên nhiệm vụ..." required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô Tả Chi Tiết</label>
                                <textarea class="form-control" rows="3"
                                    placeholder="Mô tả chi tiết nhiệm vụ..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mức Độ Ưu Tiên *</label>
                                    <select class="form-select" required>
                                        <option>Thấp</option>
                                        <option selected>Trung bình</option>
                                        <option>Cao</option>
                                        <option>Khẩn cấp</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Hạn Hoàn Thành *</label>
                                    <input type="date" class="form-control" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi Chú</label>
                                <textarea class="form-control" rows="2" placeholder="Ghi chú thêm..."></textarea>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary">Giao Nhiệm Vụ</button>
                </div>
            </div>
        </div>
    </div>
</div>