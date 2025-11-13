<main>
    <div class="hero-booking">
        <div class="overlay">
            <div class="booking-container">
                <h1 data-aos="fade-down" data-aos-duration="1000">Phòng & Suites</h1>

                <div class="booking-form" data-aos="fade-up" data-aos-duration="1000">
                    <div class="date-group">
                        <label><span>Ngày nhận</span>
                            <input type="date" id="checkin" name="checkin" />
                        </label>

                        <label><span>Ngày trả</span>
                            <input type="date" id="checkout" name="checkout" />
                        </label>
                    </div>

                    <div class="selector-group">
                        <div class="selector">
                            <label>Người lớn</label>
                            <div class="counter">
                                <button onclick="changeCount('adults', -1)">−</button>
                                <span id="adults">0</span>
                                <button onclick="changeCount('adults', 1)">+</button>
                            </div>
                        </div>

                        <div class="selector">
                            <label>Trẻ em</label>
                            <div class="counter">
                                <button onclick="changeCount('children', -1)">−</button>
                                <span id="children">0</span>
                                <button onclick="changeCount('children', 1)">+</button>
                            </div>
                        </div>

                        <div class="selector">
                            <label>Phòng</label>
                            <div class="counter">
                                <button onclick="changeCount('rooms', -1)">−</button>
                                <span id="rooms">0</span>
                                <button onclick="changeCount('rooms', 1)">+</button>
                            </div>
                        </div>
                    </div>

                    <div class="summary">
                        <span id="summary-text">0 Người lớn | 0 Trẻ em | 0 Phòng</span>
                    </div>

                    <button class="book-btn">ĐẶT PHÒNG NGAY</button>
                </div>
            </div>
        </div>
    </div>
    <div class="rooms-header">
        <h1>OceanPearl Rooms & Suites</h1>
        <p>
            OceanPearl Hotel là sự kết hợp giữa kiến ​​trúc cổ điển và thiết kế
            hiện đại, với 280 phòng và suite sang trọng có nội thất phong cách và
            cao cấp. Màu sắc nhẹ nhàng trong mỗi phòng tạo ra bầu không khí thư
            giãn mang đến cho khách doanh nhân và khách du lịch một nơi ẩn náu thư
            thái sau một ngày khám phá Phú Quốc.
        </p>
    </div>

    <div class="rooms-container">
        <div class="filter-div">
            <div class="filter-panel">
                <h3>Tìm kiếm phòng</h3>
                <input type="text" placeholder="Nhập loại phòng để tìm…" class="search-box" />

                <div class="sort-dropdown">
                    <label for="sort">Sắp xếp:</label>
                    <select id="sort" name="sort">
                        <option value="popular">Phổ biến nhất</option>
                        <option value="price-low">Giá thấp đến cao</option>
                        <option value="price-high">Giá cao đến thấp</option>
                    </select>
                </div>

                <button class="apply-btn">Áp dụng</button>
            </div>
        </div>
        <!-- danh sach phong -->
        <div class="rooms-list-div" id="room">
            <div class="rooms-list-header">
                <h2>Danh sách phòng</h2>
            </div>

            <div class="rooms-main">
                <?php
$sql = "SELECT p.MaPhong, p.LoaiPhong, p.GiaPhong, p.TrangThai, p.MoTa, p.SoNguoi, 
(SELECT DuongDanAnh FROM AnhPhong a WHERE a.MaPhong = p.MaPhong LIMIT 1) AS DuongDanAnh FROM phong p";
$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        switch ($row["TrangThai"]) {
    case 'dat':
        $trangThaiHienThi = "Đã được đặt";
        $colorTrangThai = "text-danger"; // đỏ
        break;
    case 'trong':
        $trangThaiHienThi = "Còn trống";
        $colorTrangThai = "text-success"; // xanh
        break;
    case 'baotri':
        $trangThaiHienThi = "Đang bảo trì";
        $colorTrangThai = "text-warning"; // vàng
        break;
    default:
        $trangThaiHienThi = "Không xác định";
        $colorTrangThai = "text-muted"; // xám
}
        echo '<div class="card" style="max-width: 90%">';
        echo '  <div class="row g-0">';
        echo '    <div class="picture col-md-4">';
        echo '      <img src="/My-Web-Hotel/' . $row["DuongDanAnh"] . '" class="img-fluid rounded-start" alt="' . $row["LoaiPhong"] . '" />';
        echo '    </div>';
        echo '    <div class="information col-md-5">';
        echo '      <div class="card-body">';
        echo '        <h3 class="card-title"> Phòng ' . $row["LoaiPhong"] . ' [ ' . $row["MaPhong"] . ' ]</h3>';
        echo '        <p class="card-text"><span>Diện tích: 38m²</span> </p>';
        echo '        <p class="card-text"> <span>Tối đa: '.  $row["SoNguoi"] .' khách</span> </p>';
        echo '        <p class="card-text">Trạng thái:<span class="' . $colorTrangThai . '"> ' . $trangThaiHienThi . '</span></p>';        
        echo '      </div>';
        echo '    </div>';
        echo '    <div class="money col-md-3">';
        echo '      <h2>' . number_format($row["GiaPhong"]) . 'đ</h2>';
        echo '      <p>mỗi đêm</p>';
        echo "      <a href='/My-Web-Hotel/client/index.php?page=room-detail&id=" . $row['MaPhong'] . "' class='view-all' >Chi tiết</a>";
        echo '    </div>';
        echo '  </div>';
        echo '</div>';
    }
} else {
    echo '<p>Không có phòng nào để hiển thị.</p>';
}
?>
            </div>
        </div>
    </div>
</main>