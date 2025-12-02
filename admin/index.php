<?php
session_start();
require 'includes/connect.php';
require 'includes/auth.php'; // Kiểm tra đăng nhập

// Try to use MVC Router
$useMVC = true; // Set to false to use old pages structure

if ($useMVC && file_exists(__DIR__ . '/core/Router.php')) {
    require_once __DIR__ . '/core/Router.php';
    $router = new Router($mysqli);
    
    // Define $page for footer.php compatibility
    $page = isset($_GET['page']) ? trim($_GET['page']) : 'home';
    
    include 'includes/header.php';
    include 'includes/sidebar.php';
    $router->dispatch();
    include 'includes/footer.php';
} else {
    // Fallback to old structure
    include 'includes/header.php';
    include 'includes/sidebar.php';
    
    $allowed = [
        'home' => 'pages/home.php',
        'room-manager' => 'pages/room-manager.php',
        'services-manager' => 'pages/services-manager.php',
        'invoices-manager' => 'pages/invoices-manager.php',
        'booking-manager' => 'pages/booking-manager.php',
        'customers-manager' => 'pages/customers-manager.php',
        'staff-manager' => 'pages/staff-manager.php',
        'task-manager' => 'pages/task-manager.php',
        'permission-manager' => 'pages/permission-manager.php',
        'reports-manager' => 'pages/reports-manager.php',
        'blogs-manager' => 'pages/blogs-manager.php',
        'profile' => 'pages/profile.php',
        'logout'=>'pages/logout.php',
        'my-tasks' => 'pages/my-tasks.php',
    ];
    
    $pageName = isset($_GET['page']) ? trim($_GET['page']) : 'home';
    $page = $pageName; // For footer.php compatibility
    
    if (isset($allowed[$pageName])) {
        if (function_exists('canAccessSection') && !canAccessSection($pageName)) {
            include 'pages/403.php';
        } else {
            include $allowed[$pageName];
        }
    } else {
        include 'pages/404.php';
    }
    
    include 'includes/footer.php';
}
?>
