<div class="content-card">
    <div class="card-header-custom">
        <h3 class="card-title">Danh Sách Phòng</h3>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="fas fa-plus"></i> Thêm Phòng
        </button>
    </div>
    <div class="filter-section">
        <form method="GET" action="">
            <input type="hidden" name="page" value="room-manager">
            <div class="row">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" name="search" placeholder="Tìm kiếm phòng..."
                            value="<?php echo h($search); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status" id="statusFilter">
                        <option value="">Tất cả trạng thái</option>
                        <option value="Available" <?php echo $status_filter == 'Available' ? 'selected' : ''; ?>>Có
                            sẵn</option>
                        <option value="Booked" <?php echo $status_filter == 'Booked' ? 'selected' : ''; ?>>
                            Đã
                            đặt
                        </option>
                        <option value="Occupied" <?php echo $status_filter == 'Occupied' ? 'selected' : ''; ?>>
                            Đang
                            thuê</option>
                        <option value="Maintenance" <?php echo $status_filter == 'Maintenance' ? 'selected' : ''; ?>>Bảo
                            trì
                        </option>
                        <option value="Cleaning" <?php echo $status_filter == 'Cleaning' ? 'selected' : ''; ?>>
                            Đang
                            dọn</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="type" id="typeFilter">
                        <option value="0">Tất cả loại phòng</option>
                        <?php foreach ($roomTypes as $rt): ?>
                            <option value="<?php echo $rt['room_type_id']; ?>"
                                <?php echo $type_filter == $rt['room_type_id'] ? 'selected' : ''; ?>>
                                <?php echo h($rt['room_type_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
                <div class="col-md-2">
                    <!-- NÚT RESET -->
                    <a href="?page=room-manager" class="btn btn-secondary w-100" id="resetFilter">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
    <!-- Bảng danh sách phòng -->
    <div class="table-container">
        <table class="table table-hover" id="roomsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Số Phòng</th>
                    <th>Tầng</th>
                    <th>Loại Phòng</th>
                    <th>Giá/Đêm</th>
                    <th>Trạng Thái</th>
                    <th>Diện Tích</th>
                    <th>Sức chứa</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rooms)): ?>
                    <tr>
                        <td colspan="9" class="text-center">Không có dữ liệu</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?php echo $room['room_id']; ?></td>
                            <td><?php echo h($room['room_number']); ?></td>
                            <td><?php echo $room['floor']; ?></td>
                            <td>
                                <?php
                                $statusClass = 'bg-secondary';
                                $roomText = $room['room_type_name'];
                                switch ($room['room_type_name']) {
                                    case 'Budget Room':
                                        $statusClass = 'bg-success';
                                        $roomText = 'Budget Room';
                                        break;
                                    case 'Family Room':
                                        $statusClass = 'bg-warning';
                                        $roomText = 'Family Room';
                                        break;
                                    case 'Suite Room':
                                        $statusClass = 'bg-danger';
                                        $roomText = 'Suite Room';
                                        break;
                                    case 'Deluxe Room':
                                        $statusClass = 'bg-dark';
                                        $roomText = 'Deluxe Room';
                                        break;
                                    case 'Standard Room':
                                        $statusClass = 'bg-info';
                                        $roomText = 'Standard Room';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo $roomText; ?></span>
                            </td>
                            <td><?php echo formatCurrency($room['base_price'] ?? 0); ?></td>
                            <td>
                                <?php
                                $statusClass = 'bg-secondary';
                                $statusText = $room['status'];
                                switch ($room['status']) {
                                    case 'Available':
                                        $statusClass = 'bg-success';
                                        $statusText = 'Có sẵn';
                                        break;
                                    case 'Booked':
                                        $statusClass = 'bg-warning';
                                        $statusText = 'Đã đặt';
                                        break;
                                    case 'Occupied':
                                        $statusClass = 'bg-danger';
                                        $statusText = 'Đang thuê';
                                        break;
                                    case 'Maintenance':
                                        $statusClass = 'bg-dark';
                                        $statusText = 'Bảo trì';
                                        break;
                                    case 'Cleaning':
                                        $statusClass = 'bg-info';
                                        $statusText = 'Đang dọn';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                            </td>
                            <td><?php echo $room['area'] ? $room['area'] . 'm²' : '-'; ?></td>
                            <td><?php echo $room['capacity'] ?? '-'; ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                    data-bs-target="#viewRoomModal<?php echo $room['room_id']; ?>" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning"
                                    onclick="editRoom(<?php echo $room['room_id']; ?>)" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                    onclick="deleteRoom(<?php echo $room['room_id']; ?>)" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- View Modal -->
                        <div class="modal fade" id="viewRoomModal<?php echo $room['room_id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Chi Tiết Phòng</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <?php if ($room['image']): ?>
                                                    <img src="<?php echo h($room['image']); ?>" class="img-fluid rounded"
                                                        alt="Room Image">
                                                <?php else: ?>
                                                    <img src="https://via.placeholder.com/400x300" class="img-fluid rounded"
                                                        alt="Room Image">
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <h4>Phòng <?php echo h($room['room_number']); ?></h4>
                                                <p><strong>Loại phòng:</strong>
                                                    <?php echo h($room['room_type_name'] ?? '-'); ?></p>
                                                <p><strong>Tầng:</strong> <?php echo $room['floor']; ?></p>
                                                <p><strong>Giá/đêm:</strong>
                                                    <?php echo formatCurrency($room['base_price'] ?? 0); ?></p>
                                                <p><strong>Diện tích:</strong>
                                                    <?php echo $room['area'] ? $room['area'] . 'm²' : '-'; ?></p>
                                                <p><strong>Sức chứa:</strong>
                                                    <?php echo $room['capacity'] ?? '-'; ?>
                                                    người
                                                </p>
                                                <p><strong>Trạng thái:</strong>
                                                    <span
                                                        class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                </p>
                                                <?php if ($room['amenities']): ?>
                                                    <p><strong>Tiện nghi:</strong> <?php echo h($room['amenities']); ?>
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
    <?php echo getPagination($total, $perPage, $pageNum, $baseUrl); ?>
</div>
<!-- Modal Thêm/Sửa Phòng -->
<div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $editRoom ? 'Sửa' : 'Thêm'; ?> Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php if ($editRoom): ?>
                        <input type="hidden" name="room_id" value="<?php echo $editRoom['room_id']; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số phòng *</label>
                            <input type="text" class="form-control" name="room_number"
                                value="<?php echo h($editRoom['room_number'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tầng *</label>
                            <input type="number" class="form-control" name="floor"
                                value="<?php echo $editRoom['floor'] ?? ''; ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loại phòng *</label>
                            <select class="form-select" name="room_type_id" required>
                                <option value="">Chọn loại phòng</option>
                                <?php foreach ($roomTypes as $rt): ?>
                                    <option value="<?php echo $rt['room_type_id']; ?>"
                                        <?php echo ($editRoom['room_type_id'] ?? '') == $rt['room_type_id'] ? 'selected' : ''; ?>>
                                        <?php echo h($rt['room_type_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trạng thái *</label>
                            <select class="form-select" name="status" required>
                                <option value="Available"
                                    <?php echo ($editRoom['status'] ?? 'Available') == 'Available' ? 'selected' : ''; ?>>
                                    Có sẵn</option>
                                <option value="Booked"
                                    <?php echo ($editRoom['status'] ?? '') == 'Booked' ? 'selected' : ''; ?>>Đã
                                    đặt
                                </option>
                                <option value="Occupied"
                                    <?php echo ($editRoom['status'] ?? '') == 'Occupied' ? 'selected' : ''; ?>>
                                    Đang
                                    thuê
                                </option>
                                <option value="Maintenance"
                                    <?php echo ($editRoom['status'] ?? '') == 'Maintenance' ? 'selected' : ''; ?>>
                                    Bảo
                                    trì</option>
                                <option value="Cleaning"
                                    <?php echo ($editRoom['status'] ?? '') == 'Cleaning' ? 'selected' : ''; ?>>
                                    Đang
                                    dọn
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"
                        name="<?php echo $editRoom ? 'update_room' : 'add_room'; ?>">
                        <?php echo $editRoom ? 'Cập nhật' : 'Thêm'; ?> Phòng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>