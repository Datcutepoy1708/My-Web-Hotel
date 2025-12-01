<?php
// Phân quyền
$canViewRoom = function_exists('checkPermission') ? checkPermission('room.view') : true;
$canCreateRoom = function_exists('checkPermission') ? checkPermission('room.create') : true;
$canEditRoom = function_exists('checkPermission') ? checkPermission('room.edit') : true;
$canDeleteRoom = function_exists('checkPermission') ? checkPermission('room.delete') : true;

if (!$canViewRoom) {
    http_response_code(403);
    echo '<div class="alert alert-danger m-4">Bạn không có quyền xem phòng.</div>';
    return;
}

// Build base URL for pagination
$baseUrl = "index.php?page=room-manager&panel=room-panel";
if ($search) $baseUrl .= "&search=" . urlencode($search);
if ($status_filter) $baseUrl .= "&status=" . urlencode($status_filter);
if ($type_filter) $baseUrl .= "&type=" . $type_filter;
?>
<div class="content-card">
    <div class="card-header-custom">
        <h3 class="card-title">Danh Sách Phòng</h3>
        <?php if ($canCreateRoom): ?>
        <button class="btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="fas fa-plus"></i> Thêm Phòng
        </button>
        <?php endif; ?>
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
                        <?php if ($canEditRoom): ?>
                        <button class="btn btn-sm btn-outline-warning"
                            onclick="editRoom(<?php echo $room['room_id']; ?>)" title="Sửa">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php endif; ?>
                        <?php if ($canDeleteRoom): ?>
                        <button class="btn btn-sm btn-outline-danger"
                            onclick="deleteRoom(<?php echo $room['room_id']; ?>)" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
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
                                                <?php 
                                                $roomImages = [];
                                                if (!empty($room['image'])) {
                                                    // Parse images (có thể là JSON hoặc single image)
                                                    if (is_string($room['image']) && (strpos($room['image'], '[') === 0 || strpos($room['image'], '{') === 0)) {
                                                        $decoded = json_decode($room['image'], true);
                                                        $roomImages = is_array($decoded) ? $decoded : [$room['image']];
                                                    } else {
                                                        $roomImages = [$room['image']];
                                                    }
                                                }
                                                
                                                if (!empty($roomImages)): 
                                                    foreach ($roomImages as $img):
                                                        if (!empty($img)):
                                                ?>
                                                    <img src="../<?php echo h($img); ?>" class="img-fluid rounded mb-2"
                                                        alt="Room Image" style="max-width: 100%; max-height: 200px; object-fit: cover;">
                                                <?php 
                                                        endif;
                                                    endforeach;
                                                else: 
                                                ?>
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
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <?php if ($canEditRoom): ?>
                                <button type="button" class="btn btn-primary"
                                    onclick="editRoomFromView(<?php echo $room['room_id']; ?>)">
                                    Chỉnh Sửa
                                </button>
                                <?php endif; ?>
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
            <form method="POST" enctype="multipart/form-data">
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
                    <div class="mb-3">
                        <label class="form-label">Ảnh Phòng (có thể chọn nhiều ảnh, tối đa 5 ảnh)</label>
                        <div class="image-upload-area" onclick="document.getElementById('roomImage').click()"
                            style="border: 2px dashed #ccc; padding: 20px; text-align: center; border-radius: 5px; cursor: pointer;">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Click để chọn ảnh (có thể chọn nhiều)</p>
                            <small class="text-muted">hoặc kéo thả ảnh vào đây (Ctrl/Cmd để chọn nhiều)</small>
                        </div>
                        <input type="file" id="roomImage" name="image[]" accept="image/*" multiple
                            style="display: none" onchange="previewMultipleImages(this, 'roomPreviewContainer')" />
                        <div id="roomPreviewContainer" class="mt-3">
                            <?php 
                            if ($editRoom && !empty($editRoom['image'])) {
                                $images = [];
                                // Parse images (có thể là JSON hoặc single image)
                                if (is_string($editRoom['image']) && (strpos($editRoom['image'], '[') === 0 || strpos($editRoom['image'], '{') === 0)) {
                                    $decoded = json_decode($editRoom['image'], true);
                                    $images = is_array($decoded) ? $decoded : [$editRoom['image']];
                                } else {
                                    $images = [$editRoom['image']];
                                }
                                
                                foreach ($images as $img): 
                                    if (!empty($img)):
                            ?>
                            <div class="image-preview-item d-inline-block me-2 mb-2" style="position: relative;">
                                <img src="../<?php echo h($img); ?>" 
                                    style="max-width: 150px; max-height: 150px; border-radius: 5px; border: 2px solid #ddd;" />
                                <button type="button" class="btn btn-sm btn-danger" style="position: absolute; top: 0; right: 0;"
                                    onclick="removeImagePreview(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                                <input type="hidden" name="existing_images[]" value="<?php echo h($img); ?>">
                            </div>
                            <?php 
                                    endif;
                                endforeach;
                            }
                            ?>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Định dạng: JPG, PNG, GIF, WEBP. Kích thước tối đa: 5MB mỗi ảnh. Tối đa 5 ảnh.</small>
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

