<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php"); exit;
}

$role = $_SESSION['role'];
$routes = [
    'SUPER ADMIN' => 'view/admin/dashboard.php',
    'SPV'         => 'view/spv/dashboard.php',
    'KASIR'       => 'view/kasir/dashboard.php',
    'TRAINING'    => 'view/training/dashboard.php',
    'COOKER'      => 'view/cooker/dashboard.php',
];

if (isset($routes[$role])) {
    header("Location: " . $routes[$role]); exit;
} else {
    session_destroy();
    header("Location: login.php?error=invalid_role"); exit;
}
