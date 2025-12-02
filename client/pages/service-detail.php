<?php
// Lấy ID dịch vụ từ URL
$service_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($service_id <= 0) {
    header("Location: /My-Web-Hotel/client/index.php?page=services");
    exit();
}

// Kiểm tra đăng nhập
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['logged_in']);
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

// Lấy thông tin chi tiết dịch vụ
$sql = "SELECT * FROM service WHERE service_id = ? AND deleted IS NULL";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: /My-Web-Hotel/client/index.php?page=404");
    exit();
}

$service = $result->fetch_assoc();

// Debug: Kiểm tra giá trị status
// echo "Status value: '" . $service['status'] . "' | Length: " . strlen($service['status']);
// Uncomment dòng trên để xem giá trị status thực tế

// Lấy 3 dịch vụ gợi ý ngẫu nhiên (loại trừ dịch vụ hiện tại)
$suggestStmt = $mysqli->prepare("
    SELECT * FROM service 
    WHERE LOWER(TRIM(status)) = 'active' AND service_id != ? AND deleted IS NULL
    ORDER BY RAND()
    LIMIT 3
");
$suggestStmt->bind_param("i", $service_id);
$suggestStmt->execute();
$suggestResult = $suggestStmt->get_result();
$suggestedServices = [];
while ($suggestService = $suggestResult->fetch_assoc()) {
    $suggestedServices[] = $suggestService;
}

// Xử lý form đặt dịch vụ
$booking_message = '';
$booking_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_service'])) {
    $customer_name = trim($_POST['customer_name']);
    $customer_phone = trim($_POST['customer_phone']);
    $customer_email = trim($_POST['customer_email']);
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    $number_of_people = intval($_POST['number_of_people']);
    $special_request = trim($_POST['special_request']);
    
    if (empty($customer_name) || empty($customer_phone) || empty($booking_date) || empty($booking_time)) {
        $booking_error = "Vui lòng điền đầy đủ thông tin bắt buộc!";
    } else {
        $sql_booking = "INSERT INTO service_booking 
                        (service_id, customer_name, customer_phone, customer_email, 
                         booking_date, booking_time, number_of_people, special_request, 
                         booking_status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        $stmt_booking = $mysqli->prepare($sql_booking);
        $stmt_booking->bind_param("isssssis", 
            $service_id, $customer_name, $customer_phone, $customer_email,
            $booking_date, $booking_time, $number_of_people, $special_request
        );
        
        if ($stmt_booking->execute()) {
            $booking_message = "Đặt dịch vụ thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.";
        } else {
            $booking_error = "Có lỗi xảy ra. Vui lòng thử lại!";
        }
    }
}
?>

<script>
window.IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
window.CURRENT_USER_NAME = "<?php echo htmlspecialchars($userName); ?>";
window.SERVICE_ID = <?php echo $service_id; ?>;
window.SERVICE_NAME = "<?php echo htmlspecialchars($service['service_name']); ?>";
window.SERVICE_PRICE = <?php echo $service['price']; ?>;
</script>

<style>
:root {
    --primary-color: #deb666;
    --primary-dark: #c9a155;
    --primary-light: #e8c784;
}

.header-title {
    background: #eee;
    padding: 40px 0;
    text-align: center;
    color: white;
    margin-bottom: 30px;
}

.header-title h1 {
    color: black;
    font-size: 2.5rem;
    font-weight: bold;
    margin: 0;
}

.detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px 50px;
}

.main-content {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
    margin-bottom: 50px;
}

.service-info {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.image-section {
    margin-bottom: 30px;
}

.main-image-container {
    position: relative;
    height: 400px;
    border-radius: 10px;
    overflow: hidden;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.main-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.main-image-container::before {
    content: '';
    position: absolute;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    top: -100px;
    right: -50px;
}

.service-meta {
    display: flex;
    gap: 30px;
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.8rem;
    color: #495057;
}

.meta-item i {
    color: var(--primary-color);
}

.service-description {
    margin-bottom: 30px;
}

.service-description h2 {
    font-size: 2rem;
    margin-bottom: 15px;
    color: #212529;
    position: relative;
    padding-bottom: 10px;
}

.service-description h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: var(--primary-color);
}

.service-description p {
    color: #495057;
    line-height: 1.8;
    text-align: justify;
}

.features-section {
    margin-bottom: 30px;
}

.features-section h2 {
    font-size: 2rem;
    margin-bottom: 20px;
    color: #212529;
    position: relative;
    padding-bottom: 10px;
}

.features-section h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: var(--primary-color);
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: transform 0.3s;
}

.feature-item:hover {
    transform: translateX(5px);
    background: #e9ecef;
}

