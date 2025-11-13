<?php
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'hotel_management';

$error = '';
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_error) {
    error_log('DB connect error: ' . $mysqli->connect_error);
    $error = 'Có lỗi máy chủ.Vui lòng thử lại sau';
    die('Database connection failed: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function formatCurrency($amount)
{
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

function formatDate($date)
{
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($datetime)
{
    if (!$datetime) return '-';
    return date('d/m/Y H:i', strtotime($datetime));
}

function getPagination($total, $perPage, $currentPage, $baseUrl)
{
    $totalPages = ceil($total / $perPage);
    $pagination = '';

    if ($totalPages <= 1) return '';

    $pagination .= '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

    // Previous button
    if ($currentPage > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage - 1) . '">Trước</a></li>';
    } else {
        $pagination .= '<li class="page-item disabled"><span class="page-link">Trước</span></li>';
    }

    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    if ($start > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=1">1</a></li>';
        if ($start > 2) {
            $pagination .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $pagination .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $i . '">' . $i . '</a></li>';
        }
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $pagination .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }

    // Next button
    if ($currentPage < $totalPages) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage + 1) . '">Sau</a></li>';
    } else {
        $pagination .= '<li class="page-item disabled"><span class="page-link">Sau</span></li>';
    }

    $pagination .= '</ul></nav>';
    return $pagination;
}
function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
