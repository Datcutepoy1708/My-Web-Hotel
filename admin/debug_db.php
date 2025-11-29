<?php
/**
 * Debug script để kiểm tra database connection và data
 * Truy cập: /My-Web-Hotel/admin/debug_db.php
 */

// Cấu hình database
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'hotel_management'; // Đổi tên database nếu cần

echo "<h1>Database Debug Tool</h1>";
echo "<h2>Database: $dbName</h2>";

// Test connection
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_error) {
    die("<p style='color:red'>Connection failed: " . $mysqli->connect_error . "</p>");
}
echo "<p style='color:green'>✓ Database connection successful!</p>";

$mysqli->set_charset('utf8mb4');

// Kiểm tra các bảng
$tables = ['customer', 'room', 'room_type', 'service', 'booking', 'invoice', 'review', 'blog'];
echo "<h3>Kiểm tra các bảng:</h3>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Bảng</th><th>Tồn tại</th><th>Số records</th><th>Số records (không deleted)</th></tr>";

foreach ($tables as $table) {
    $check = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($check && $check->num_rows > 0) {
        // Đếm tổng số records
        $totalResult = $mysqli->query("SELECT COUNT(*) as total FROM $table");
        $total = $totalResult ? $totalResult->fetch_assoc()['total'] : 0;
        
        // Đếm records không bị deleted
        $activeResult = $mysqli->query("SELECT COUNT(*) as total FROM $table WHERE deleted IS NULL");
        $active = $activeResult ? $activeResult->fetch_assoc()['total'] : 0;
        
        echo "<tr>";
        echo "<td><strong>$table</strong></td>";
        echo "<td style='color:green'>✓ Có</td>";
        echo "<td>$total</td>";
        echo "<td>$active</td>";
        echo "</tr>";
    } else {
        echo "<tr>";
        echo "<td><strong>$table</strong></td>";
        echo "<td style='color:red'>✗ Không tồn tại</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "</tr>";
    }
}
echo "</table>";

// Hiển thị sample data từ customer
echo "<h3>Sample data từ bảng customer (không bị deleted):</h3>";
$result = $mysqli->query("SELECT customer_id, full_name, email, username, account_status, deleted FROM customer WHERE deleted IS NULL LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Họ tên</th><th>Email</th><th>Username</th><th>Status</th><th>Deleted</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['customer_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . $row['account_status'] . "</td>";
        echo "<td>" . ($row['deleted'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:orange'>⚠ Không có dữ liệu trong bảng customer (deleted IS NULL)</p>";
}

// Test query giống như trong customers-manager.php
echo "<h3>Test query giống customers-manager.php:</h3>";
$testQuery = "SELECT c.* FROM customer c WHERE c.deleted IS NULL ORDER BY c.created_at DESC LIMIT 5";
$testResult = $mysqli->query($testQuery);
if ($testResult) {
    echo "<p style='color:green'>✓ Query thành công, số kết quả: " . $testResult->num_rows . "</p>";
    if ($testResult->num_rows > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Họ tên</th><th>Email</th></tr>";
        while ($row = $testResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['customer_id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p style='color:red'>✗ Query failed: " . $mysqli->error . "</p>";
}

// Kiểm tra charset
echo "<h3>Database charset:</h3>";
$charset = $mysqli->get_charset();
echo "<p>Character set: " . $charset->charset . "</p>";

$mysqli->close();
?>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}
th {
    background: #deb666;
    color: #000;
    padding: 10px;
}
td {
    padding: 8px;
}
</style>

