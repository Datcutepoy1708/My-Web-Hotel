<?php
// Lấy thông tin nhân viên hiện tại
$id_nhan_vien = $_SESSION['id_nhan_vien'];

// Cập nhật tiến độ nhiệm vụ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_progress'])) {
    $id_nhiem_vu = intval($_POST['id_nhiem_vu']);
    $tien_do_hoan_thanh = intval($_POST['tien_do_hoan_thanh']);
    $trang_thai = $_POST['trang_thai'];

    // Kiểm tra quyền sở hữu nhiệm vụ
    $checkStmt = $mysqli->prepare("SELECT id_nhiem_vu FROM nhiem_vu WHERE id_nhiem_vu = ? AND id_nhan_vien_duoc_gan = ?");
    $checkStmt->bind_param("ii", $id_nhiem_vu, $id_nhan_vien);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $stmt = $mysqli->prepare("UPDATE nhiem_vu SET tien_do_hoan_thanh = ?, trang_thai = ? WHERE id_nhiem_vu = ?");
        $stmt->bind_param("isi", $tien_do_hoan_thanh, $trang_thai, $id_nhiem_vu);

        if ($stmt->execute()) {
            // Ghi lịch sử
            $loai_thay_doi = 'Cập nhật tiến độ';
            $noi_dung = "Cập nhật tiến độ: $tien_do_hoan_thanh%, Trạng thái: $trang_thai";
            $historyStmt = $mysqli->prepare("
                INSERT INTO lich_su_thay_doi (id_nhan_vien, id_nhiem_vu, loai_thay_doi, noi_dung_thay_doi, nguoi_thay_doi)
                VALUES (?, ?, ?, ?, ?)
            ");
            $historyStmt->bind_param("iissi", $id_nhan_vien, $id_nhiem_vu, $loai_thay_doi, $noi_dung, $id_nhan_vien);
            $historyStmt->execute();
            $historyStmt->close();

            $message = 'Cập nhật tiến độ thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi: ' . $stmt->error;
            $messageType = 'danger';
        }
        $stmt->close();
    } else {
        $message = 'Bạn không có quyền cập nhật nhiệm vụ này!';
        $messageType = 'danger';
    }
    $checkStmt->close();
}

// Phân trang và lọc
$trang_thai_filter = isset($_GET['trang_thai']) ? trim($_GET['trang_thai']) : '';
$muc_do_filter = isset($_GET['muc_do']) ? trim($_GET['muc_do']) : '';
$pageNum = isset($_GET['pageNum']) ? intval($_GET['pageNum']) : 1;
$pageNum = max(1, $pageNum);
$perPage = 10;
$offset = ($pageNum - 1) * $perPage;

// Xây dựng query
$where = "WHERE nv.id_nhan_vien_duoc_gan = ?";
$params = [$id_nhan_vien];
$types = 'i';

if ($trang_thai_filter) {
    $where .= " AND nv.trang_thai = ?";
    $params[] = $trang_thai_filter;
    $types .= 's';
}

if ($muc_do_filter) {
    $where .= " AND nv.muc_do_uu_tien = ?";
    $params[] = $muc_do_filter;
    $types .= 's';
}

// Đếm tổng số nhiệm vụ
$countSql = "SELECT COUNT(*) as total 
             FROM nhiem_vu nv
             $where";
$countStmt = $mysqli->prepare($countSql);
$countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalResult = $countStmt->get_result();
$totalTasks = $totalResult->fetch_assoc()['total'];
$countStmt->close();

// Lấy danh sách nhiệm vụ
$sql = "SELECT nv.*, 
               nv1.ho_ten as nguoi_gan_ten,
               nv1.ma_nhan_vien as nguoi_gan_ma
        FROM nhiem_vu nv
        LEFT JOIN nhan_vien nv1 ON nv.id_nhan_vien_gan_phien = nv1.id_nhan_vien
        $where
        ORDER BY nv.created_at DESC
        LIMIT ? OFFSET ?";

$params[] = $perPage;
$params[] = $offset;
$types .= 'ii';

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Build base URL for pagination
$baseUrl = "index.php?page=my-tasks";
if ($trang_thai_filter) $baseUrl .= "&trang_thai=" . urlencode($trang_thai_filter);
if ($muc_do_filter) $baseUrl .= "&muc_do=" . urlencode($muc_do_filter);
?>

