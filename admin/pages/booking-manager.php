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
            $panel = isset($_GET['panel']) ? trim($_GET['panel']) : 'roomBooking-panel';
            $panelAllowed = [
                'roomBooking-panel' => 'pages/roomBooking-panel.php',
                'serviceBooking-panel' => 'pages/serviceBooking-panel.php',
            ];
            if (isset($panelAllowed[$panel])) {
                include $panelAllowed[$panel];
            } else {
                include 'pages/404.php';
            }  
        ?>

    </div>
</div>
</div>