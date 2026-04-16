<?php
require_once __DIR__ . '/../../middleware/auth.php';
if($_SESSION['role']!=='KASIR'){header("Location: ../../dashboard.php");exit;}
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Dashboard Kasir';

$myTrx   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c, IFNULL(SUM(total),0) s FROM transaksi WHERE id_kasir=".(int)$_SESSION['id_user']." AND DATE(tanggal)=CURDATE() AND status_pembayaran='PAID'"));
$lastTrx = mysqli_query($conn,"SELECT * FROM transaksi WHERE id_kasir=".(int)$_SESSION['id_user']." ORDER BY tanggal DESC LIMIT 5");
$menuList= mysqli_query($conn,"SELECT * FROM menu WHERE is_active=1 ORDER BY kategori,nama_menu LIMIT 8");

include __DIR__ . '/../../includes/layout_start.php';
?>

<div class="row g-3 mb-4">
  <div class="col-sm-6">
    <div class="stat-card">
      <div class="s-ic mb-2" style="background:rgba(16,185,129,.1);color:#10b981"><i class="fa-solid fa-receipt"></i></div>
      <div style="font-size:24px;font-weight:800"><?= number_format($myTrx['c']) ?></div>
      <div style="font-size:12px;color:#6b7280">Transaksi Saya Hari Ini</div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="stat-card">
      <div class="s-ic mb-2" style="background:rgba(230,57,70,.1);color:#e63946"><i class="fa-solid fa-sack-dollar"></i></div>
      <div style="font-size:20px;font-weight:800">Rp <?= number_format($myTrx['s'],0,',','.') ?></div>
      <div style="font-size:12px;color:#6b7280">Total Penjualan Saya</div>
    </div>
  </div>
</div>

<div class="mb-3">
  <a href="transaksi.php" class="btn" style="background:#e63946;color:#fff;border-radius:9px;font-size:14px;padding:11px 22px">
    <i class="fa-solid fa-plus me-2"></i>Buat Transaksi Baru
  </a>
</div>

<div class="card-box">
  <div class="c-head"><h5><i class="fa-solid fa-clock-rotate-left me-2" style="color:#10b981"></i>Transaksi Terakhir Saya</h5></div>
  <div class="c-body p-0">
    <table class="table table-hover mb-0">
      <thead><tr><th>No Struk</th><th>Tanggal</th><th>Total</th><th>Metode</th><th>Status</th></tr></thead>
      <tbody>
      <?php while($t=mysqli_fetch_assoc($lastTrx)):
        $sc=['PAID'=>'sb-success','PENDING'=>'sb-warning','FAILED'=>'sb-danger'][$t['status_pembayaran']]??'';
      ?>
      <tr>
        <td><code style="font-size:11px"><?= htmlspecialchars($t['no_struk']) ?></code></td>
        <td style="font-size:12px;color:#6b7280"><?= date('d/m/Y H:i',strtotime($t['tanggal'])) ?></td>
        <td style="font-weight:700">Rp <?= number_format($t['total'],0,',','.') ?></td>
        <td><?= $t['metode_pembayaran'] ?></td>
        <td><span class="rbadge <?=$sc?>"><?= $t['status_pembayaran'] ?></span></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
