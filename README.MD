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
                                    <?php echo ($editRoom['status'] ?? '') == 'Booked' ? 'selected' : ''; ?>>Đã đặt
                                </option>
                                <option value="Occupied"
                                    <?php echo ($editRoom['status'] ?? '') == 'Occupied' ? 'selected' : ''; ?>>Đang thuê
                                </option>
                                <option value="Maintenance"
                                    <?php echo ($editRoom['status'] ?? '') == 'Maintenance' ? 'selected' : ''; ?>>Bảo
                                    trì</option>
                                <option value="Cleaning"
                                    <?php echo ($editRoom['status'] ?? '') == 'Cleaning' ? 'selected' : ''; ?>>Đang dọn
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