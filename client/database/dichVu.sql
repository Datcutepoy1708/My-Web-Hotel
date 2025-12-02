-- Tạo bảng dịch vụ khách sạn
CREATE TABLE dich_vu_khach_san (
    ma_dich_vu INT PRIMARY KEY AUTO_INCREMENT,
    loai_hinh ENUM('Spa', 'Ăn uống', 'Giải trí', 'Sự kiện') NOT NULL,
    ten_dich_vu VARCHAR(200) NOT NULL,
    gia DECIMAL(10, 2) NOT NULL,
    don_vi VARCHAR(50) NOT NULL,
    thoi_gian_phuc_vu VARCHAR(100),
    trang_thai ENUM('Đang hoạt động', 'Dừng hoạt động') DEFAULT 'Đang hoạt động',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Thêm chỉ mục để tăng hiệu suất truy vấn
CREATE INDEX idx_loai_hinh ON dich_vu_khach_san(loai_hinh);
CREATE INDEX idx_trang_thai ON dich_vu_khach_san(trang_thai);

-- Thêm dữ liệu mẫu
INSERT INTO dich_vu_khach_san (loai_hinh, ten_dich_vu, gia, don_vi, thoi_gian_phuc_vu, trang_thai) VALUES
-- Dịch vụ Spa
('Spa', 'Massage body thư giãn', 500000, 'Buổi/60 phút', '9:00 - 22:00', 'Đang hoạt động'),
('Spa', 'Chăm sóc da mặt cao cấp', 800000, 'Buổi/90 phút', '9:00 - 22:00', 'Đang hoạt động'),
('Spa', 'Tắm trắng toàn thân', 1200000, 'Buổi/120 phút', '9:00 - 20:00', 'Đang hoạt động'),
('Spa', 'Massage đá nóng', 700000, 'Buổi/75 phút', '10:00 - 21:00', 'Dừng hoạt động'),

-- Dịch vụ Ăn uống
('Ăn uống', 'Buffet sáng quốc tế', 350000, 'Người', '6:00 - 10:00', 'Đang hoạt động'),
('Ăn uống', 'Set menu trưa', 450000, 'Set', '11:30 - 14:00', 'Đang hoạt động'),
('Ăn uống', 'BBQ ngoài trời', 650000, 'Người', '18:00 - 22:00', 'Đang hoạt động'),
('Ăn uống', 'Tiệc cocktail', 250000, 'Người', '17:00 - 23:00', 'Đang hoạt động'),
('Ăn uống', 'Dinner cao cấp 5 món', 1500000, 'Set', '18:30 - 22:00', 'Dừng hoạt động'),

-- Dịch vụ Giải trí
('Giải trí', 'Bể bơi vô cực', 200000, 'Người/ngày', '6:00 - 22:00', 'Đang hoạt động'),
('Giải trí', 'Phòng gym & yoga', 150000, 'Người/ngày', '5:00 - 23:00', 'Đang hoạt động'),
('Giải trí', 'Karaoke VIP', 500000, 'Giờ', '19:00 - 2:00', 'Đang hoạt động'),
('Giải trí', 'Sân tennis', 300000, 'Giờ', '6:00 - 21:00', 'Đang hoạt động'),
('Giải trí', 'Tour tham quan thành phố', 800000, 'Người', '8:00 - 17:00', 'Dừng hoạt động'),

-- Dịch vụ Sự kiện
('Sự kiện', 'Tổ chức hội nghị (50 người)', 15000000, 'Sự kiện', 'Theo yêu cầu', 'Đang hoạt động'),
('Sự kiện', 'Tiệc cưới (200 khách)', 80000000, 'Sự kiện', 'Theo yêu cầu', 'Đang hoạt động'),
('Sự kiện', 'Team building ngoài trời', 5000000, 'Nhóm/20 người', '8:00 - 17:00', 'Đang hoạt động'),
('Sự kiện', 'Sinh nhật VIP', 10000000, 'Sự kiện', 'Theo yêu cầu', 'Đang hoạt động'),
('Sự kiện', 'Họp báo & ra mắt sản phẩm', 25000000, 'Sự kiện', 'Theo yêu cầu', 'Dừng hoạt động');

-- Các câu truy vấn hữu ích
-- Xem tất cả dịch vụ đang hoạt động
-- SELECT * FROM dich_vu_khach_san WHERE trang_thai = 'Đang hoạt động';

-- Xem dịch vụ theo loại hình
-- SELECT * FROM dich_vu_khach_san WHERE loai_hinh = 'Spa';

-- Xem dịch vụ theo khoảng giá
-- SELECT * FROM dich_vu_khach_san WHERE gia BETWEEN 500000 AND 1000000;
CREATE TABLE login_tokens (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    token VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
CREATE TABLE room_images (
    ID INT(11) NOT NULL AUTO_INCREMENT,
    room_id INT(11) NOT NULL,
    url_image VARCHAR(255) NOT NULL,
    description TEXT NULL,
    PRIMARY KEY (ID),
    FOREIGN KEY (room_id) REFERENCES room(room_id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);
INSERT INTO room_images (room_id, url_image, description) VALUES
(1, 'afe571421a3a4f39ef1b251c3a13287c.jpg', 'Ảnh phòng mẫu'),
(1, 'demo.jpg', 'Ảnh demo'),
(1, 'family.jpg', 'Phòng gia đình'),
(1, 'Pdoi.png', 'Phòng đôi'),
(1, 'Pdon.jpg', 'Phòng đơn'),
(1, 'phong-president-suite.jpg', 'Phòng President Suite');
