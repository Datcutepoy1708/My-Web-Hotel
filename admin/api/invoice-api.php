<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'get_invoice') {
    $invoice_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($invoice_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid invoice ID']);
        exit;
    }
    
    $stmt = $mysqli->prepare("
        SELECT i.*, 
               c.full_name, c.phone, c.email, c.address,
               b.booking_id, b.check_in_date, b.check_out_date,
               GROUP_CONCAT(DISTINCT bs.booking_service_id) as booking_service_ids,
               GROUP_CONCAT(DISTINCT CONCAT(s.service_name, ' (x', bs.quantity, ')') SEPARATOR ', ') as service_names
        FROM invoice i
        LEFT JOIN customer c ON i.customer_id = c.customer_id
        LEFT JOIN booking b ON i.booking_id = b.booking_id
        LEFT JOIN invoice_service isv ON i.invoice_id = isv.invoice_id
        LEFT JOIN booking_service bs ON isv.booking_service_id = bs.booking_service_id AND bs.deleted IS NULL
        LEFT JOIN service s ON bs.service_id = s.service_id
        WHERE i.invoice_id = ? AND i.deleted IS NULL
        GROUP BY i.invoice_id
    ");
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();
    $stmt->close();
    
    if ($invoice) {
        echo json_encode(['success' => true, 'invoice' => $invoice]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>