.feature-item i {
    color: var(--primary-color);
    font-size: 1.5rem;
}

/* Booking Card */
.booking-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 20px;
    height: fit-content;
}

.price-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.price {
    font-size: 2rem;
    font-weight: bold;
    color: var(--primary-color);
}

.price span {
    font-size: 1.5rem;
    color: #6c757d;
    font-weight: normal;
}

.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 1.5rem;
    font-weight: 600;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.booking-form .form-group {
    margin-bottom: 20px;
}

.booking-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #495057;
}

.booking-form input,
.booking-form select,
.booking-form textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1.5rem;
    transition: border-color 0.3s;
}

.booking-form input:focus,
.booking-form select:focus,
.booking-form textarea:focus {
    outline: none;
    border-color: var(--primary-color);
}

.check-service-btn {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.3s;
}

.check-service-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(222, 182, 102, 0.4);
}

.check-service-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

/* Suggestions Section */
.suggestions-section {
    margin-top: 50px;
}

.section-title {
    font-size: 2.5rem;
    margin-bottom: 30px;
    text-align: center;
    color: #212529;
    position: relative;
    padding-bottom: 15px;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: var(--primary-color);
}

.suggestions {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
}

.suggestion-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
}

.suggestion-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
}

.suggestion-image {
    height: 200px;
    background-size: cover;
    background-position: center;
    background-color: #e9ecef;
}

.suggestion-content {
    padding: 20px;
}

.suggestion-title {
    font-size: 1.8rem;
    margin-bottom: 10px;
    color: #212529;
}

.suggestion-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 10px;
    color: #6c757d;
}

