<?php
/**
 * LuxBot Chatbot API
 * OceanPearl Hotel – hotel_management
 * 
 * Actions:
 *  - chat       : Xử lý tin nhắn từ khách
 *  - get_settings: Lấy cấu hình bot
 *  - add_keyword : Thêm từ khóa mới (Admin)
 *  - update_keyword: Cập nhật từ khóa (Admin)
 *  - delete_keyword: Xóa từ khóa (Admin)
 *  - get_keywords : Lấy danh sách từ khóa (Admin)
 *  - get_analytics: Lấy thống kê (Admin)
 *  - get_history  : Lấy lịch sử chat (Admin)
 */

header('Content-Type: application/json; charset=utf-8');

// Cho phép CORS từ cùng domain
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (strpos($origin, 'localhost') !== false || strpos($origin, '127.0.0.1') !== false) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once __DIR__ . '/../includes/connect.php';

// Helper: JSON response
function jsonResponse($success, $data = [], $message = '') {
    $response = ['success' => $success, 'message' => $message];
    echo json_encode(array_merge($response, $data), JSON_UNESCAPED_UNICODE);
    exit;
}

// Helper: Lấy setting từ DB
function getSetting($mysqli, $key, $default = '') {
    $stmt = $mysqli->prepare("SELECT setting_value FROM bot_settings WHERE setting_key = ?");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return $default;
}

// Helper: Kiểm tra bảng tồn tại
function tableExists($mysqli, $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    return $result && $result->num_rows > 0;
}

