<?php
$canViewBooking = function_exists('checkPermission') ? checkPermission('booking.view') : true;

if (!$canViewBooking) {
    http_response_code(403);
    echo '<div class="main-content"><div class="alert alert-danger m-4">Bạn không có quyền xem trang booking.</div></div>';
    return;
}
?>
<div class="main-content">
    <div class="content-header">
        <h1>Quản Lý Booking</h1>
        <?php
            $current_panel = isset($panel) ? $panel : (isset($_GET['panel']) ? $_GET['panel'] : 'roomBooking-panel');
        ?>
        <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="<?php echo ($current_panel=='roomBooking-panel') ? 'nav-link active' : 'nav-link'; ?>"
                    href="/My-Web-Hotel/admin/index.php?page=booking-manager&panel=roomBooking-panel">
                    <span>Booking Phòng</span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="<?php echo ($current_panel=='serviceBooking-panel') ? 'nav-link active' : 'nav-link'; ?>"
                    href="/My-Web-Hotel/admin/index.php?page=booking-manager&panel=serviceBooking-panel">
                    <span>Booking Dịch Vụ</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <?php
            $panel = isset($panel) ? $panel : (isset($_GET['panel']) ? trim($_GET['panel']) : 'roomBooking-panel');
            $panelAllowed = [
                'roomBooking-panel' => 'roomBooking-panel.php',
                'serviceBooking-panel' => 'serviceBooking-panel.php',
            ];
            if (isset($panelAllowed[$panel])) {
                // $mysqli should be available from parent scope (index.php or Controller)
                // If not, try to get it from global scope
                if (!isset($mysqli)) {
                    global $mysqli;
                    if (!isset($mysqli)) {
                        require_once __DIR__ . '/../includes/connect.php';
                    }
                }
                // Use absolute path to avoid issues
                $panelFile = __DIR__ . DIRECTORY_SEPARATOR . $panelAllowed[$panel];
                if (file_exists($panelFile)) {
                    include $panelFile;
                } else {
                    echo '<div class="alert alert-danger">File not found: ' . htmlspecialchars($panelFile) . '</div>';
                }
            } else {
                $notFoundFile = __DIR__ . DIRECTORY_SEPARATOR . '404.php';
                if (file_exists($notFoundFile)) {
                    include $notFoundFile;
                } else {
                    echo '<div class="alert alert-danger">404 - Page not found</div>';
                }
            }  
        ?>

    </div>
</div>
</div>