<div class="main-content">
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-bed"></i>
                </div>
                <div class="stat-info">
                    <h3>48</h3>
                    <p>Tổng Phòng</p>
                    <small class="text-success"><i class="fas fa-arrow-up"></i> 12% so với tháng trước</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3>32</h3>
                    <p>Phòng Đã Thuê</p>
                    <small class="text-success"><i class="fas fa-arrow-up"></i> 8% so với tháng trước</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="stat-info">
                    <h3>16</h3>
                    <p>Phòng Trống</p>
                    <small class="text-danger"><i class="fas fa-arrow-down"></i> 5% so với tháng trước</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-info">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="stat-info">
                    <h3>128M</h3>
                    <p>Doanh Thu Tháng</p>
                    <small class="text-success"><i class="fas fa-arrow-up"></i> 15% so với tháng trước</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-12">
            <div class="chart-card">
                <div class="card-header">
                    <h5>Tỷ Lệ Phòng</h5>
                </div>
                <div style="max-width: 400px; margin: 0 auto">
                    <canvas id="roomChart" style="height: 300px"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-card">
                <div class="card-title">Doanh Thu 7 Ngày Qua</div>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="quick-actions-card">
                <div class="card-header">
                    <h5>Thao Tác Nhanh</h5>
                </div>
                <div class="quick-actions">
                    <button class="quick-action-btn">
                        <i class="fas fa-plus-circle"></i>
                        <span>Thêm Phòng</span>
                    </button>
                    <button class="quick-action-btn">
                        <i class="fas fa-plus-circle"></i>
                        <span>Thêm Dịch Vụ</span>
                    </button>
                    <button class="quick-action-btn">
                        <i class="fas fa-plus-circle"></i>
                        <span>Thêm Nhân Viên</span>
                    </button>
                    <button class="quick-action-btn">
                        <i class="fas fa-chart-bar"></i>
                        <span>Xem Báo Cáo</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>