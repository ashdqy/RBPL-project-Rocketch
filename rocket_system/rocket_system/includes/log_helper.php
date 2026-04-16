<?php
/**
 * Catat aktivitas ke system_log
 * Panggil: log_activity($conn, $_SESSION['id_user'], 'PROMO', 'CREATE', 'Membuat promo Diskon 10%');
 */
function log_activity($conn, $id_user, $modul, $aksi, $deskripsi) {
    $ip  = mysqli_real_escape_string($conn, $_SERVER['REMOTE_ADDR'] ?? '');
    $mod = mysqli_real_escape_string($conn, strtoupper($modul));
    $act = mysqli_real_escape_string($conn, strtoupper($aksi));
    $des = mysqli_real_escape_string($conn, $deskripsi);
    $uid = (int)$id_user;
    mysqli_query($conn,
        "INSERT INTO system_log (id_user, modul, aksi, deskripsi, ip_address)
         VALUES ($uid, '$mod', '$act', '$des', '$ip')"
    );
}
