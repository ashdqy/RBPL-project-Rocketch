<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_user'])) {
    $depth = substr_count($_SERVER['PHP_SELF'], '/') - 2;
    $back  = str_repeat('../', max(0, $depth));
    header("Location: {$back}login.php");
    exit;
}
