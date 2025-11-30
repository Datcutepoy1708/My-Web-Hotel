<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="/My-Web-Hotel/admin/assets/js/sidebar.js"></script>
<script src="/My-Web-Hotel/admin/assets/js/modal-reset.js"></script>

<?php
    // Ensure $page is defined
    $page = $page ?? (isset($_GET['page']) ? trim($_GET['page']) : 'home');
    
    if ($page == 'home') echo '<script src="/My-Web-Hotel/admin/assets/js/home.js"></script>';
    if ($page == 'room-manager') echo '<script src="/My-Web-Hotel/admin/assets/js/room-manager.js"></script>';
    if ($page == 'staff-manager') echo '<script src="/My-Web-Hotel/admin/assets/js/staff-manager.js"></script>';
    if ($page == 'reports-manager') echo '<script src="/My-Web-Hotel/admin/assets/js/reports-manager.js"></script>';
    if ($page == 'blogs-manager') echo '<script src="/My-Web-Hotel/admin/assets/js/blogs-manager.js"></script>';
    if ($page == 'invoices-manager') echo '<script src="/My-Web-Hotel/admin/assets/js/invoices-manager.js"></script>';
    if ($page == 'booking-manager') echo '<script src="/My-Web-Hotel/admin/assets/js/booking-manager.js"></script>';
?>
</body>

</html>