// Kiểm tra bảng chatbot đã được tạo chưa
if (!tableExists($mysqli, 'bot_keywords') || !tableExists($mysqli, 'chat_history') || !tableExists($mysqli, 'bot_settings')) {
    jsonResponse(false, [], 'Hệ thống chatbot chưa được cài đặt. Vui lòng chạy chatbot_setup.sql trước.');
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'chat';

switch ($action) {
    // ──────────────────────────────────────────────────────────────
    // PUBLIC: Xử lý tin nhắn chat từ khách hàng
    // ──────────────────────────────────────────────────────────────
    case 'chat':
        $userMessage = trim($_POST['message'] ?? '');
        $sessionId   = $_POST['session_id'] ?? session_id();

        if (empty($userMessage)) {
            jsonResponse(false, [], 'Tin nhắn không được để trống');
        }

        // Kiểm tra bot có được bật không
        $isActive = getSetting($mysqli, 'is_active', '1');
        if ($isActive !== '1') {
            jsonResponse(true, [
                'bot_reply' => 'Hệ thống chat tạm thời không khả dụng. Vui lòng gọi hotline: +84.244.243.434',
                'keyword_match' => null,
                'is_unknown' => true
            ]);
        }

        // Tìm từ khóa phù hợp (tìm theo LIKE, không phân biệt hoa thường)
        $msgLower = mb_strtolower($userMessage, 'UTF-8');

        // Lấy tất cả từ khóa đang active
        $keywordsResult = $mysqli->query(
            "SELECT id, keyword, response FROM bot_keywords WHERE is_active = 1 ORDER BY LENGTH(keyword) DESC"
        );

        $matchedKeyword = null;
        $botReply       = null;
        $isUnknown      = false;

        if ($keywordsResult && $keywordsResult->num_rows > 0) {
            while ($kw = $keywordsResult->fetch_assoc()) {
                $kwLower = mb_strtolower($kw['keyword'], 'UTF-8');
                // Kiểm tra từ khóa có trong tin nhắn không
                if (strpos($msgLower, $kwLower) !== false) {
                    $matchedKeyword = $kw;
                    $botReply = $kw['response'];
                    break;
                }
            }
        }

        // Nếu không tìm được từ khóa
        if (!$botReply) {
            $isUnknown = true;
            $botReply = getSetting($mysqli, 'unknown_message',
                'Dạ, em chưa hiểu ý của mình ạ 😊 Anh/Chị có thể hỏi về Giá phòng, Check-in/out, Địa chỉ hoặc Dịch vụ của chúng em không ạ?'
            );
        }

        // Tăng match_count nếu có từ khóa khớp
        if ($matchedKeyword) {
            $updateStmt = $mysqli->prepare("UPDATE bot_keywords SET match_count = match_count + 1 WHERE id = ?");
            $updateStmt->bind_param('i', $matchedKeyword['id']);
            $updateStmt->execute();
        }

        // Lưu lịch sử chat
        $keywordId = $matchedKeyword ? $matchedKeyword['id'] : null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

        $histStmt = $mysqli->prepare(
            "INSERT INTO chat_history (session_id, user_message, bot_response, keyword_id, is_unknown, ip_address)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $histStmt->bind_param('sssiss', $sessionId, $userMessage, $botReply, $keywordId, $isUnknown, $ipAddress);
        $histStmt->execute();

        jsonResponse(true, [
            'bot_reply'     => $botReply,
            'keyword_match' => $matchedKeyword ? $matchedKeyword['keyword'] : null,
            'is_unknown'    => $isUnknown
        ]);
        break;

    // ──────────────────────────────────────────────────────────────
    // PUBLIC: Lấy cấu hình bot (welcome message, tên bot...)
    // ──────────────────────────────────────────────────────────────
    case 'get_settings':
        $settingsResult = $mysqli->query("SELECT setting_key, setting_value FROM bot_settings");
        $settings = [];
        while ($row = $settingsResult->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        jsonResponse(true, ['settings' => $settings]);
        break;

    // ──────────────────────────────────────────────────────────────
    // ADMIN: Lấy danh sách từ khóa
    // ──────────────────────────────────────────────────────────────
    case 'get_keywords':
        if (!isset($_SESSION['id_nhan_vien'])) {
            jsonResponse(false, [], 'Không có quyền truy cập');
        }
        $category = $_GET['category'] ?? $_POST['category'] ?? '';
        $search   = $_GET['search'] ?? $_POST['search'] ?? '';

        $sql = "SELECT id, keyword, response, category, is_active, match_count, created_at, updated_at
                FROM bot_keywords WHERE 1=1";
        $params = [];
        $types  = '';

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
            $types .= 's';
        }
        if ($search) {
            $sql .= " AND (keyword LIKE ? OR response LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= 'ss';
        }
        $sql .= " ORDER BY match_count DESC, id ASC";

        if ($params) {
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $mysqli->query($sql);
        }

        $keywords = [];
        while ($row = $result->fetch_assoc()) {
            $keywords[] = $row;
        }
        jsonResponse(true, ['keywords' => $keywords]);
        break;

    // ──────────────────────────────────────────────────────────────
    // ADMIN: Thêm từ khóa mới
    // ──────────────────────────────────────────────────────────────
    case 'add_keyword':
        if (!isset($_SESSION['id_nhan_vien'])) {
            jsonResponse(false, [], 'Không có quyền truy cập');
        }
        $keyword  = trim($_POST['keyword'] ?? '');
        $response = trim($_POST['response'] ?? '');
        $category = trim($_POST['category'] ?? 'General');

        if (empty($keyword) || empty($response)) {
            jsonResponse(false, [], 'Từ khóa và câu trả lời không được để trống');
        }

        // Kiểm tra trùng từ khóa
        $checkStmt = $mysqli->prepare("SELECT id FROM bot_keywords WHERE keyword = ?");
        $checkStmt->bind_param('s', $keyword);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            jsonResponse(false, [], 'Từ khóa này đã tồn tại');
        }

        $insertStmt = $mysqli->prepare(
            "INSERT INTO bot_keywords (keyword, response, category) VALUES (?, ?, ?)"
        );
        $insertStmt->bind_param('sss', $keyword, $response, $category);
        $insertStmt->execute();

        jsonResponse(true, ['id' => $mysqli->insert_id], 'Thêm từ khóa thành công');
        break;

    // ──────────────────────────────────────────────────────────────
    // ADMIN: Cập nhật từ khóa
    // ──────────────────────────────────────────────────────────────
    case 'update_keyword':
        if (!isset($_SESSION['id_nhan_vien'])) {
            jsonResponse(false, [], 'Không có quyền truy cập');
        }
        $id       = (int)($_POST['id'] ?? 0);
        $keyword  = trim($_POST['keyword'] ?? '');
        $response = trim($_POST['response'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $isActive = (int)($_POST['is_active'] ?? 1);

        if (!$id || empty($keyword) || empty($response)) {
            jsonResponse(false, [], 'Dữ liệu không hợp lệ');
        }

        $updateStmt = $mysqli->prepare(
            "UPDATE bot_keywords SET keyword = ?, response = ?, category = ?, is_active = ? WHERE id = ?"
        );
        $updateStmt->bind_param('sssii', $keyword, $response, $category, $isActive, $id);
        $updateStmt->execute();

        jsonResponse(true, [], 'Cập nhật từ khóa thành công');
        break;

    // ──────────────────────────────────────────────────────────────
    // ADMIN: Xóa từ khóa
    // ──────────────────────────────────────────────────────────────
    case 'delete_keyword':
        if (!isset($_SESSION['id_nhan_vien'])) {
            jsonResponse(false, [], 'Không có quyền truy cập');
        }
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonResponse(false, [], 'ID không hợp lệ');
        }

        // Set keyword_id = NULL trong chat_history trước khi xóa
        $nullStmt = $mysqli->prepare("UPDATE chat_history SET keyword_id = NULL WHERE keyword_id = ?");
        $nullStmt->bind_param('i', $id);
        $nullStmt->execute();

        $delStmt = $mysqli->prepare("DELETE FROM bot_keywords WHERE id = ?");
        $delStmt->bind_param('i', $id);
        $delStmt->execute();

        jsonResponse(true, [], 'Xóa từ khóa thành công');
        break;

    // ──────────────────────────────────────────────────────────────
    // ADMIN: Cập nhật settings
    // ──────────────────────────────────────────────────────────────
    case 'update_setting':
        if (!isset($_SESSION['id_nhan_vien'])) {
            jsonResponse(false, [], 'Không có quyền truy cập');
        }
        $key   = trim($_POST['key'] ?? '');
        $value = trim($_POST['value'] ?? '');

        if (empty($key)) {
            jsonResponse(false, [], 'Key không hợp lệ');
        }

        $upsertStmt = $mysqli->prepare(
            "INSERT INTO bot_settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        $upsertStmt->bind_param('ss', $key, $value);
        $upsertStmt->execute();

        jsonResponse(true, [], 'Cập nhật cài đặt thành công');
        break;

    // ──────────────────────────────────────────────────────────────
    // ADMIN: Thống kê
    // ──────────────────────────────────────────────────────────────
    case 'get_analytics':
        if (!isset($_SESSION['id_nhan_vien'])) {
            jsonResponse(false, [], 'Không có quyền truy cập');
        }

        // Tổng số hội thoại (theo session)
        $totalSessions = $mysqli->query(
            "SELECT COUNT(DISTINCT session_id) as total FROM chat_history"
        )->fetch_assoc()['total'];

        // Tổng tin nhắn
        $totalMessages = $mysqli->query(
            "SELECT COUNT(*) as total FROM chat_history"
        )->fetch_assoc()['total'];

        // Tin nhắn không trả lời được
        $unknownCount = $mysqli->query(
            "SELECT COUNT(*) as total FROM chat_history WHERE is_unknown = 1"
        )->fetch_assoc()['total'];

        // Tỉ lệ trả lời được
        $answerRate = $totalMessages > 0 ? round((($totalMessages - $unknownCount) / $totalMessages) * 100, 1) : 0;

        // Top 10 từ khóa phổ biến nhất
        $topKeywords = [];
        $topResult = $mysqli->query(
            "SELECT keyword, match_count, category FROM bot_keywords
             WHERE match_count > 0 ORDER BY match_count DESC LIMIT 10"
        );
        while ($row = $topResult->fetch_assoc()) {
            $topKeywords[] = $row;
        }

        // Thống kê theo ngày (7 ngày gần nhất)
        $dailyStats = [];
        $dailyResult = $mysqli->query(
            "SELECT DATE(created_at) as date,
                    COUNT(*) as total,
                    SUM(is_unknown) as unknown,
                    COUNT(*) - SUM(is_unknown) as answered
             FROM chat_history
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC"
        );
        while ($row = $dailyResult->fetch_assoc()) {
            $dailyStats[] = $row;
        }

        // Thống kê theo category
        $categoryStats = [];
        $catResult = $mysqli->query(
            "SELECT bk.category, SUM(bk.match_count) as total_matches
             FROM bot_keywords bk
             WHERE bk.is_active = 1
             GROUP BY bk.category
             ORDER BY total_matches DESC"
        );
        while ($row = $catResult->fetch_assoc()) {
            $categoryStats[] = $row;
        }

        // Tổng số từ khóa
        $totalKeywords = $mysqli->query(
            "SELECT COUNT(*) as total FROM bot_keywords WHERE is_active = 1"
        )->fetch_assoc()['total'];

        jsonResponse(true, [
            'total_sessions'  => (int)$totalSessions,
            'total_messages'  => (int)$totalMessages,
            'unknown_count'   => (int)$unknownCount,
            'answer_rate'     => $answerRate,
            'total_keywords'  => (int)$totalKeywords,
            'top_keywords'    => $topKeywords,
            'daily_stats'     => $dailyStats,
            'category_stats'  => $categoryStats
        ]);
        break;

    // ──────────────────────────────────────────────────────────────
    // ADMIN: Lịch sử chat
    // ──────────────────────────────────────────────────────────────
    case 'get_history':
        if (!isset($_SESSION['id_nhan_vien'])) {
            jsonResponse(false, [], 'Không có quyền truy cập');
        }
        $onlyUnknown = (int)($_GET['only_unknown'] ?? $_POST['only_unknown'] ?? 0);
        $limit       = min((int)($_GET['limit'] ?? 50), 200);
        $offset      = (int)($_GET['offset'] ?? 0);

        $sql = "SELECT ch.id, ch.session_id, ch.user_message, ch.bot_response,
                       ch.is_unknown, ch.ip_address, ch.created_at,
                       bk.keyword as matched_keyword
                FROM chat_history ch
                LEFT JOIN bot_keywords bk ON ch.keyword_id = bk.id";

        if ($onlyUnknown) {
            $sql .= " WHERE ch.is_unknown = 1";
        }
        $sql .= " ORDER BY ch.created_at DESC LIMIT ? OFFSET ?";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }

        // Tổng số record
        $countSql = "SELECT COUNT(*) as total FROM chat_history";
        if ($onlyUnknown) $countSql .= " WHERE is_unknown = 1";
        $totalCount = $mysqli->query($countSql)->fetch_assoc()['total'];

        jsonResponse(true, [
            'history'     => $history,
            'total'       => (int)$totalCount,
            'limit'       => $limit,
            'offset'      => $offset
        ]);
        break;

    // ──────────────────────────────────────────────────────────────
    // ADMIN: Xóa lịch sử chat
    // ──────────────────────────────────────────────────────────────
    case 'clear_history':
        if (!isset($_SESSION['id_nhan_vien'])) {
            jsonResponse(false, [], 'Không có quyền truy cập');
        }
        $mysqli->query("DELETE FROM chat_history");
        $mysqli->query("UPDATE bot_keywords SET match_count = 0");
        jsonResponse(true, [], 'Đã xóa toàn bộ lịch sử chat');
        break;

    default:
        jsonResponse(false, [], 'Action không hợp lệ');
}
