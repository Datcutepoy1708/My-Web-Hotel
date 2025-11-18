<div class="sidebar" id="sidebar">
    <div>
        <div class="header">
            <img src="/My-Web-Hotel/client/assets/images/user3.jpg" alt="usuario" />
            <div class="info"><strong>Admin</strong><br /></div>
        </div>
        <?php $current_page = isset($page) ? $page : (isset($_GET['page']) ? $_GET['page'] : 'home'); ?>
        <div class="menu">
            <div class="menu-item <?php echo $current_page==='home' ? 'active' : '';?>" data-page="home">
                <i class="fas fa-home"></i><span>Tổng Quan</span>
            </div>
            <div class="menu-item <?php echo $current_page==='room-manager' ? 'active' : '';?>"
                data-page="room-manager">
                <i class="fas fa-bed"></i><span>Phòng</span>
            </div>
            <div class="menu-item <?php echo $current_page==='services-manager' ? 'active' : '';?>"
                data-page="services-manager">
                <i class="fas fa-concierge-bell"></i><span>Dịch Vụ</span>
            </div>
            <div class="menu-item <?php echo $current_page==='invoices-manager' ? 'active' : '';?>"
                data-page="invoices-manager">
                <i class="fa-solid fa-coins"></i><span>Hóa Đơn</span>
            </div>
            <div class="menu-item <?php echo $current_page==='booking-manager' ? 'active' : '';?>"
                data-page="booking-manager">
                <i class="fa-solid fa-calendar-days"></i><span>Booking</span>
            </div>
            <div class="menu-item <?php echo $current_page==='customers-manager' ? 'active' : '';?>"
                data-page="customers-manager">
                <i class="fas fa-users"></i><span>Khách Hàng</span>
            </div>
            <div class="menu-item <?php echo $current_page==='staff-manager' ? 'active' : '';?>"
                data-page="staff-manager">
                <i class="fa-solid fa-user-tie"></i><span>Nhân Viên</span>
            </div>
            <div class="menu-item <?php echo $current_page==='reports-manager' ? 'active' : '';?>"
                data-page="reports-manager">
                <i class="fas fa-chart-line"></i><span>Thống Kê</span>
            </div>
            <div class="menu-item <?php echo $current_page==='blogs-manager' ? 'active' : '';?>"
                data-page="blogs-manager">
                <i class="fa-solid fa-comments"></i><span>Blog & Review</span>
            </div>
            <div class="menu-item" data-page="logout">
                <i class="fas fa-sign-out-alt"></i><span>Đăng Xuất</span>
            </div>
        </div>
    </div>
    <div class="bottom">
        <div class="toggle-theme">
            <i class="fas fa-moon"></i><span>Chế Độ Tối</span>
            <label class="switch">
                <input type="checkbox" id="theme-toggle" />
            </label>
        </div>
        <div class="toggle-sidebar">
            <i class="fas fa-angle-double-left" id="collapse-icon"></i><span>Thu Gọn</span>
        </div>
    </div>
</div>