<script>
    // Preview image function (single)
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (!preview) return;
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = "block";
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Preview multiple images function
    function previewMultipleImages(input, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const files = input.files;
        const maxImages = 5;
        let currentCount = container.querySelectorAll('.image-preview-item').length;
        
        if (files.length + currentCount > maxImages) {
            alert('Chỉ có thể chọn tối đa ' + maxImages + ' ảnh. Bạn đã chọn ' + (files.length + currentCount) + ' ảnh.');
            input.value = '';
            return;
        }
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview-item d-inline-block me-2 mb-2';
                    div.style.position = 'relative';
                    div.innerHTML = `
                        <img src="${e.target.result}" 
                            style="max-width: 150px; max-height: 150px; border-radius: 5px; border: 2px solid #ddd;" />
                        <button type="button" class="btn btn-sm btn-danger" style="position: absolute; top: 0; right: 0;"
                            onclick="removeImagePreview(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        }
    }
    
    function removeImagePreview(button) {
        const item = button.closest('.image-preview-item');
        if (item) {
            item.remove();
            // Update file input
            const fileInput = document.getElementById('roomImage');
            if (fileInput) {
                // Reset file input (không thể xóa file đã chọn, nhưng có thể clear)
                fileInput.value = '';
            }
        }
    }
    
    // ==================== HELPER FUNCTIONS ====================

    // Hàm reset form tổng quát
    function resetFormFields(form) {
        if (!form) return;

        form.reset();

        // Xóa input hidden (trừ page và panel)
        form.querySelectorAll('input[type="hidden"]').forEach(input => {
            if (input.name !== 'page' && input.name !== 'panel') {
                input.remove();
            }
        });

        // Reset text/number/tel/email inputs
        form.querySelectorAll('input[type="text"], input[type="number"], input[type="tel"], input[type="email"],select').forEach(input => {
            input.value = '';
        });

        // Reset textarea
        form.querySelectorAll('textarea').forEach(textarea => {
            textarea.value = '';
        });

        // Reset date về hôm nay
        const today = new Date().toISOString().split('T')[0];
        form.querySelectorAll('input[type="date"]').forEach(input => {
            input.value = today;
        });

        // Clear readonly fields
        form.querySelectorAll('input[readonly]').forEach(input => {
            input.value = '';
        });
    }

    // Hàm xóa query string edit
    function clearEditQueryString() {
        const url = new URL(window.location);
        url.searchParams.delete('action');
        url.searchParams.delete('id');
        window.history.replaceState({}, '', url.toString());
    }

    // Hàm force cleanup backdrop
    function forceCleanupBackdrop() {
        const openModals = document.querySelectorAll('.modal.show');
        if (openModals.length === 0) {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }
    }

    // Hàm reset modal về trạng thái "Thêm mới"
    function resetModalToAddMode(modalElement, form) {
        if (!modalElement || !form) return;

        const modalId = modalElement.id;
        const modalTitle = modalElement.querySelector('.modal-title');
        const submitBtn = form.querySelector('button[type="submit"]');

        // Config cho từng modal
        const modalConfig = {
            'addRoomModal': {
                title: 'Thêm Phòng',
                buttonName: 'add_room',
                buttonHTML: '<i class="fas fa-save"></i> Thêm Phòng'
            },
            'addRoomTypeModal': {
                title: 'Thêm Loại Phòng',
                buttonName: 'add_room_type',
                buttonHTML: '<i class="fas fa-save"></i> Thêm Loại Phòng'
            },
            'addBookingServiceModal': {
                title: 'Thêm Booking Dịch Vụ',
                buttonName: 'add_booking_service',
                buttonHTML: 'Thêm Booking dịch vụ'
            }
        };

        const config = modalConfig[modalId];
        if (config) {
            if (modalTitle) modalTitle.textContent = config.title;
            if (submitBtn) {
                submitBtn.name = config.buttonName;
                submitBtn.innerHTML = config.buttonHTML;
            }
        }
    }

    // ==================== ROOM FUNCTIONS ====================



    function editRoom(id) {
        const url = new URL(window.location.href);
        url.searchParams.set('action', 'edit');
        url.searchParams.set('id', id);
        window.location.href = url.toString();
    }

    function deleteRoom(id) {
        if (confirm('Bạn có chắc chắn muốn xóa phòng này?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="room_id" value="' + id + '">' +
                '<input type="hidden" name="delete_room" value="1">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    // ==================== MODAL AUTO-RESET ====================

    document.addEventListener('DOMContentLoaded', function() {

        // Tự động mở modal edit nếu có action=edit
        <?php if ($editRoom): ?>
            const editModal = new bootstrap.Modal(document.getElementById('addRoomModal'));
            editModal.show();
        <?php endif; ?>

        // Danh sách modal cần auto-reset
        const resettableModals = ['addRoomModal', 'addRoomTypeModal', 'addBookingServiceModal'];

        // Xử lý TỔNG QUÁT cho TẤT CẢ modal
        document.querySelectorAll('.modal').forEach(modalElement => {

            // Event: Khi modal đã đóng hoàn toàn
            modalElement.addEventListener('hidden.bs.modal', function() {
                const form = modalElement.querySelector('form');
                const modalId = modalElement.id;

                // Chỉ xử lý modal trong danh sách
                if (resettableModals.includes(modalId)) {
                    const isEditMode = window.location.search.includes('action=edit');

                    if (isEditMode) {
                        // Xóa query string edit
                        clearEditQueryString();
                    }

                    // Reset form về trạng thái "Thêm mới"
                    if (form) {
                        resetFormFields(form);
                        resetModalToAddMode(modalElement, form);
                    }
                }

                // Cleanup backdrop
                setTimeout(forceCleanupBackdrop, 100);
            });

            // Event: Khi modal sắp mở
            modalElement.addEventListener('show.bs.modal', function() {
                const form = modalElement.querySelector('form');
                const isEditMode = window.location.search.includes('action=edit');

                // Nếu KHÔNG phải edit mode, reset form
                if (!isEditMode && form && resettableModals.includes(modalElement.id)) {
                    resetFormFields(form);
                }
            });
        });

        // Xử lý nút "Thêm mới" - xóa query string edit và reset form
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
            button.addEventListener('click', function() {
                const isEditMode = window.location.search.includes('action=edit');
                if (isEditMode) {
                    clearEditQueryString();
                }
                // Reset form when opening add modal
                const modalId = button.getAttribute('data-bs-target');
                if (modalId && (modalId.includes('addRoomModal') || modalId.includes('addRoomTypeModal'))) {
                    setTimeout(function() {
                        const form = document.querySelector(modalId + ' form');
                        if (form && !window.location.search.includes('action=edit')) {
                            resetFormFields(form);
                            resetModalToAddMode(document.querySelector(modalId), form);
                        }
                    }, 200);
                }
            });
        });

        // Xử lý ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                setTimeout(forceCleanupBackdrop, 150);
            }
        });
    });
</script>