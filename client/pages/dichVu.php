    <main>
        <!-- Banner chính -->
        <section class="hero">
            <div class="hero-text">
                <h1>DỊCH VỤ ĐẲNG CẤP</h1>
                <p>
                    Tận hưởng không gian sang trọng, tiện nghi và đẳng cấp bậc nhất.
                </p>
            </div>
        </section>

        <!-- Dịch vụ nổi bật -->
        <section class="services" id="services">
            <h2>DỊCH VỤ CỦA CHÚNG TÔI</h2>
            <div class="service-grid">
                <a href="/My-Web-Hotel/client/index.php?page=spa" class="service-card spa">
                    <h3>Sức khoẻ & Spa</h3>
                </a>
                <a href="/My-Web-Hotel/client/index.php?page=giaiTri" class="service-card entertain">
                    <h3>Giải trí</h3>
                </a>
                <a href="/My-Web-Hotel/client/index.php?page=restaurant" class="service-card restaurant">
                    <h3>Nhà hàng</h3>
                </a>
                <a href="/My-Web-Hotel/client/index.php?page=suKien" class="service-card event">
                    <h3>Hội nghị & Sự kiện</h3>
                </a>
            </div>
        </section>

        <!-- Giới thiệu -->
        <section class="about">
            <h2>TRẢI NGHIỆM SANG TRỌNG</h2>
            <p>
                Chúng tôi mang đến không gian nghỉ dưỡng và dịch vụ cao cấp, giúp
                khách hàng tận hưởng kỳ nghỉ hoàn hảo.
            </p>
        </section>

        <!-- Danh sách dịch vụ -->
        <div class="services-list-container">
         <div class="filter-div">
             <div class="filter-panel">
                 <h3>Tìm kiếm dịch vụ</h3>
                 <input type="text" placeholder="Nhập loại dịch vụ để tìm…" class="search-box" />

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
         <div class="services-list-div" id="room">
             <div class="services-list-header">
                 <h2>Danh sách dịch vụ</h2>
             </div>

             <div class="services-main">
                 <div class="card" style="max-width: 90%">
                     <div class="row g-0">
                         <div class="picture col-md-4">
                             <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=400"
                                 alt="Spa Service" class="service-image" />
                         </div>
                         <div class=" information col-md-5">
                             <div class="card-body">
                                 <h3 class="card-title">Spa & Massage</h3>
                                 <p><strong>Thời gian:</strong> 60-90 phút</p>
                                 <p>
                                     <strong>Loại hình:</strong> Massage, Chăm sóc da, Trị liệu
                                 </p>
                                 <p>
                                     <strong>Trạng thái:</strong>
                                     <span class="text-success">Đang hoạt động</span>
                                 </p>
                             </div>
                         </div>
                         <div class="money col-md-3">
                             <h2>500.000₫</h2>
                             <p>mỗi buổi</p>
                             <a href=/My-Web-Hotel/client/index.php?page=room-detail&id=" . $row[MaPhong] . "
                                 class=view-all>Chi tiết</a>
                         </div>
                     </div>
                 </div>
                 <div class="card" style="max-width: 90%">
                     <div class="row g-0">
                         <div class="picture col-md-4">
                             <img src="https://th.bing.com/th/id/OIP.uPRR5uBFkU9bqxSiMb3WzwHaEK?w=500" alt="Spa Service"
                                 class="service-image" />
                         </div>
                         <div class=" information col-md-5">
                             <div class="card-body">
                                 <h3 class="card-title">Buffet BBQ</h3>
                                 <p><strong>Thời gian:</strong> 60-90 phút</p>
                                 <p>
                                     <strong>Loại hình:</strong> Ăn uống, buffet
                                 </p>
                                 <p>
                                     <strong>Trạng thái:</strong>
                                     <span class="text-success">Đang hoạt động</span>
                                 </p>
                             </div>
                         </div>
                         <div class="money col-md-3">
                             <h2>180.000₫</h2>
                             <p>mỗi người</p>
                             <a href=/My-Web-Hotel/client/index.php?page=room-detail&id=" . $row[MaPhong] . "
                                 class=view-all>Chi tiết</a>
                         </div>
                     </div>
                 </div>
                 <div class="card" style="max-width: 90%">
                     <div class="row g-0">
                         <div class="picture col-md-4">
                             <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=400"
                                 alt="Spa Service" class="service-image" />
                         </div>
                         <div class=" information col-md-5">
                             <div class="card-body">
                                 <h3 class="card-title">Spa & Massage</h3>
                                 <p><strong>Thời gian:</strong> 60-90 phút</p>
                                 <p>
                                     <strong>Loại hình:</strong> Massage, Chăm sóc da, Trị liệu
                                 </p>
                                 <p>
                                     <strong>Trạng thái:</strong>
                                     <span class="text-success">Đang hoạt động</span>
                                 </p>
                             </div>
                         </div>
                         <div class="money col-md-3">
                             <h2>500.000₫</h2>
                             <p>mỗi buổi</p>
                             <a href=/My-Web-Hotel/client/index.php?page=room-detail&id=" . $row[MaPhong] . "
                                 class=view-all>Chi tiết</a>
                         </div>
                     </div>
                 </div>
                 <div class="card" style="max-width: 90%">
                     <div class="row g-0">
                         <div class="picture col-md-4">
                             <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=400"
                                 alt="Spa Service" class="service-image" />
                         </div>
                         <div class=" information col-md-5">
                             <div class="card-body">
                                 <h3 class="card-title">Spa & Massage</h3>
                                 <p><strong>Thời gian:</strong> 60-90 phút</p>
                                 <p>
                                     <strong>Loại hình:</strong> Massage, Chăm sóc da, Trị liệu
                                 </p>
                                 <p>
                                     <strong>Trạng thái:</strong>
                                     <span class="text-success">Đang hoạt động</span>
                                 </p>
                             </div>
                         </div>
                         <div class="money col-md-3">
                             <h2>500.000₫</h2>
                             <p>mỗi buổi</p>
                             <a href=/My-Web-Hotel/client/index.php?page=room-detail&id=" . $row[MaPhong] . "
                                 class=view-all>Chi tiết</a>
                         </div>
                     </div>
                 </div>
                 <div class="card" style="max-width: 90%">
                     <div class="row g-0">
                         <div class="picture col-md-4">
                             <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=400"
                                 alt="Spa Service" class="service-image" />
                         </div>
                         <div class=" information col-md-5">
                             <div class="card-body">
                                 <h3 class="card-title">Spa & Massage</h3>
                                 <p><strong>Thời gian:</strong> 60-90 phút</p>
                                 <p>
                                     <strong>Loại hình:</strong> Massage, Chăm sóc da, Trị liệu
                                 </p>
                                 <p>
                                     <strong>Trạng thái:</strong>
                                     <span class="text-success">Đang hoạt động</span>
                                 </p>
                             </div>
                         </div>
                         <div class="money col-md-3">
                             <h2>500.000₫</h2>
                             <p>mỗi buổi</p>
                             <a href=/My-Web-Hotel/client/index.php?page=room-detail&id=" . $row[MaPhong] . "
                                 class=view-all>Chi tiết</a>
                         </div>
                     </div>
                 </div>
                 <div class="card" style="max-width: 90%">
                     <div class="row g-0">
                         <div class="picture col-md-4">
                             <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=400"
                                 alt="Spa Service" class="service-image" />
                         </div>
                         <div class=" information col-md-5">
                             <div class="card-body">
                                 <h3 class="card-title">Spa & Massage</h3>
                                 <p><strong>Thời gian:</strong> 60-90 phút</p>
                                 <p>
                                     <strong>Loại hình:</strong> Massage, Chăm sóc da, Trị liệu
                                 </p>
                                 <p>
                                     <strong>Trạng thái:</strong>
                                     <span class="text-success">Đang hoạt động</span>
                                 </p>
                             </div>
                         </div>
                         <div class="money col-md-3">
                             <h2>500.000₫</h2>
                             <p>mỗi buổi</p>
                             <a href=/My-Web-Hotel/client/index.php?page=room-detail&id=" . $row[MaPhong] . "
                                 class=view-all>Chi tiết</a>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>

        <!-- Ưu điểm -->
        <section class="features">
            <div class="features-text">
                <h2>LÝ DO CHỌN CHÚNG TÔI</h2>
                <ul>
                    <li>Không gian đẳng cấp, view biển sang trọng</li>
                    <li>Dịch vụ 5 sao chuyên nghiệp</li>
                    <li>Ẩm thực tinh hoa</li>
                    <li>Hội nghị & sự kiện quy mô lớn</li>
                </ul>
            </div>
            <div class="features-img"></div>
        </section>
    </main>