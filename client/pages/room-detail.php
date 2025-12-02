<?php
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['logged_in']);
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
if (!isset($_GET['id'])) {
    header("Location: /My-Web-Hotel/client/index.php?page=404");
}
if (isset($_GET['id'])) {
    $roomId = $_GET['id'];

   // Lấy thông tin phòng
$stmt = $mysqli->prepare("
    SELECT 
        r.status, 
        r.room_number,
        rt.room_type_name AS room_type, 
        rt.base_price AS room_price, 
        rt.description AS 'desc', 
        rt.capacity
    FROM room r
    JOIN room_type rt ON rt.room_type_id = r.room_type_id
    WHERE r.room_id = ?
");

$stmt->bind_param("i", $roomId);
$stmt->execute();
$roomResult = $stmt->get_result();
$room = $roomResult->fetch_assoc();


    // Lấy ảnh phòng
    $imgQuery = $mysqli->prepare("SELECT url_image FROM room_images WHERE room_id = ?");
    $imgQuery->bind_param("s", $roomId);
    $imgQuery->execute();
    $imgResult = $imgQuery->get_result();
    $images = [];
    while ($img = $imgResult->fetch_assoc()) {
        $images[] = $img['url_image'];
    }

    // Lấy 3 phòng gợi ý ngẫu nhiên (loại trừ phòng hiện tại)
    $suggestStmt = $mysqli->prepare("
        SELECT 
            r.room_id,
            r.room_number,
            rt.room_type_name AS room_type,
            rt.base_price AS room_price,
            rt.capacity,
            (SELECT url_image 
             FROM room_images 
             WHERE room_images.room_id = r.room_id 
             ORDER BY RAND()
             LIMIT 1) AS room_image
        FROM room r
        JOIN room_type rt ON rt.room_type_id = r.room_type_id
        WHERE r.status = 'available' AND r.room_id != ?
        ORDER BY RAND()
        LIMIT 3
    ");
    
    $suggestStmt->bind_param("i", $roomId);
    $suggestStmt->execute();
    $suggestResult = $suggestStmt->get_result();
    $suggestedRooms = [];
    while ($suggestRoom = $suggestResult->fetch_assoc()) {
        $suggestedRooms[] = $suggestRoom;
    }
}
?>

<!-- Thêm script để truyền biến PHP sang JavaScript -->
<script>
window.IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
window.CURRENT_USER_NAME = "<?php echo htmlspecialchars($userName); ?>";
window.ROOM_ID = "<?php echo htmlspecialchars($roomId); ?>";
</script>

<main>
    <div class="header-title">
        <h1>Phòng <?php echo  $room["room_number"] ?> - <?php echo  $room["room_type"] ?></h1>
    </div>
    <div class="detail-container">
        <div class="main-content">
            <div class="room-info">
                <div class="image-section">
                    <div class="main-image-slider">
                        <div class="slider-track" id="sliderTrack">
                            <?php foreach ($images as $img): ?>
                            <div class="slide">
                                <img src="/My-Web-Hotel/uploads/images/<?php echo $img; ?>" alt="Room Image">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="slider-btn slider-btn-prev" onclick="moveSlide(-1)">‹</button>
                        <button class="slider-btn slider-btn-next" onclick="moveSlide(1)">›</button>
                    </div>

                    <div class="thumbnail-gallery" id="thumbnailGallery">
                        <?php foreach ($images as $index => $img): ?>
                        <div class="gallery-item <?php echo $index === 0 ? 'active' : ''; ?>"
                            onclick="goToSlide(<?php echo $index; ?>)">
                            <img src="/My-Web-Hotel/uploads/images/<?php echo $img; ?>"
                                alt="Thumb <?php echo $index + 1; ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>
                <div class="room-meta">
                    <div class="meta-item">
                        <span><i class="fa-solid fa-ruler fa-lg"></i></span>
                        <span>45m²</span>
                    </div>
                    <div class="meta-item">
                        <span><i class="fa-solid fa-person fa-lg"></i></span>
                        <span><?php echo  $room["capacity"] ?> khách</span>
                    </div>
                </div>

                <div class="room-description">
                    <h2>Mô Tả Phòng</h2>
                    <p><?php echo nl2br($room['desc']); ?></p>
                </div>

                <div class="amenities-section">
                    <h2>Tiện Nghi Phòng</h2>
                    <div class="amenities-grid">
                        <div class="amenity-item">
                            <span class="amenity-icon"><i class="fa-solid fa-wifi fa-lg"></i></span>
                            <span>WiFi tốc độ cao miễn phí</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon"><i class="fa-solid fa-snowflake fa-lg"></i></span>
                            <span>Điều hòa không khí</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon"><i class="fa-solid fa-tv fa-lg"></i></span>
                            <span>Smart TV 55" 4K</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon"><i class="fa-solid fa-lock fa-lg"></i></span>
                            <span>Két an toàn điện tử</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon"><i class="fa-solid fa-mug-hot fa-lg"></i></span>
                            <span>Máy pha cà phê Nespresso</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon"><i class="fa-solid fa-martini-glass fa-lg"></i></span>
                            <span>Minibar đầy đủ</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon"><i class="fa-solid fa-temperature-arrow-up fa-lg"></i></span>
                            <span>Máy sấy tóc cao cấp</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon"><i class="fa-solid fa-shirt fa-lg"></i></span>
                            <span>Tủ quần áo rộng rãi</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon"><i class="fa-solid fa-bottle-droplet fa-lg"></i></span>
                            <span>Đồ dùng cao cấp L'Occitane</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon"><i class="fa-solid fa-bed fa-lg"></i></span>
                            <span>Ga trải Cotton Ai Cập</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon"><i class="fa-solid fa-phone-volume fa-lg"></i></span>
                            <span>Điện thoại liên lạc 24/7</span>
                        </div>
                        <div class="amenity-item">
                            <span class="amenity-icon"><i class="fa-solid fa-couch fa-lg"></i></span>
                            <span>Ban công riêng có ghế</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="booking-card">
                <div class="price-section">
                    <div class="price">
                        <?php echo number_format($room["room_price"])  ?>₫ <span>/đêm</span>
                        <span class="original-price"><?php echo number_format($room["room_price"]*100/83)?>₫</span>
                    </div>
                    <div class="discount-badge">Giảm 17%</div>
                </div>

                <form class="booking-form">
                    <div class="form-group">
                        <label>Ngày nhận phòng</label>
                        <input type="date" id="checkin" name="checkin">
                    </div>
                    <div class="form-group">
                        <label>Ngày trả phòng</label>
                        <input type="date" id="checkout" name="checkout">
                    </div>
                    <div class="form-group">
                        <label>Số lượng khách</label>
                        <div class="guests-group">
                            <select id="adults">
                                <option>1 Người lớn</option>
                                <option selected>2 Người lớn</option>
                                <option>3 Người lớn</option>
                            </select>
                            <select id="children">
                                <option selected>0 Trẻ em</option>
                                <option>1 Trẻ em</option>
                                <option>2 Trẻ em</option>
                            </select>
                        </div>
                    </div>
                </form>
                <button class="check-room-btn" id="check-room-btn">Đặt Phòng Ngay</button>
            </div>
        </div>

        <!-- Popup thông báo -->
        <div class="modal fade" id="loginPopup" tabindex="-1" aria-labelledby="loginPopupLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginPopupLabel">Yêu cầu đăng nhập</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn chưa đăng nhập! Vui lòng đăng nhập để tiếp tục.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <a href="/My-Web-Hotel/client/pages/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                            class="btn btn-primary">
                            <i class="fa fa-sign-in-alt me-2"></i>Đăng nhập
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gợi ý phòng từ CSDL -->
        <div class="suggestions-section">
            <h2 class="section-title">Các Phòng Khác Bạn Có Thể Thích</h2>
            <div class="suggestions">
                <?php if (!empty($suggestedRooms)): ?>
                <?php 
                    
                    foreach ($suggestedRooms as $index => $suggested):     
                        // Tính giá giảm 17%
                        $discountedPrice = $suggested['room_price'];
                        $originalPrice = round($suggested['room_price'] * 100 / 83);
                    ?>
                <div class="suggestion-card">
                    <div class="suggestion-image"
                        style="background-image: url('/My-Web-Hotel/uploads/images/<?php echo htmlspecialchars($suggested['room_image']); ?>');">
                    </div>
                    <div class="suggestion-content">
                        <h3 class="suggestion-title">
                            Phòng <?php echo htmlspecialchars($suggested['room_number']); ?> -
                            <?php echo htmlspecialchars($suggested['room_type']); ?>
                        </h3>
                        <div class="suggestion-meta">
                            <span><i class="fa-solid fa-ruler"></i> 45m²</span>
                            <span><i class="fa-solid fa-person fa-lg"></i> <?php echo $suggested['capacity']; ?>
                                khách</span>
                        </div>
                        <p style="color: #666; font-size: 1.5rem;">
                            <?php echo htmlspecialchars($suggested['room_type']); ?> với thiết kế sang trọng và đầy đủ
                            tiện nghi hiện đại.
                        </p>
                        <div class="suggestion-price">
                            <div>
                                <div class="price"><?php echo number_format($discountedPrice); ?>₫</div>
                                <span style="color: #999; font-size: 1.4rem; text-decoration: line-through;">
                                    <?php echo number_format($originalPrice); ?>₫
                                </span>
                                <span style="color: #999; font-size: 1.4rem;">/đêm</span>
                            </div>
                            <a href="/My-Web-Hotel/client/index.php?page=room-detail&id=<?php echo $suggested['room_id']; ?>"
                                class="view-btn">Xem Chi Tiết</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p style="text-align: center; padding: 20px; color: #666;">
                    Hiện không có phòng gợi ý nào khác.
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<script>
// Kiểm tra tình trạng đăng nhập và xử lý đặt phòng
document.addEventListener("DOMContentLoaded", () => {
    const checkBtn = document.getElementById("check-room-btn");
    const loginModalEl = document.getElementById("loginPopup");

    // Khởi tạo modal Bootstrap
    const loginModal = new bootstrap.Modal(loginModalEl);

    if (checkBtn) {
        checkBtn.addEventListener("click", (e) => {
            e.preventDefault(); // Ngăn hành động mặc định

            // Kiểm tra đã đăng nhập chưa
            if (!window.IS_LOGGED_IN) {
                // Nếu chưa đăng nhập → hiển thị modal yêu cầu đăng nhập
                loginModal.show();
            } else {
                // Lấy thông tin từ form
                const checkinDate = document.getElementById("checkin").value;
                const checkoutDate = document.getElementById("checkout").value;
                const adultsSelect = document.getElementById("adults");
                const childrenSelect = document.getElementById("children");
                const roomId = window.ROOM_ID;

                // Kiểm tra ngày đã được chọn chưa
                if (!checkinDate || !checkoutDate) {
                    alert("Vui lòng chọn ngày nhận phòng và trả phòng!");
                    return;
                }

                // Kiểm tra ngày checkout phải sau checkin
                if (new Date(checkoutDate) <= new Date(checkinDate)) {
                    alert("Ngày trả phòng phải sau ngày nhận phòng!");
                    return;
                }

                // Lấy giá trị số người từ select
                const adultsValue = adultsSelect.selectedIndex + 1; // 1, 2, 3
                const childrenValue = childrenSelect.selectedIndex; // 0, 1, 2

                // Lấy thông tin phòng từ window (được set từ PHP)
                const roomData = {
                    roomId: roomId,
                    roomName: window.ROOM_NAME || "",
                    roomType: window.ROOM_TYPE || "",
                    roomPrice: window.ROOM_PRICE || 0,
                    checkin: checkinDate,
                    checkout: checkoutDate,
                    adults: adultsValue,
                    children: childrenValue,
                    maxGuests: window.MAX_GUESTS || 2,
                };

                // Lưu dữ liệu vào sessionStorage
                sessionStorage.setItem("bookingData", JSON.stringify(roomData));

                // Chuyển hướng đến trang booking
                window.location.href =
                    "/My-Web-Hotel/client/index.php?page=booking&id=" + roomId;
            }
        });
    }
});
</script>