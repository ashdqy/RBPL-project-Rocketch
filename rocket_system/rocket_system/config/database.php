<?php
$host     = 'localhost';
$dbname   = 'rocket2';
$user     = 'root';
$password = '';

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("<div style='font-family:sans-serif;padding:20px;color:red;'>
        <h3>Koneksi Database Gagal</h3>
        <p>" . mysqli_connect_error() . "</p>
        <p>Pastikan MySQL aktif dan database <strong>rocketch</strong> sudah diimport.</p>
    </div>");
}

mysqli_set_charset($conn, 'utf8mb4');