<div class="main-content">
    <div class="content-header">
        <h1>Nhiệm Vụ Của Tôi</h1>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo h($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" action="index.php">
            <input type="hidden" name="page" value="my-tasks">
            <div class="row g-3">
                <div class="col-md-4">
                    <select class="form-select" name="trang_thai">
                        <option value="">Tất cả trạng thái</option>
                        <option value="Chưa bắt đầu" <?php echo $trang_thai_filter == 'Chưa bắt đầu' ? 'selected' : ''; ?>>Chưa bắt đầu</option>
                        <option value="Đang thực hiện" <?php echo $trang_thai_filter == 'Đang thực hiện' ? 'selected' : ''; ?>>Đang thực hiện</option>
                        <option value="Hoàn thành" <?php echo $trang_thai_filter == 'Hoàn thành' ? 'selected' : ''; ?>>Hoàn thành</option>
                        <option value="Hủy" <?php echo $trang_thai_filter == 'Hủy' ? 'selected' : ''; ?>>Hủy</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="muc_do">
                        <option value="">Tất cả mức độ</option>
                        <option value="Thấp" <?php echo $muc_do_filter == 'Thấp' ? 'selected' : ''; ?>>Thấp</option>
                        <option value="Trung bình" <?php echo $muc_do_filter == 'Trung bình' ? 'selected' : ''; ?>>Trung bình</option>
                        <option value="Cao" <?php echo $muc_do_filter == 'Cao' ? 'selected' : ''; ?>>Cao</option>
                        <option value="Khẩn cấp" <?php echo $muc_do_filter == 'Khẩn cấp' ? 'selected' : ''; ?>>Khẩn cấp</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tasks List -->
    <div class="table-container">
        <?php if (empty($tasks)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> Bạn chưa có nhiệm vụ nào
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($tasks as $task): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><?php echo h($task['ten_nhiem_vu']); ?></h6>
                                <?php
                                $priorityClass = 'bg-secondary';
                                switch ($task['muc_do_uu_tien']) {
                                    case 'Khẩn cấp':
                                        $priorityClass = 'bg-danger';
                                        break;
                                    case 'Cao':
                                        $priorityClass = 'bg-warning';
                                        break;
                                    case 'Trung bình':
                                        $priorityClass = 'bg-info';
                                        break;
                                    case 'Thấp':
                                        $priorityClass = 'bg-success';
                                        break;
                                }

                                $statusClass = 'bg-secondary';
                                switch ($task['trang_thai']) {
                                    case 'Hoàn thành':
                                        $statusClass = 'bg-success';
                                        break;
                                    case 'Đang thực hiện':
                                        $statusClass = 'bg-primary';
                                        break;
                                    case 'Hủy':
                                        $statusClass = 'bg-danger';
                                        break;
                                }
                                ?>
                                <span class="badge <?php echo $priorityClass; ?>"><?php echo h($task['muc_do_uu_tien']); ?></span>
                            </div>
                            <div class="card-body">
                                <?php if ($task['mo_ta_chi_tiet']): ?>
                                    <p class="card-text"><?php echo nl2br(h($task['mo_ta_chi_tiet'])); ?></p>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> Bắt đầu: <?php echo formatDate($task['ngay_bat_dau']); ?><br>
                                        <i class="fas fa-calendar-check"></i> Hạn: <?php echo formatDate($task['han_hoan_thanh']); ?><br>
                                        <?php if ($task['nguoi_gan_ten']): ?>
                                            <i class="fas fa-user"></i> Người giao: <?php echo h($task['nguoi_gan_ten']); ?> (<?php echo h($task['nguoi_gan_ma']); ?>)
                                        <?php endif; ?>
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small">Tiến độ hoàn thành: <?php echo $task['tien_do_hoan_thanh']; ?>%</label>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: <?php echo $task['tien_do_hoan_thanh']; ?>%"
                                            aria-valuenow="<?php echo $task['tien_do_hoan_thanh']; ?>"
                                            aria-valuemin="0"
                                            aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo h($task['trang_thai']); ?></span>
                                </div>

                                <?php if ($task['ghi_chu']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted"><strong>Ghi chú:</strong> <?php echo nl2br(h($task['ghi_chu'])); ?></small>
                                    </div>
                                <?php endif; ?>

                                <!-- Form cập nhật tiến độ -->
                                <button type="button" class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#updateTaskModal<?php echo $task['id_nhiem_vu']; ?>">
                                    <i class="fas fa-edit"></i> Cập Nhật Tiến Độ
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Cập Nhật Tiến Độ -->
                    <div class="modal fade" id="updateTaskModal<?php echo $task['id_nhiem_vu']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Cập Nhật Tiến Độ</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="id_nhiem_vu" value="<?php echo $task['id_nhiem_vu']; ?>">

                                        <div class="mb-3">
                                            <label class="form-label">Tiến Độ Hoàn Thành (%)</label>
                                            <input type="number" class="form-control" name="tien_do_hoan_thanh"
                                                min="0" max="100" value="<?php echo $task['tien_do_hoan_thanh']; ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Trạng Thái</label>
                                            <select class="form-select" name="trang_thai" required>
                                                <option value="Chưa bắt đầu" <?php echo $task['trang_thai'] == 'Chưa bắt đầu' ? 'selected' : ''; ?>>Chưa bắt đầu</option>
                                                <option value="Đang thực hiện" <?php echo $task['trang_thai'] == 'Đang thực hiện' ? 'selected' : ''; ?>>Đang thực hiện</option>
                                                <option value="Hoàn thành" <?php echo $task['trang_thai'] == 'Hoàn thành' ? 'selected' : ''; ?>>Hoàn thành</option>
                                                <option value="Hủy" <?php echo $task['trang_thai'] == 'Hủy' ? 'selected' : ''; ?>>Hủy</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" name="update_progress" class="btn btn-primary">Cập Nhật</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php echo getPagination($totalTasks, $perPage, $pageNum, $baseUrl); ?>
</div>