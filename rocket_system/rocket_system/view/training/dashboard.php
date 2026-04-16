<?php
require_once __DIR__ . '/../../middleware/auth.php';
if($_SESSION['role']!=='TRAINING'){header("Location: ../../dashboard.php");exit;}
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Dashboard Training';

$totalStok = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM stok"))['c'];
$totalMasuk= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM stok_log WHERE jenis='MASUK' AND DATE(tanggal)=CURDATE()"))['c'];
$totalReturn=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM return_barang WHERE DATE(tanggal)=CURDATE()"))['c'];
$recentLogs= mysqli_query($conn,"SELECT sl.*, s.nama_bahan FROM stok_log sl JOIN stok s ON sl.id_stok=s.id_stok WHERE sl.id_user=".(int)$_SESSION['id_user']." ORDER BY sl.tanggal DESC LIMIT 7");

include __DIR__ . '/../../includes/layout_start.php';
?>

<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="stat-card"><div class="s-ic mb-2" style="background:rgba(245,158,11,.1);color:#f59e0b"><i class="fa-solid fa-boxes-stacked"></i></div>
      <div style="font-size:24px;font-weight:800"><?=$totalStok?></div><div style="font-size:12px;color:#6b7280">Total Item Stok</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card"><div class="s-ic mb-2" style="background:rgba(16,185,129,.1);color:#10b981"><i class="fa-solid fa-arrow-down"></i></div>
      <div style="font-size:24px;font-weight:800"><?=$totalMasuk?></div><div style="font-size:12px;color:#6b7280">Barang Masuk Hari Ini</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card"><div class="s-ic mb-2" style="background:rgba(59,130,246,.1);color:#3b82f6"><i class="fa-solid fa-rotate-left"></i></div>
      <div style="font-size:24px;font-weight:800"><?=$totalReturn?></div><div style="font-size:12px;color:#6b7280">Return Hari Ini</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-sm-6">
    <a href="stok.php" class="btn w-100 py-3" style="background:#10b981;color:#fff;border-radius:9px;font-size:14px">
      <i class="fa-solid fa-arrow-down-to-bracket me-2"></i>Input Barang Masuk
    </a>
  </div>
  <div class="col-sm-6">
    <a href="return.php" class="btn w-100 py-3" style="background:#3b82f6;color:#fff;border-radius:9px;font-size:14px">
      <i class="fa-solid fa-rotate-left me-2"></i>Input Return Barang
    </a>
  </div>
</div>

<div class="card-box">
  <div class="c-head"><h5><i class="fa-solid fa-scroll me-2" style="color:#f59e0b"></i>Aktivitas Saya Terakhir</h5></div>
  <div class="c-body p-0">
    <table class="table table-hover mb-0">
      <thead><tr><th>Waktu</th><th>Bahan</th><th>Jenis</th><th>Perubahan</th><th>Keterangan</th></tr></thead>
      <tbody>
      <?php while($l=mysqli_fetch_assoc($recentLogs)):
        $jmap=['MASUK'=>'sb-success','KELUAR'=>'sb-danger','RETURN'=>'sb-info'];
        $sc=$jmap[$l['jenis']]??'';
      ?>
      <tr>
        <td style="font-size:12px;color:#6b7280"><?= date('d/m H:i',strtotime($l['tanggal'])) ?></td>
        <td><?= htmlspecialchars($l['nama_bahan']) ?></td>
        <td><span class="rbadge <?=$sc?>"><?= $l['jenis'] ?></span></td>
        <td style="font-weight:700"><?= ($l['perubahan']>0?'+':'').number_format($l['perubahan']) ?></td>
        <td style="font-size:12px;color:#6b7280"><?= htmlspecialchars(substr($l['alasan'],0,40)) ?></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
