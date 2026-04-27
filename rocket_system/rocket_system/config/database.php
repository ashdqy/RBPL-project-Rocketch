<?php
$host     = 'sql309.infinityfree.com';
$dbname   = 'if0_41770489_rocket2';
$user     = 'if0_41770489';
$password = 'rocketchsystem';
$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("<div style='font-family:sans-serif;padding:20px;color:red;'>
        <h3>Koneksi Database Gagal</h3>
        <p>" . mysqli_connect_error() . "</p>
        <p>Pastikan MySQL aktif dan database <strong>rocketch</strong> sudah diimport.</p>
    </div>");
}

mysqli_set_charset($conn, 'utf8mb4');
