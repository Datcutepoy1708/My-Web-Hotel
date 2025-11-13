  <div class="main-content">
      <!-- Page Header -->
      <div class="filter-group">
          <div class="row g-3">
              <div class="col-md-5 ">
                  <button class="filter-btn" onclick="setTimeRange('today')">
                      Hôm nay
                  </button>
                  <button class="filter-btn" onclick="setTimeRange('week')">
                      Tuần này
                  </button>
                  <button class="filter-btn active" onclick="setTimeRange('month')">
                      Tháng này
                  </button>
                  <button class="filter-btn" onclick="setTimeRange('year')">
                      Năm nay
                  </button>
              </div>
              <div class="col-md-4">
                  <div class="date-range-picker">
                      <input type="date" id="startDate" value="2025-11-01" />
                      <span>đến</span>
                      <input type="date" id="endDate" value="2025-11-30" />
                  </div>
              </div>
              <div class="col-md-2">
                  <button class="btn-export  w-100" onclick="exportReport()">
                      <i class="fas fa-download"></i> Xuất báo cáo
                  </button>
              </div>
          </div>
      </div>

      <!-- Summary Box -->
      <div class="summary-box">
          <div class="summary-title">Tổng Quan Tháng 11/2025</div>
          <div class="summary-stats">
              <div class="summary-item">
                  <div class="summary-item-value">850M</div>
                  <div class="summary-item-label">Tổng Doanh Thu</div>
              </div>
              <div class="summary-item">
                  <div class="summary-item-value">248</div>
                  <div class="summary-item-label">Tổng Đặt Phòng</div>
              </div>
              <div class="summary-item">
                  <div class="summary-item-value">72.5%</div>
                  <div class="summary-item-label">Tỷ Lệ Lấp Đầy</div>
              </div>
              <div class="summary-item">
                  <div class="summary-item-value">4.8</div>
                  <div class="summary-item-label">Đánh Giá TB</div>
              </div>
          </div>
      </div>

      <!-- Charts Row 1 -->
      <div class="row">
          <div class="col-lg-8">
              <div class="chart-card">
                  <div class="chart-header">
                      <h3 class="chart-title">
                          <i class="fas fa-chart-line"></i>
                          Xu Hướng Doanh Thu & Đặt Phòng
                      </h3>
                      <div class="chart-actions">
                          <button class="chart-btn active" onclick="updateRevenueChart('month')">
                              Tháng
                          </button>
                          <button class="chart-btn" onclick="updateRevenueChart('quarter')">
                              Quý
                          </button>
                          <button class="chart-btn" onclick="updateRevenueChart('year')">
                              Năm
                          </button>
                      </div>
                  </div>
                  <div class="chart-container">
                      <canvas id="revenueChart"></canvas>
                  </div>
              </div>
          </div>

          <div class="col-lg-4">
              <div class="chart-card">
                  <div class="chart-header">
                      <h3 class="chart-title">
                          <i class="fas fa-chart-pie"></i>
                          Phân Bổ Doanh Thu
                      </h3>
                  </div>
                  <div class="chart-container small">
                      <canvas id="revenuePieChart"></canvas>
                  </div>
              </div>
          </div>
      </div>

      <!-- Charts Row 2 -->
      <div class="row">
          <div class="col-lg-6">
              <div class="chart-card">
                  <div class="chart-header">
                      <h3 class="chart-title">
                          <i class="fas fa-chart-bar"></i>
                          Doanh thu theo loại dịch vụ
                      </h3>
                  </div>
                  <div class="chart-container small">
                      <canvas id="roomTypeChart"></canvas>
                  </div>
              </div>
          </div>

          <div class="col-lg-6">
              <div class="chart-card">
                  <div class="chart-header">
                      <h3 class="chart-title">
                          <i class="fas fa-percentage"></i>
                          Tỷ Lệ Lấp Đầy Theo Tầng
                      </h3>
                  </div>
                  <div class="chart-container small">
                      <canvas id="occupancyChart"></canvas>
                  </div>
              </div>
          </div>
      </div>

      <!-- Data Tables -->
      <div class="row">
          <div class="col-lg-6">
              <div class="chart-card">
                  <div class="chart-header">
                      <h3 class="chart-title">
                          <i class="fas fa-trophy"></i>
                          Top 5 Phòng Có Doanh Thu Cao Nhất
                      </h3>
                  </div>
                  <div class="data-table">
                      <table>
                          <thead>
                              <tr>
                                  <th>Phòng</th>
                                  <th>Loại</th>
                                  <th>Số Lần Đặt</th>
                                  <th>Doanh Thu</th>
                              </tr>
                          </thead>
                          <tbody>
                              <tr>
                                  <td><strong>Phòng 302</strong></td>
                                  <td><span class="badge bg-warning">VIP</span></td>
                                  <td>28</td>
                                  <td><strong>70,000,000 VNĐ</strong></td>
                              </tr>
                              <tr>
                                  <td><strong>Phòng 301</strong></td>
                                  <td><span class="badge bg-info">Suite</span></td>
                                  <td>32</td>
                                  <td><strong>48,000,000 VNĐ</strong></td>
                              </tr>
                              <tr>
                                  <td><strong>Phòng 201</strong></td>
                                  <td><span class="badge bg-info">Suite</span></td>
                                  <td>30</td>
                                  <td><strong>45,000,000 VNĐ</strong></td>
                              </tr>
                              <tr>
                                  <td><strong>Phòng 202</strong></td>
                                  <td><span class="badge bg-success">Deluxe</span></td>
                                  <td>35</td>
                                  <td><strong>28,000,000 VNĐ</strong></td>
                              </tr>
                              <tr>
                                  <td><strong>Phòng 102</strong></td>
                                  <td><span class="badge bg-success">Deluxe</span></td>
                                  <td>33</td>
                                  <td><strong>26,400,000 VNĐ</strong></td>
                              </tr>
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>

          <div class="col-lg-6">
              <div class="chart-card">
                  <div class="chart-header">
                      <h3 class="chart-title">
                          <i class="fas fa-user-star"></i>
                          Top 5 Khách Hàng VIP
                      </h3>
                  </div>
                  <div class="data-table">
                      <table>
                          <thead>
                              <tr>
                                  <th>Khách Hàng</th>
                                  <th>Số Lần Đặt</th>
                                  <th>Tổng Chi Tiêu</th>
                                  <th>Hạng</th>
                              </tr>
                          </thead>
                          <tbody>
                              <tr>
                                  <td><strong>Nguyễn Văn A</strong></td>
                                  <td>12</td>
                                  <td>35,000,000 VNĐ</td>
                                  <td><span class="badge bg-warning">Platinum</span></td>
                              </tr>
                              <tr>
                                  <td><strong>Trần Thị B</strong></td>
                                  <td>10</td>
                                  <td>28,500,000 VNĐ</td>
                                  <td><span class="badge bg-warning">Platinum</span></td>
                              </tr>
                              <tr>
                                  <td><strong>Lê Văn C</strong></td>
                                  <td>8</td>
                                  <td>22,000,000 VNĐ</td>
                                  <td><span class="badge bg-secondary">Gold</span></td>
                              </tr>
                              <tr>
                                  <td><strong>Phạm Thị D</strong></td>
                                  <td>7</td>
                                  <td>18,500,000 VNĐ</td>
                                  <td><span class="badge bg-secondary">Gold</span></td>
                              </tr>
                              <tr>
                                  <td><strong>Hoàng Văn E</strong></td>
                                  <td>6</td>
                                  <td>15,000,000 VNĐ</td>
                                  <td><span class="badge bg-info">Silver</span></td>
                              </tr>
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
      </div>