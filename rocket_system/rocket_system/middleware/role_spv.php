<?php
require_once __DIR__ . '/auth.php';
if (!in_array($_SESSION['role'], ['SUPER ADMIN', 'SPV'])) {
    header("Location: " . str_repeat('../', max(0, substr_count($_SERVER['PHP_SELF'], '/') - 2)) . "dashboard.php");
    exit;
}
