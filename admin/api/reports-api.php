<?php
session_start();
require_once __DIR__ . '/../includes/connect.php';

// Kiểm tra đăng nhập - không redirect, chỉ trả về JSON error
if (!isset($_SESSION['id_nhan_vien']) || !isset($_SESSION['is_staff'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$period = isset($_GET['period']) ? $_GET['period'] : 'month';

// Escape để tránh SQL injection
$start_date = $mysqli->real_escape_string($start_date);
$end_date = $mysqli->real_escape_string($end_date);

if ($action == 'revenue_trend') {
    // Xu hướng doanh thu và booking theo thời gian
    $labels = [];
    $revenue_data = [];
    $booking_data = [];
    
    if ($period == 'month') {
        // Theo tháng trong năm hiện tại
        $year = date('Y');
        for ($month = 1; $month <= 12; $month++) {
            $month_start = "$year-$month-01";
            $month_end = date('Y-m-t', strtotime($month_start));
            
            $labels[] = "T$month";
            
            // Doanh thu
            $rev_query = "
                SELECT COALESCE(SUM(total_amount), 0) as revenue
                FROM invoice
                WHERE deleted IS NULL
                AND DATE(created_at) BETWEEN '$month_start' AND '$month_end'
                AND status IN ('Paid', 'Unpaid')
            ";
            $rev_result = $mysqli->query($rev_query);
            $rev_row = $rev_result->fetch_assoc();
            $revenue_data[] = floatval($rev_row['revenue'] ?? 0) / 1000000; // Triệu VNĐ
            
            // Số booking (cả phòng và dịch vụ)
            $book_query = "
                SELECT 
                    COUNT(DISTINCT CASE WHEN booking_id IS NOT NULL THEN booking_id END) as room_bookings,
                    COUNT(DISTINCT isv.booking_service_id) as service_bookings
                FROM invoice i
                LEFT JOIN invoice_service isv ON i.invoice_id = isv.invoice_id
                WHERE i.deleted IS NULL
                AND DATE(i.created_at) BETWEEN '$month_start' AND '$month_end'
                AND i.status IN ('Paid', 'Unpaid')
            ";
            $book_result = $mysqli->query($book_query);
            $book_row = $book_result->fetch_assoc();
            $booking_data[] = intval($book_row['room_bookings'] ?? 0) + intval($book_row['service_bookings'] ?? 0);
        }
    } elseif ($period == 'quarter') {
        // Theo quý
        $year = date('Y');
        for ($q = 1; $q <= 4; $q++) {
            $quarter_start = "$year-" . (($q-1)*3 + 1) . "-01";
            $quarter_end = date('Y-m-t', strtotime("$year-" . ($q*3) . "-01"));
            
            $labels[] = "Q$q";
            
            $rev_query = "
                SELECT COALESCE(SUM(total_amount), 0) as revenue
                FROM invoice
                WHERE deleted IS NULL
                AND DATE(created_at) BETWEEN '$quarter_start' AND '$quarter_end'
                AND status IN ('Paid', 'Unpaid')
            ";
            $rev_result = $mysqli->query($rev_query);
            $rev_row = $rev_result->fetch_assoc();
            $revenue_data[] = floatval($rev_row['revenue'] ?? 0) / 1000000;
            
            $book_query = "
                SELECT 
                    COUNT(DISTINCT CASE WHEN booking_id IS NOT NULL THEN booking_id END) as room_bookings,
                    COUNT(DISTINCT isv.booking_service_id) as service_bookings
                FROM invoice i
                LEFT JOIN invoice_service isv ON i.invoice_id = isv.invoice_id
                WHERE i.deleted IS NULL
                AND DATE(i.created_at) BETWEEN '$quarter_start' AND '$quarter_end'
                AND i.status IN ('Paid', 'Unpaid')
            ";
            $book_result = $mysqli->query($book_query);
            $book_row = $book_result->fetch_assoc();
            $booking_data[] = intval($book_row['room_bookings'] ?? 0) + intval($book_row['service_bookings'] ?? 0);
        }
    } elseif ($period == 'year') {
        // Theo năm (5 năm gần nhất)
        $current_year = date('Y');
        for ($y = $current_year - 4; $y <= $current_year; $y++) {
            $year_start = "$y-01-01";
            $year_end = "$y-12-31";
            
            $labels[] = "$y";
            
            $rev_query = "
                SELECT COALESCE(SUM(total_amount), 0) as revenue
                FROM invoice
                WHERE deleted IS NULL
                AND DATE(created_at) BETWEEN '$year_start' AND '$year_end'
                AND status IN ('Paid', 'Unpaid')
            ";
            $rev_result = $mysqli->query($rev_query);
            $rev_row = $rev_result->fetch_assoc();
            $revenue_data[] = floatval($rev_row['revenue'] ?? 0) / 1000000;
            
            $book_query = "
                SELECT 
                    COUNT(DISTINCT CASE WHEN booking_id IS NOT NULL THEN booking_id END) as room_bookings,
                    COUNT(DISTINCT isv.booking_service_id) as service_bookings
                FROM invoice i
                LEFT JOIN invoice_service isv ON i.invoice_id = isv.invoice_id
                WHERE i.deleted IS NULL
                AND DATE(i.created_at) BETWEEN '$year_start' AND '$year_end'
                AND i.status IN ('Paid', 'Unpaid')
            ";
            $book_result = $mysqli->query($book_query);
            $book_row = $book_result->fetch_assoc();
            $booking_data[] = intval($book_row['room_bookings'] ?? 0) + intval($book_row['service_bookings'] ?? 0);
        }
    }
    
    echo json_encode([
        'success' => true,
        'labels' => $labels,
        'revenue' => $revenue_data,
        'bookings' => $booking_data
    ]);
    exit;
}

if ($action == 'summary') {
    // Dữ liệu tổng quan
    $summary_query = "
        SELECT 
            COALESCE(SUM(i.total_amount), 0) as total_revenue,
            COALESCE(SUM(i.room_charge), 0) as room_revenue,
            COALESCE(SUM(i.service_charge), 0) as service_revenue,
            COUNT(DISTINCT i.invoice_id) as total_invoices,
            COUNT(DISTINCT CASE WHEN i.booking_id IS NOT NULL THEN i.booking_id END) as total_room_bookings,
            COUNT(DISTINCT CASE WHEN i.booking_id IS NULL THEN i.invoice_id END) as total_service_only_invoices,
            COUNT(DISTINCT isv.booking_service_id) as total_service_bookings
        FROM invoice i
        LEFT JOIN booking b ON i.booking_id = b.booking_id
        LEFT JOIN invoice_service isv ON i.invoice_id = isv.invoice_id
        WHERE i.deleted IS NULL 
        AND DATE(i.created_at) BETWEEN '$start_date' AND '$end_date'
        AND i.status IN ('Paid', 'Unpaid', 'Refunded')
    ";
    $summary_result = $mysqli->query($summary_query);
    $summary = $summary_result ? $summary_result->fetch_assoc() : [];
    $summary['total_bookings'] = (intval($summary['total_room_bookings'] ?? 0)) + (intval($summary['total_service_bookings'] ?? 0));
    
    // Tính tỷ lệ lấp đầy
    $occupancy_query = "
        SELECT 
            SUM(DATEDIFF(
                LEAST(b.check_out_date, '$end_date'),
                GREATEST(b.check_in_date, '$start_date')
            ) + 1) as total_nights_booked
        FROM booking b
        WHERE b.deleted IS NULL 
        AND b.status IN ('Confirmed', 'Completed')
        AND b.check_in_date <= '$end_date' 
        AND b.check_out_date >= '$start_date'
    ";
    $occupancy_result = $mysqli->query($occupancy_query);
    $nights = $occupancy_result ? $occupancy_result->fetch_assoc() : ['total_nights_booked' => 0];
    
    $total_rooms = $mysqli->query("SELECT COUNT(*) as total FROM room WHERE deleted IS NULL")->fetch_assoc()['total'] ?? 0;
    $days_in_period = (strtotime($end_date) - strtotime($start_date)) / 86400 + 1;
    $total_available_nights = $total_rooms * $days_in_period;
    $occupancy_rate = $total_available_nights > 0 ? ($nights['total_nights_booked'] / $total_available_nights) * 100 : 0;
    
    // Đánh giá trung bình
    $rating_query = "
        SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews
        FROM review 
        WHERE deleted IS NULL 
        AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
        AND status = 'Approved'
    ";
    $rating_result = $mysqli->query($rating_query);
    $rating = $rating_result ? $rating_result->fetch_assoc() : ['avg_rating' => 0, 'total_reviews' => 0];
    
    echo json_encode([
        'success' => true,
        'summary' => [
            'total_revenue' => floatval($summary['total_revenue'] ?? 0),
            'room_revenue' => floatval($summary['room_revenue'] ?? 0),
            'service_revenue' => floatval($summary['service_revenue'] ?? 0),
            'total_invoices' => intval($summary['total_invoices'] ?? 0),
            'total_bookings' => intval($summary['total_bookings'] ?? 0),
            'occupancy_rate' => round($occupancy_rate, 1),
            'avg_rating' => round(floatval($rating['avg_rating'] ?? 0), 1),
            'total_reviews' => intval($rating['total_reviews'] ?? 0)
        ],
        'date_range' => [
            'start_date' => $start_date,
            'end_date' => $end_date
        ]
    ]);
    exit;
}

if ($action == 'revenue_distribution') {
    // Phân bổ doanh thu: Phòng vs Dịch vụ
    $query = "
        SELECT 
            COALESCE(SUM(room_charge), 0) as room_revenue,
            COALESCE(SUM(service_charge), 0) as service_revenue
        FROM invoice
        WHERE deleted IS NULL
        AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
        AND status IN ('Paid', 'Unpaid')
    ";
    $result = $mysqli->query($query);
    $row = $result->fetch_assoc();
    
    $room_rev = floatval($row['room_revenue'] ?? 0);
    $service_rev = floatval($row['service_revenue'] ?? 0);
    
    echo json_encode([
        'success' => true,
        'room' => $room_rev,
        'service' => $service_rev
    ]);
    exit;
}

if ($action == 'service_revenue') {
    // Doanh thu theo loại dịch vụ
    $query = "
        SELECT 
            s.service_name,
            s.service_type,
            COALESCE(SUM(bs.amount * bs.unit_price), 0) as revenue
        FROM service s
        LEFT JOIN booking_service bs ON s.service_id = bs.service_id AND bs.deleted IS NULL AND bs.status = 'confirmed'
        LEFT JOIN invoice_service isv ON bs.booking_service_id = isv.booking_service_id
        LEFT JOIN invoice i ON isv.invoice_id = i.invoice_id AND i.deleted IS NULL
        WHERE s.deleted IS NULL AND s.status = 'Active'
        AND (i.created_at IS NULL OR DATE(i.created_at) BETWEEN '$start_date' AND '$end_date')
        AND (i.status IS NULL OR i.status IN ('Paid', 'Unpaid'))
        GROUP BY s.service_id, s.service_name, s.service_type
        ORDER BY revenue DESC
    ";
    $result = $mysqli->query($query);
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = [
            'name' => $row['service_name'],
            'type' => $row['service_type'],
            'revenue' => floatval($row['revenue'] ?? 0)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'services' => $services
    ]);
    exit;
}

if ($action == 'occupancy_by_floor') {
    // Tỷ lệ lấp đầy theo tầng
    $query = "
        SELECT 
            r.floor,
            COUNT(DISTINCT r.room_id) as total_rooms,
            SUM(
                CASE 
                    WHEN b.booking_id IS NOT NULL 
                    AND b.status IN ('Confirmed', 'Completed')
                    AND b.check_in_date <= '$end_date'
                    AND b.check_out_date >= '$start_date'
                    THEN DATEDIFF(
                        LEAST(b.check_out_date, '$end_date'),
                        GREATEST(b.check_in_date, '$start_date')
                    ) + 1
                    ELSE 0
                END
            ) as booked_nights
        FROM room r
        LEFT JOIN booking b ON r.room_id = b.room_id AND b.deleted IS NULL
        WHERE r.deleted IS NULL
        GROUP BY r.floor
        ORDER BY r.floor
    ";
    $result = $mysqli->query($query);
    $floors = [];
    
    $days_in_period = (strtotime($end_date) - strtotime($start_date)) / 86400 + 1;
    
    while ($row = $result->fetch_assoc()) {
        $total_rooms = intval($row['total_rooms'] ?? 0);
        $booked_nights = intval($row['booked_nights'] ?? 0);
        $total_available_nights = $total_rooms * $days_in_period;
        $occupancy_rate = $total_available_nights > 0 ? ($booked_nights / $total_available_nights) * 100 : 0;
        
        $floors[] = [
            'floor' => intval($row['floor'] ?? 0),
            'occupancy' => round($occupancy_rate, 1)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'floors' => $floors
    ]);
    exit;
}

if ($action == 'check_export_permission') {
    // Kiểm tra quyền xuất báo cáo
    $hasPermission = false;
    if (function_exists('checkPermission')) {
        $hasPermission = checkPermission('report.export');
    }
    
    echo json_encode([
        'success' => true,
        'hasPermission' => $hasPermission
    ]);
    exit;
}

if ($action == 'export') {
    // Kiểm tra quyền xuất báo cáo
    $hasPermission = false;
    if (function_exists('checkPermission')) {
        $hasPermission = checkPermission('report.export');
    }
    
    if (!$hasPermission) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xuất báo cáo']);
        exit;
    }
    
    // TODO: Implement export functionality (Excel/PDF)
    // Hiện tại chỉ trả về thông báo
    echo json_encode([
        'success' => false,
        'message' => 'Chức năng xuất báo cáo đang được phát triển'
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
