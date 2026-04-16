<?php
// HAPUS FILE INI SETELAH SELESAI SETUP
echo "<pre>";
$passwords = ['superadmin123','spv123','kasir123','training123','cooker123','123456'];
foreach($passwords as $p) {
    echo "$p => " . password_hash($p, PASSWORD_BCRYPT) . "\n";
}
echo "</pre>";
