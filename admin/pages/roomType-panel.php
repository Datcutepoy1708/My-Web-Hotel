<div class="content-card">
    <div class="card-header-custom">
        <h3 class="card-title">Danh Sách Loại Phòng</h3>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="fas fa-plus"></i> Thêm Loại Phòng
        </button>
    </div>
    <div class="filter-section">
        <form method="GET" action="">
            <input type="hidden" name="page" value="room-manager">
            <div class="row">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Tìm kiếm" value="<?php echo h($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="">Đang Hoạt Động</option>
                        <option value="">Dừng Hoạt Động</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="sort">
                        <option value="0">Tất cả</option>
                        <option value="0">Diện Tích Tăng Dần</option>
                        <option value="0">Diện tích Giảm Dần</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>
    <!-- Bảng danh sách loại phòng -->
    <div class="table-container">
        <table class="table table-hover" id="roomsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Loại</th>
                    <th>Diện Tích</th>
                    <th>Tiện nghi</th>
                    <th>Sức chứa</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($roomTypesAll)): ?>
                    <tr>
                        <td colspan="9" class="text-center">Không có dữ liệu</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($roomTypesAll as $rt): ?>
                        <tr>
                            <td><?php echo $rt['room_type_id']; ?></td>
                            <td><?php echo $rt['room_type_name']; ?></td>
                            <td><?php echo floatval($rt['area']) . ' m²'; ?></td>
                            <td><?php echo $rt['amenities']; ?></td>
                            <td><?php echo $rt['capacity']; ?></td>
                            <td>
                                <?php
                                $statusClass = 'bg-secondary';
                                $statusText = $rt['status'];
                                switch ($rt['status']) {
                                    case 'active':
                                        $statusClass = 'bg-success';
                                        $statusText = 'Đang hoạt động';
                                        break;
                                    case 'mantainance':
                                        $statusClass = 'bg-warning';
                                        $statusText = 'Đang bảo trì';
                                        break;
                                    case 'inactive':
                                        $statusClass = 'bg-error';
                                        $statusText = 'Dừng hoạt động';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                    data-bs-target="#viewRoomTypeModal<?php echo $rt['room_type_id']; ?>" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning"
                                    onclick="editRoomTypes(<?php echo $rt['room_type_id']; ?>)" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="deleteRoomTypes(<?php echo $rt['room_type_id']; ?>)" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <div class="modal fade" id="viewRoomTypeModal<?php echo $rt['room_type_id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Chi Tiết Loại Phòng</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <?php if ($rt['image']): ?>
                                                    <img src="<?php echo h($rt['image']); ?>" class="img-fluid rounded"
                                                        alt="Room Image">
                                                <?php else: ?>
                                                    <img src="https://via.placeholder.com/400x300" class="img-fluid rounded"
                                                        alt="Room Image">
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Tên loại phòng:</strong>
                                                    <?php echo h($rt['room_type_name'] ?? '-'); ?></p>
                                                <p><strong>Mô tả:</strong> <?php echo $rt['description']; ?></p>
                                                <p><strong>Giá/đêm:</strong>
                                                    <?php echo formatCurrency($rt['base_price'] ?? 0) ; ?></p>
                                                <p><strong>Diện tích:</strong>
                                                    <?php echo $rt['area'] ? $rt['area'] . 'm²' : '-'; ?></p>
                                                <p><strong>Sức chứa:</strong>
                                                    <?php echo $rt['capacity'] ?? '-'; ?>
                                                    người
                                                </p>
                                                <p><strong>Trạng thái:</strong>
                                                    <span
                                                        class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                </p>
                                                <?php if ($rt['amenities']): ?>
                                                    <p><strong>Tiện nghi:</strong> <?php echo h($rt['amenities']); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                        <button type="button" class="btn btn-primary"
                                            onclick="editRoomFromView(<?php echo $room['room_id']; ?>)">
                                            Chỉnh Sửa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Pagination -->
    <?php echo getPagination($totalRoomTypes, $perPage, $pageNum, $baseUrl); ?>
</div>
<!-- Modal Thêm Loại Phòng -->
<div class="modal fade" id="addRoomTypeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm Loại Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên loại phòng *</label>
                        <input type="text" class="form-control" name="room_type_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giá cơ bản (VNĐ) *</label>
                            <input type="number" class="form-control" name="base_price" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sức chứa (người) *</label>
                            <input type="number" class="form-control" name="capacity" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Diện tích (m²)</label>
                            <input type="number" class="form-control" name="area" step="0.01">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tiện nghi</label>
                            <input type="text" class="form-control" name="amenities"
                                placeholder="VD: WiFi, TV, Điều hòa...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" name="add_room_type">Thêm Loại Phòng</button>
                </div>
            </form>
        </div>
    </div>
</div>