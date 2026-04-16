<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = $pageTitle ?? 'Rocket Chicken System';
$role = $_SESSION['role'] ?? '';
$root = '/RC/rocket_system/rocket_system/';
function nav_active($cur, $kw) { return (strpos($cur, $kw) !== false) ? 'active' : ''; }
$cur = $_SERVER['PHP_SELF'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?> — Rocket Chicken</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
:root{--sw:240px;--brand:#e63946;--sdk:#c1121f;--sbg:#111827;--shov:#1f2937}
body{background:#f0f2f5;font-family:'Segoe UI',sans-serif;min-height:100vh}
#sidebar{position:fixed;top:0;left:0;height:100vh;width:var(--sw);background:var(--sbg);display:flex;flex-direction:column;z-index:1000;overflow-y:auto}
.s-brand{padding:18px 14px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,.07)}
.s-icon{width:36px;height:36px;border-radius:9px;background:var(--brand);display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;flex-shrink:0}
.s-name{color:#fff;font-weight:700;font-size:14px;line-height:1.3}
.s-name small{font-size:10.5px;color:#6b7280;font-weight:400;display:block}
.s-section{padding:10px 0 3px 14px;font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:#4b5563;margin-top:4px}
.s-link{color:#9ca3af;padding:9px 14px;display:flex;align-items:center;gap:9px;border-radius:7px;margin:1px 7px;font-size:13px;transition:.15s;text-decoration:none}
.s-link:hover{background:var(--shov);color:#fff}
.s-link.active{background:var(--shov);color:#fff;border-left:3px solid var(--brand)}
.s-link i{width:16px;text-align:center;font-size:13px}
.s-footer{margin-top:auto;padding:14px;border-top:1px solid rgba(255,255,255,.06)}
.u-badge{display:flex;align-items:center;gap:9px;background:rgba(255,255,255,.04);border-radius:9px;padding:9px 11px}
.u-av{width:32px;height:32px;border-radius:50%;background:var(--brand);display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;font-weight:700;flex-shrink:0}
.u-info span{color:#f3f4f6;font-size:12.5px;font-weight:600;display:block}
.u-info small{color:#6b7280;font-size:10.5px}
#main{margin-left:var(--sw);min-height:100vh}
.topbar{background:#fff;padding:11px 22px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,.06);position:sticky;top:0;z-index:99}
.topbar .pg-title{font-size:16px;font-weight:700;color:#111827}
.content{padding:22px}
.stat-card{background:#fff;border-radius:13px;padding:18px;box-shadow:0 1px 4px rgba(0,0,0,.06);transition:.2s;border:1px solid rgba(0,0,0,.04)}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,.09)}
.stat-card .s-ic{width:46px;height:46px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:19px}
.card-box{background:#fff;border-radius:13px;box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.04)}
.card-box .c-head{padding:14px 18px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between}
.card-box .c-head h5{margin:0;font-size:14.5px;font-weight:700}
.card-box .c-body{padding:18px}
.table th{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;background:#fafafa;padding:10px 14px}
.table td{padding:11px 14px;font-size:13px;vertical-align:middle}
.table-hover tbody tr:hover td{background:#f9fafb}
.rbadge{font-size:10.5px;padding:3px 8px;border-radius:20px;font-weight:600;letter-spacing:.3px}
.rb-sa{background:#fef2f2;color:#dc2626}.rb-spv{background:#eff6ff;color:#2563eb}
.rb-ka{background:#f0fdf4;color:#16a34a}.rb-tr{background:#fffbeb;color:#d97706}.rb-co{background:#fdf4ff;color:#9333ea}
.sb-success{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0}
.sb-danger{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}
.sb-warning{background:#fffbeb;color:#d97706;border:1px solid #fde68a}
.sb-info{background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe}
@media(max-width:768px){#sidebar{width:56px}.s-name,.s-link span,.s-section,.u-info{display:none}#main{margin-left:56px}}
</style>
</head>
<body>
<nav id="sidebar">
  <div class="s-brand">
    <div class="s-icon"><i class="fa-solid fa-fire-flame-curved"></i></div>
    <div class="s-name">Rocket Chicken<small><?= htmlspecialchars($role) ?></small></div>
  </div>
  <div class="mt-1">

  <?php if($role==='SUPER ADMIN'): ?>
    <div class="s-section">Dashboard</div>
    <a href="<?=$root?>view/admin/dashboard.php" class="s-link <?=nav_active($cur,'admin/dashboard')?>"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
    <div class="s-section">Manajemen</div>
    <a href="<?=$root?>view/admin/users/index.php" class="s-link <?=nav_active($cur,'admin/users')?>"><i class="fa-solid fa-users"></i><span>Kelola User</span></a>
    <a href="<?=$root?>view/admin/menu/index.php" class="s-link <?=nav_active($cur,'admin/menu')?>"><i class="fa-solid fa-utensils"></i><span>Kelola Menu</span></a>
    <a href="<?=$root?>view/admin/promo.php" class="s-link <?=nav_active($cur,'admin/promo')?>"><i class="fa-solid fa-tags"></i><span>Atur Promo</span></a>
    <a href="<?=$root?>view/admin/log/index.php" class="s-link <?=nav_active($cur,'admin/log')?>"><i class="fa-solid fa-scroll"></i><span>Log Aktivitas Stok</span></a>
    <div class="s-section">Monitoring</div>
    <a href="<?=$root?>view/admin/transaksi/index.php" class="s-link <?=nav_active($cur,'admin/transaksi')?>"><i class="fa-solid fa-receipt"></i><span>Audit Transaksi</span></a>
    <a href="<?=$root?>view/admin/laporan/index.php" class="s-link <?=nav_active($cur,'admin/laporan')?>"><i class="fa-solid fa-chart-line"></i><span>Monitor Laporan</span></a>
    <div class="s-section">Sistem</div>
    <a href="<?=$root?>view/admin/system_log.php" class="s-link <?=nav_active($cur,'admin/system_log')?>"><i class="fa-solid fa-shield-halved"></i><span>Log Sistem</span></a>
    <a href="<?=$root?>view/admin/backup/index.php" class="s-link <?=nav_active($cur,'admin/backup')?>"><i class="fa-solid fa-database"></i><span>Backup Database</span></a>

  <?php elseif($role==='SPV'): ?>
    <div class="s-section">Dashboard</div>
    <a href="<?=$root?>view/spv/dashboard.php" class="s-link <?=nav_active($cur,'spv/dashboard')?>"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
    <div class="s-section">Laporan</div>
    <a href="<?=$root?>view/spv/laporan.php" class="s-link <?=nav_active($cur,'spv/laporan')?>"><i class="fa-solid fa-file-circle-plus"></i><span>Generate Laporan</span></a>
    <a href="<?=$root?>view/spv/validasi.php" class="s-link <?=nav_active($cur,'spv/validasi')?>"><i class="fa-solid fa-check-double"></i><span>Validasi Laporan</span></a>
    <a href="<?=$root?>view/spv/keuangan.php" class="s-link <?=nav_active($cur,'spv/keuangan')?>"><i class="fa-solid fa-chart-pie"></i><span>Laporan Keuangan</span></a>
    <div class="s-section">Data</div>
    <a href="<?=$root?>view/spv/transaksi.php" class="s-link <?=nav_active($cur,'spv/transaksi')?>"><i class="fa-solid fa-receipt"></i><span>Riwayat Transaksi</span></a>
    <a href="<?=$root?>view/spv/stok.php" class="s-link <?=nav_active($cur,'spv/stok')?>"><i class="fa-solid fa-boxes-stacked"></i><span>Lihat Stok</span></a>

  <?php elseif($role==='KASIR'): ?>
    <div class="s-section">Dashboard</div>
    <a href="<?=$root?>view/kasir/dashboard.php" class="s-link <?=nav_active($cur,'kasir/dashboard')?>"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
    <div class="s-section">Transaksi</div>
    <a href="<?=$root?>view/kasir/transaksi.php" class="s-link <?=nav_active($cur,'kasir/transaksi')?>"><i class="fa-solid fa-plus-circle"></i><span>Buat Transaksi</span></a>
    <a href="<?=$root?>view/kasir/riwayat.php" class="s-link <?=nav_active($cur,'kasir/riwayat')?>"><i class="fa-solid fa-clock-rotate-left"></i><span>Riwayat Transaksi</span></a>

  <?php elseif($role==='TRAINING'): ?>
    <div class="s-section">Dashboard</div>
    <a href="<?=$root?>view/training/dashboard.php" class="s-link <?=nav_active($cur,'training/dashboard')?>"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
    <div class="s-section">Stok</div>
    <a href="<?=$root?>view/training/stok.php" class="s-link <?=nav_active($cur,'training/stok')?>"><i class="fa-solid fa-arrow-down-to-bracket"></i><span>Input Barang Masuk</span></a>
    <a href="<?=$root?>view/training/return.php" class="s-link <?=nav_active($cur,'training/return')?>"><i class="fa-solid fa-rotate-left"></i><span>Input Return</span></a>

  <?php elseif($role==='COOKER'): ?>
    <div class="s-section">Dashboard</div>
    <a href="<?=$root?>view/cooker/dashboard.php" class="s-link <?=nav_active($cur,'cooker/dashboard')?>"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
    <div class="s-section">Dapur</div>
    <a href="<?=$root?>view/cooker/penggunaan.php" class="s-link <?=nav_active($cur,'cooker/penggunaan')?>"><i class="fa-solid fa-fire-burner"></i><span>Penggunaan Bahan</span></a>
    <a href="<?=$root?>view/cooker/sisa.php" class="s-link <?=nav_active($cur,'cooker/sisa')?>"><i class="fa-solid fa-scale-balanced"></i><span>Update Sisa Stok</span></a>

  <?php endif; ?>
  </div>
  <div class="s-footer">
    <div class="u-badge">
      <div class="u-av"><?= strtoupper(substr($_SESSION['nama']??'U',0,1)) ?></div>
      <div class="u-info"><span><?= htmlspecialchars($_SESSION['nama']??'') ?></span><small><?= htmlspecialchars($role) ?></small></div>
    </div>
    <a href="<?=$root?>logout.php" class="btn btn-sm w-100 mt-2"
      style="background:rgba(239,68,68,.12);color:#ef4444;border:1px solid rgba(239,68,68,.2);font-size:12px">
      <i class="fa-solid fa-right-from-bracket me-1"></i>Logout
    </a>
  </div>
</nav>
<div id="main">
  <div class="topbar">
    <div class="pg-title"><?= htmlspecialchars($pageTitle) ?></div>
    <div class="d-flex align-items-center gap-3">
      <span class="text-muted" style="font-size:12px">
        <i class="fa-regular fa-clock me-1"></i><?= date('d M Y, H:i') ?>
      </span>
    </div>
  </div>
  <div class="content">