.suggestion-price {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.suggestion-price .price {
    font-size: 1.8rem;
    color: var(--primary-color);
    font-weight: bold;
}

.view-btn {
    padding: 10px 20px;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: background 0.3s;
}

.view-btn:hover {
    background: var(--primary-dark);
    color: white;
}

@media (max-width: 992px) {
    .main-content {
        grid-template-columns: 1fr;
    }

    .booking-card {
        position: static;
    }
}

@media (max-width: 768px) {
    .header-title h1 {
        font-size: 2rem;
    }

    .suggestions {
        grid-template-columns: 1fr;
    }
}
</style>

<main>
    <div class="header-title">
        <h1><?php echo htmlspecialchars($service['service_name']); ?></h1>
    </div>

    <div class="detail-container">
        <div class="main-content">
            <div class="service-info">
                <!-- Image Section -->
                <div class="image-section">
                    <div class="main-image-container">
                        <?php if (!empty($service['image'])): ?>
                        <img src="/My-Web-Hotel/uploads/images/<?php echo htmlspecialchars($service['image']); ?>"
                            alt="<?php echo htmlspecialchars($service['service_name']); ?>">
                        <?php else: ?>
                        <i class="bi bi-image" style="font-size: 5rem; color: white;"></i>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Meta Info -->
                <div class="service-meta">
                    <div class="meta-item">
                        <i class="bi bi-tag-fill"></i>
                        <span><?php echo htmlspecialchars($service['service_type']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="bi bi-rulers"></i>
                        <span><?php echo htmlspecialchars($service['unit']); ?></span>
                    </div>
                </div>

                <!-- Description -->
                <div class="service-description">
                    <h2>Mô Tả Dịch Vụ</h2>
                    <p><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                </div>

                <!-- Features -->
                <div class="features-section">
                    <h2>Đặc Điểm Dịch Vụ</h2>
                    <div class="features-grid">
                        <div class="feature-item">
                            <i class="bi bi-clock"></i>
                            <span>Phục vụ 24/7</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-star-fill"></i>
                            <span>Chất lượng cao cấp</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-people"></i>
                            <span>Đội ngũ chuyên nghiệp</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-shield-check"></i>
                            <span>An toàn & Vệ sinh</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-gem"></i>
                            <span>Tiện nghi hiện đại</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-headset"></i>
                            <span>Hỗ trợ tận tình</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Card -->
            <div class="booking-card">
                <div class="price-section">
                    <div class="price">
                        <?php echo number_format($service['price'], 0, ',', '.'); ?>₫
                        <span>/<?php echo htmlspecialchars($service['unit']); ?></span>
                    </div>
                    <span
                        class="status-badge <?php echo strtolower(trim($service['status'])) == 'active' ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo strtolower(trim($service['status'])) == 'active' ? 'Đang hoạt động' : 'Ngừng hoạt động'; ?>
                    </span>
                </div>

                <?php if (strtolower(trim($service['status'])) == 'active'): ?>
                <form class="booking-form" id="bookingForm">
                    <div class="form-group">
                        <label>Ngày sử dụng</label>
                        <input type="date" id="booking_date" name="booking_date" min="<?php echo date('Y-m-d'); ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Thời gian</label>
                        <input type="time" id="booking_time" name="booking_time" required>
                    </div>
                    <div class="form-group">
                        <label>Số người</label>
                        <input type="number" id="number_of_people" name="number_of_people" min="1" value="1">
                    </div>
                    <button type="button" class="check-service-btn" id="check-service-btn">
                        Đặt Dịch Vụ Ngay
                    </button>
                </form>
                <?php else: ?>
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i> Dịch vụ hiện đang ngừng hoạt động
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Popup đăng nhập -->
        <div class="modal fade" id="loginPopup" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Yêu cầu đăng nhập</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn chưa đăng nhập! Vui lòng đăng nhập để tiếp tục đặt dịch vụ.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <a href="/My-Web-Hotel/client/pages/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                            class="btn btn-primary">
                            Đăng nhập
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popup form đặt dịch vụ -->
        <div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Thông Tin Đặt Dịch Vụ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="">
                        <div class="modal-body">
                            <?php if ($booking_message): ?>
                            <div class="alert alert-success"><?php echo $booking_message; ?></div>
                            <?php endif; ?>
                            <?php if ($booking_error): ?>
                            <div class="alert alert-danger"><?php echo $booking_error; ?></div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="customer_phone" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="customer_email">
                            </div>
                            <input type="hidden" name="booking_date" id="hidden_booking_date">
                            <input type="hidden" name="booking_time" id="hidden_booking_time">
                            <input type="hidden" name="number_of_people" id="hidden_number_of_people">
                            <div class="mb-3">
                                <label class="form-label">Yêu cầu đặc biệt</label>
                                <textarea class="form-control" name="special_request" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" name="book_service" class="btn btn-primary">Xác Nhận Đặt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Suggestions Section -->
        <?php if (!empty($suggestedServices)): ?>
        <div class="suggestions-section">
            <h2 class="section-title">Các Dịch Vụ Khác Bạn Có Thể Thích</h2>
            <div class="suggestions">
                <?php foreach ($suggestedServices as $suggested): ?>
                <div class="suggestion-card">
                    <div class="suggestion-image"
                        style="background-image: url('/My-Web-Hotel/uploads/images/<?php echo !empty($suggested['image']) ? htmlspecialchars($suggested['image']) : 'default-service.jpg'; ?>');">
                    </div>
                    <div class="suggestion-content">
                        <h3 class="suggestion-title"><?php echo htmlspecialchars($suggested['service_name']); ?></h3>
                        <div class="suggestion-meta">
                            <span><i class="bi bi-tag"></i>
                                <?php echo htmlspecialchars($suggested['service_type']); ?></span>
                            <span><i class="bi bi-rulers"></i>
                                <?php echo htmlspecialchars($suggested['unit']); ?></span>
                        </div>
                        <p style="color: #666; font-size: 0.95rem;">
                            <?php echo mb_substr(htmlspecialchars($suggested['description']), 0, 100) . '...'; ?>
                        </p>
                        <div class="suggestion-price">
                            <div class="price"><?php echo number_format($suggested['price'], 0, ',', '.'); ?>₫</div>
                            <a href="/My-Web-Hotel/client/index.php?page=serviceDetail&id=<?php echo $suggested['service_id']; ?>"
                                class="view-btn">Xem Chi Tiết</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const checkBtn = document.getElementById("check-service-btn");
    const loginModalEl = document.getElementById("loginPopup");
    const bookingModalEl = document.getElementById("bookingModal");

    const loginModal = new bootstrap.Modal(loginModalEl);
    const bookingModal = new bootstrap.Modal(bookingModalEl);

    if (checkBtn) {
        checkBtn.addEventListener("click", (e) => {
            e.preventDefault();

            if (!window.IS_LOGGED_IN) {
                loginModal.show();
            } else {
                const bookingDate = document.getElementById("booking_date").value;
                const bookingTime = document.getElementById("booking_time").value;
                const numberOfPeople = document.getElementById("number_of_people").value;

                if (!bookingDate || !bookingTime) {
                    alert("Vui lòng chọn ngày và thời gian!");
                    return;
                }

                // Set hidden fields
                document.getElementById("hidden_booking_date").value = bookingDate;
                document.getElementById("hidden_booking_time").value = bookingTime;
                document.getElementById("hidden_number_of_people").value = numberOfPeople;

                bookingModal.show();
            }
        });
    }

    <?php if ($booking_message || $booking_error): ?>
    bookingModal.show();
    <?php endif; ?>
});
</script>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">