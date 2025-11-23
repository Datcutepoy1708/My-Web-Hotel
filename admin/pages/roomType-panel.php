<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_room_type'])) {
        $room_type_id = intval($_POST['room_type_id']);
        $room_type_name = trim($_POST['room_type_name']);
        $description = trim($_POST['description'] ?? '');
        $base_price = floatval($_POST['base_price']);
        $capacity = intval($_POST['capacity']);
        $amenities = trim($_POST['amenities'] ?? '');
        $area = floatval($_POST['area'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        // Sửa lại câu UPDATE
        $stmt = $mysqli->prepare("UPDATE room_type 
                              SET room_type_name=?, 
                                  description=?, 
                                  base_price=?, 
                                  capacity=?, 
                                  status=?, 
                                  amenities=?, 
                                  area=? 
                              WHERE room_type_id=? 
                              AND deleted IS NULL");

        // Đúng thứ tự: s s d i s s d i (8 tham số)
        $stmt->bind_param(
            "ssdissdi",
            $room_type_name,    // s - string
            $description,       // s - string
            $base_price,        // d - double
            $capacity,          // i - integer
            $status,            // s - string
            $amenities,         // s - string
            $area,              // d - double
            $room_type_id       // i - integer (WHERE clause)
        );

        if ($stmt->execute()) {
            $message = 'Cập nhật loại phòng thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }
    if (isset($_POST['delete_room_type'])) {
        $room_type_id = intval($_POST['room_type_id']);
        $stmt = $mysqli->prepare("UPDATE room_type SET deleted=NOW() WHERE room_type_id=?");
        $stmt->bind_param("i", $room_type_id);
        if ($stmt->execute()) {
            $message = 'Xóa  loại phòng thành công';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    }
}
// Lấy ra thông tin loại phòng để edit
$editRoomTypes = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $mysqli->prepare("SELECT * FROM room_type WHERE room_type_id = ? AND deleted IS NULL");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editRoomTypes = $result->fetch_assoc();
    $stmt->close();
}



// Build base URL for pagination
$baseUrl = "index.php?page=room-manager&panel=roomType-panel";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urlencode($status_filter);
if ($type_filter) $baseUrl .= "&type=" . $type_filter;
?>
<div class="content-card">
    <div class="card-header-custom">
        <h3 class="card-title">Danh Sách Loại Phòng</h3>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addRoomTypeModal">
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
                        <option value="">Đang bảo trì</option>
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
                                        $statusClass = 'bg-danger';
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
                                                    <?php echo formatCurrency($rt['base_price'] ?? 0); ?></p>
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
                                            onclick="editRoomTypeFromView(<?php echo $rt['room_type_id']; ?>)">
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
                <h5 class="modal-title"><?php echo $editRoomTypes ? 'Sửa' : 'Thêm'; ?> Loại Phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php if ($editRoomTypes): ?>
                        <input type="hidden" name="room_type_id" value="<?php echo $editRoomTypes['room_type_id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Tên loại phòng *</label>
                        <input type="text" class="form-control" name="room_type_name" required value="<?php echo $editRoomTypes['room_type_name']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" rows="3">
                            <?php echo $editRoomTypes['description']; ?>
                        </textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giá cơ bản (VNĐ) *</label>
                            <input type="number" class="form-control" name="base_price" step="0.01" required value="<?php echo h($editRoomTypes['base_price']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sức chứa (người) *</label>
                            <input type="number" class="form-control" name="capacity" required value="<?php echo h($editRoomTypes['capacity']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Diện tích (m²)</label>
                            <input type="number" class="form-control" name="area" step="0.01" value="<?php echo h($editRoomTypes['area']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tiện nghi</label>
                            <input type="text" class="form-control" name="amenities"
                                placeholder="VD: WiFi, TV, Điều hòa..." value="<?php echo $editRoomTypes['amenities']; ?>">

                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Trạng thái *</label>
                        <select class="form-select" name="status" required>
                            <option value="active" <?php echo ($editRoomTypes['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>
                                Đang hoạt động</option>
                            <option value="inactive" <?php echo ($editRoomTypes['status'] ?? 'inactive') == 'inactive' ? 'selected' : ''; ?>>Dừng hoạt động
                            </option>
                            <option value="maintenance" value="inactive" <?php echo ($editRoomTypes['status'] ?? 'maintainance') == 'maintainance' ? 'selected' : ''; ?>>
                                Bảo
                                trì</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"
                        name="<?php echo $editRoom ? 'update_room_type' : 'add_room_type'; ?>">
                        <?php echo $editRoom ? 'Cập nhật' : 'Thêm'; ?> Phòng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function editRoomTypes(id) {
        window.location.href = 'index.php?page=room-manager&panel=roomType-panel&action=edit&id=' + id;
    }
    <?php if ($editRoomTypes): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = new bootstrap.Modal(document.getElementById('addRoomTypeModal'));
            modal.show();
        });
    <?php endif; ?>

    function editRoomTypeFromView(id) {
        // Đóng view modal (có ID động)
        const viewModal = bootstrap.Modal.getInstance(
            document.getElementById("viewRoomTypeModal" + id)
        );
        if (viewModal) {
            viewModal.hide();
        }

        // Chuyển đến trang edit
        window.location.href = 'index.php?page=room-manager&panel=roomType-panel&action=edit&id=' + id;
    }

    function deleteRoomTypes(id) {
        if (confirm('Bạn có chắc chắn muốn xóa phòng này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="room_type_id" value="' + id + '">' +
                '<input type="hidden" name="delete_room_type" value="1">';
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>