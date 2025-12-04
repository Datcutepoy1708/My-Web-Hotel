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
    require_once __DIR__ . '/../includes/email_helper.php';
    
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
            $booking_service_id = $mysqli->insert_id;
            
            // Gửi email xác nhận
            if (!empty($customer_email)) {
                $bookingData = [
                    'booking_service_id' => $booking_service_id,
                    'service_name' => $service['service_name'],
                    'quantity' => $number_of_people,
                    'unit_price' => $service['price'],
                    'booking_date' => date('Y-m-d H:i:s')
                ];
                
                EmailHelper::sendServiceBookingConfirmation(
                    $customer_email,
                    $customer_name,
                    $bookingData
                );
            }
            
            $booking_message = "Đặt dịch vụ thành công! Email xác nhận đã được gửi đến " . htmlspecialchars($customer_email);
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
                        <i class="fa-solid fa-tag"></i>
                        <span><?php echo htmlspecialchars($service['service_type']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fa-solid fa-ruler-vertical"></i>
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
                            <span><i class="fa-solid fa-tag"></i>
                                <?php echo htmlspecialchars($suggested['service_type']); ?></span>
                            <span><i class="fa-solid fa-ruler-vertical"></i>
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