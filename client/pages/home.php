<?php
$sql = "SELECT 
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
        WHERE r.status = 'available'
        ORDER BY room_price DESC 
        LIMIT 10";

$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();


$bestRooms = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bestRooms[] = $row;
    }
}
?>

<main>
    <!-- Hero -->
    <section class="hero">
        <div class="hero-content">
            <div data-aos="fade-down" data-aos-duration="1000">
                <h1>Cảm nhận <span>sự thoải mái</span></h1>
                <p>VỚI DỊCH VỤ TỐT NHẤT CỦA CHÚNG TÔI</p>
            </div>
            <div class="buttons" data-aos="fade-up" data-aos-duration="1000">
                <button class="btn btn-booking"
                    onclick="window.location.href = '/My-Web-Hotel/client/index.php?page=room'">
                    ĐẶT PHÒNG NGAY
                </button>
                <button class="btn btn-outline"
                    onclick="window.location.href = '/My-Web-Hotel/client/index.php?page=about'">
                    TÌM HIỂU THÊM
                </button>
            </div>
        </div>
    </section>
    <!-- Rooms -->
    <section class="room-slider">
        <h2>Phòng tốt nhất</h2>
        <p>Phòng ngủ đa dạng và sang trọng cho nhiều sự lựa chọn</p>

        <div class="slider-container" data-aos="zoom-in" data-aos-duration="1000">
            <button class="btn left" onclick="moveSlide(-1)">&#10094;</button>
            <div class="slider" id="slider">
                <?php foreach ($bestRooms as $room): ?>
                <div class="slide" data-id="<?php echo $room['room_id']; ?>">
                    <img src="/My-Web-Hotel/uploads/images/<?php echo $room['room_image'] ?: '/my-web-hotel/client/assets/images/no-image.jpg'; ?>"
                        alt="Room <?php echo htmlspecialchars($room['room_number']); ?>" />

                    <h3>Phòng <?php echo htmlspecialchars($room['room_number']); ?></h3>
                    <p><?php echo htmlspecialchars($room['room_type']); ?></p>
                    <p><?php echo number_format($room['room_price'], 0, ',', '.'); ?>đ / đêm</p>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="btn right" onclick="moveSlide(1)">&#10095;</button>
        </div>

        <a href="/My-Web-Hotel/client/index.php?page=room#room" class="view-all room">Xem Tất Cả</a>
    </section>
    <!-- Service -->
    <section class="services">
        <div class="services-header">
            <div class="text">
                <h2>Dịch vụ nổi bật</h2>
                <p>Nhiều dịch vụ để đảm bảo sự thư giãn và thoải mái của bạn</p>
            </div>
            <a href="/My-Web-Hotel/client/index.php?page=dichVu#service" class="view-all service">Xem Tất Cả</a>
        </div>

        <div class="services-grid">
            <div class="service-card" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="100">
                <img src="/my-web-hotel/client/assets/images/Spa.jpg" alt="Spa thư giãn" />
                <h3>5 Spa thư giãn</h3>
                <p>
                    Trải nghiệm liệu pháp chăm sóc cơ thể chuyên nghiệp trong không
                    gian yên tĩnh.
                </p>
            </div>
            <div class="service-card" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                <img src="/my-web-hotel/client/assets/images/NhaAn.jpg" alt="Nhà hàng cao cấp" />
                <h3>2 Nhà hàng cao cấp</h3>
                <p>Thực đơn đa dạng từ Á đến Âu, phục vụ bởi đầu bếp 5 sao.</p>
            </div>
            <div class="service-card" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                <img src="/my-web-hotel/client/assets/images/HoBoi.jpg" alt="Hồ bơi ngoài trời" />
                <h3>10 Hồ bơi ngoài trời</h3>
                <p>Hồ bơi rộng rãi với tầm nhìn thoáng đãng và quầy bar bên hồ.</p>
            </div>
            <div class="service-card" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
                <img src="/my-web-hotel/client/assets/images/PHop.jpg" alt="Phòng họp hiện đại" />
                <h3>8 Phòng họp hiện đại</h3>
                <p>
                    Trang bị đầy đủ thiết bị hội nghị, phù hợp cho sự kiện và hội
                    thảo.
                </p>
            </div>
        </div>
    </section>
    <!-- experience -->
    <section class="experience">
        <h2>Trải nghiệm cao cấp</h2>
        <p>
            Ghi lại những trải nghiệm kỳ nghỉ của bạn tại Khách sạn Ocean Pearl
        </p>

        <div class="experience-container" data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="100">
            <button class="btn left" onclick="moveExperience(-1)">
                &#10094;
            </button>

            <div class="experience-grid" id="experience-slider"></div>

            <button class="btn right" onclick="moveExperience(1)">
                &#10095;
            </button>
        </div>
        <a href="/my-web-hotel/client/index.php?page=gallery" class="view-all">Xem Tất Cả</a>
    </section>
    <!-- Places -->
    <section class="places">
        <div class="places-header">
            <div class="text">
                <h2>Những địa điểm nổi tiếng</h2>
                <p>
                    Các địa điểm vui chơi và giải trí tuyệt vời đang chờ bạn khám phá
                </p>
            </div>
            <a href="/my-web-hotel/client/index.php?page=places" class="view-all">Xem Tất Cả</a>
        </div>

        <div class="places-grid">
            <div class="place-card" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                <img src="/my-web-hotel/client/assets/images/sun-world-hon-thom-nature-park-vietnam-water-slides.jpeg"
                    alt="Hạ Long Bay" />
                <div class="overlay">
                    <h3>Sun World</h3>
                </div>
            </div>

            <div class="place-card" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="150">
                <img src="/my-web-hotel/client/assets/images/GW-T5.2021-01-1.jpg" alt="Hội An Ancient Town" />
                <div class="overlay">
                    <h3>Grand World</h3>
                </div>
            </div>

            <div class="place-card" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                <img src="/my-web-hotel/client/assets/images/sun-grand-city-hillside-residence-6.jpg"
                    alt="Phú Quốc Island" />
                <div class="overlay">
                    <h3>Sunset Town</h3>
                </div>
            </div>
        </div>
    </section>
    <!-- Reviews -->
    <section class="testimonial">
        <h2>Đánh giá khách sạn</h2>
        <p>Khách hàng nói gì về kỳ nghỉ tại Ocean Pearl</p>
        <div class="testimonial-container" data-aos="flip-down" data-aos-duration="1000">
            <button class="btn left" onclick="moveTestimonial(-1)">
                &#10094;
            </button>

            <div class="testimonial-grid" id="testimonial-slider">
                <div class="testimonial-card">
                    <img src="/my-web-hotel/client/assets/images/user1.jpg" alt="Brad Knight" class="avatar" />
                    <h3>Brad Knight</h3>
                    <p class="location">Athens, Greece</p>
                    <div class="stars">★★★★★</div>
                    <p class="review">
                        I stay at this hotel about once a week. The staff is friendly,
                        and the breakfast is great!
                    </p>
                </div>

                <div class="testimonial-card">
                    <img src="/my-web-hotel/client/assets/images/user2.jpg" alt="Nguyễn Linh" class="avatar" />
                    <h3>Nguyễn Linh</h3>
                    <p class="location">Hà Nội, Việt Nam</p>
                    <div class="stars">★★★★☆</div>
                    <p class="review">
                        Phòng sạch sẽ, view đẹp. Nhân viên nhiệt tình. Sẽ quay lại lần
                        sau!
                    </p>
                </div>

                <div class="testimonial-card">
                    <img src="/my-web-hotel/client/assets/images/user3.jpg" alt="Tom Harris" class="avatar" />
                    <h3>Tom Harris</h3>
                    <p class="location">London, UK</p>
                    <div class="stars">★★★★★</div>
                    <p class="review">
                        Amazing location and very comfortable beds. Highly recommended!
                    </p>
                </div>

                <div class="testimonial-card">
                    <img src="/my-web-hotel/client/assets/images/user4.jpg" alt="Trần Mai" class="avatar" />
                    <h3>Trần Mai</h3>
                    <p class="location">Đà Nẵng, Việt Nam</p>
                    <div class="stars">★★★★☆</div>
                    <p class="review">
                        Khách sạn gần biển, thuận tiện di chuyển. Giá hợp lý.
                    </p>
                </div>

                <div class="testimonial-card">
                    <img src="/my-web-hotel/client/assets/images/user5.jpg" alt="Alex Kim" class="avatar" />
                    <h3>Alex Kim</h3>
                    <p class="location">Seoul, Korea</p>
                    <div class="stars">★★★★★</div>
                    <p class="review">
                        Staff were super helpful and the breakfast buffet was delicious!
                    </p>
                </div>
            </div>

            <button class="btn right" onclick="moveTestimonial(1)">
                &#10095;
            </button>
        </div>

        <a href="/My-Web-Hotel/client/index.php?page=danhGia" class="view-all">Xem Tất Cả</a>
    </section>
</main>