<?php
if (isset($_GET['id'])) {
    $MaPhong = $_GET['id'];

    // Lấy thông tin phòng
    $stmt = $mysqli->prepare("SELECT * FROM Phong WHERE MaPhong = ?");
    $stmt->bind_param("s", $MaPhong);
    $stmt->execute();
    $roomResult = $stmt->get_result();
    $room = $roomResult->fetch_assoc();

    // Lấy ảnh phòng
    $imgQuery = $mysqli->prepare("SELECT DuongDanAnh FROM AnhPhong WHERE MaPhong = ?");
    $imgQuery->bind_param("s", $MaPhong);
    $imgQuery->execute();
    $imgResult = $imgQuery->get_result();
    $images = [];
    while ($img = $imgResult->fetch_assoc()) {
        $images[] = $img['DuongDanAnh'];
    }

}
?>

<main>
    <div class="header-title">
        <h1>Deluxe Ocean View Room</h1>
    </div>
    <div class="detail-container">
        <div class="main-content">
            <div class="room-info">
                <div class="image-section">
                    <div class="main-image-slider">
                        <div class="slider-track" id="sliderTrack">
                            <?php foreach ($images as $img): ?>
                            <div class="slide">
                                <img src="/My-Web-Hotel/<?php echo $img; ?>" alt="Room Image">
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
                            <img src="/My-Web-Hotel/<?php echo $img; ?>" alt="Thumb <?php echo $index + 1; ?>">
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
                        <span><?php echo  $room["SoNguoi"] ?> khách</span>
                    </div>
                    <div class="meta-item">
                        <span><i class="fa-solid fa-bed fa-lg"></i></span>
                        <span>1 King Bed</span>
                    </div>
                </div>

                <div class="room-description">
                    <h2>Mô Tả Phòng</h2>
                    <p><?php echo nl2br($room['MoTa']); ?></p>
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
                        <?php echo number_format($room["GiaPhong"])  ?>₫ <span>/đêm</span>
                        <span class="original-price"><?php echo number_format($room["GiaPhong"]*100/83)?>₫</span>
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
                            <select>
                                <option>1 Người lớn</option>
                                <option selected>2 Người lớn</option>
                                <option>3 Người lớn</option>
                            </select>
                            <select>
                                <option selected>0 Trẻ em</option>
                                <option>1 Trẻ em</option>
                                <option>2 Trẻ em</option>
                            </select>
                        </div>
                    </div>
                </form>

                <button class="check-room-btn" id="check-room-btn" onclick="checkLoginStatus()">Đặt Phòng Ngay</button>
            </div>
        </div>
        <!-- Popup thông báo -->
        <div id="login-popup" class="popup">
            <div class="popup-content">
                <p>Bạn chưa đăng nhập! Vui lòng đăng nhập để tiếp tục.</p>
                <div class="popup-actions">
                    <button id="login-btn">Đăng nhập</button>
                    <button id="close-popup">Đóng</button>
                </div>
            </div>
        </div>
        <!-- gợi ý phòng -->
        <div class="suggestions-section">
            <h2 class="section-title">Các Phòng Khác Bạn Có Thể Thích</h2>
            <div class="suggestions">
                <div class="suggestion-card">
                    <div class="suggestion-image"
                        style="background-image: url('https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600')">
                        <div class="suggestion-badge">Phổ Biến</div>
                    </div>
                    <div class="suggestion-content">
                        <h3 class="suggestion-title">Superior Garden View</h3>
                        <div class="suggestion-meta">
                            <span><i class="fa-solid fa-ruler"></i> 38m²</span>
                            <span><i class="fa-solid fa-person fa-lg"></i> <?php echo  $room["SoNguoi"] ?> khách</span>
                            <span>🌳 Garden View</span>
                        </div>
                        <p style="color: #666; font-size: 1.5rem;">Phòng Superior với view vườn xanh mát, không gian
                            yên tĩnh và thoáng đãng.</p>
                        <div class="suggestion-price">
                            <div>
                                <div class="price">2.800.000₫</div>
                                <span style="color: #999; font-size: 1.4rem;">/đêm</span>
                            </div>
                            <button class="view-btn">Xem Chi Tiết</button>
                        </div>
                    </div>
                </div>

                <div class="suggestion-card">
                    <div class="suggestion-image"
                        style="background-image: url('https://images.unsplash.com/photo-1590490360182-c33d57733427?w=600')">
                        <div class="suggestion-badge">Sang Trọng</div>
                    </div>
                    <div class="suggestion-content">
                        <h3 class="suggestion-title">Premium Suite Ocean View</h3>
                        <div class="suggestion-meta">
                            <span><i class="fa-solid fa-ruler"></i> 65m²</span>
                            <span><i class="fa-solid fa-person fa-lg"></i> 4 khách</span>
                            <span>🌊 Ocean View</span>
                        </div>
                        <p style="color: #666; font-size: 1.5rem;">Suite cao cấp với phòng khách riêng biệt, ban công
                            rộng và jacuzzi.</p>
                        <div class="suggestion-price">
                            <div>
                                <div class="price">6.500.000₫</div>
                                <span style="color: #999; font-size: 1.4rem;">/đêm</span>
                            </div>
                            <button class="view-btn">Xem Chi Tiết</button>
                        </div>
                    </div>
                </div>

                <div class="suggestion-card">
                    <div class="suggestion-image"
                        style="background-image: url('https://images.unsplash.com/photo-1618773928121-c32242e63f39?w=600')">
                        <div class="suggestion-badge">Gia Đình</div>
                    </div>
                    <div class="suggestion-content">
                        <h3 class="suggestion-title">Family Room Sea View</h3>
                        <div class="suggestion-meta">
                            <span><i class="fa-solid fa-ruler"></i> 55m²</span>
                            <span><i class="fa-solid fa-person fa-lg"></i> 4-5 khách</span>
                            <span>🌊 Sea View</span>
                        </div>
                        <p style="color: #666; font-size: 1.5rem;">Phòng rộng rãi lý tưởng cho gia đình với 2 giường
                            đôi và sofa bed.</p>
                        <div class="suggestion-price">
                            <div>
                                <div class="price">4.800.000₫</div>
                                <span style="color: #999; font-size: 1.4rem;">/đêm</span>
                            </div>
                            <button class="view-btn">Xem Chi Tiết</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>