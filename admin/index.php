<?php
session_start();
require 'includes/connect.php';
$page = isset($_GET['page']) ? trim($_GET['page']) : 'home';
include 'includes/header.php';
include 'includes/sidebar.php';
$allowed = [
    'home' => 'pages/home.php',
    'room-manager' => 'pages/room-manager.php',
    'services-manager' => 'pages/services-manager.php',
    'invoices-manager' => 'pages/invoices-manager.php',
    'customers-manager' => 'pages/customers-manager.php',
    'staff-manager' => 'pages/staff-manager.php',
    'reports-manager' => 'pages/reports-manager.php',
    'blogs-manager' => 'pages/blogs-manager.php',
];
$page = isset($_GET['page']) ? trim($_GET['page']) : 'home';
if (isset($allowed[$page])) {
    include $allowed[$page];
} else {
    include 'pages/404.php';
}  
include 'includes/footer.php';
?>