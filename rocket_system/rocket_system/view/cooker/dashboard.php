<?php
require_once __DIR__ . '/../../middleware/auth.php';
if($_SESSION['role']!=='COOKER'){header("Location: ../../dashboard.php");exit;}
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Dashboard Cooker';

$stokRendah = mysqli_query($conn,"SELECT * FROM stok ORDER BY jumlah ASC LIMIT 5");
$myLogs = mysqli_query($conn,"SELECT sl.*, s.nama_bahan FROM stok_log sl JOIN stok s ON sl.id_stok=s.id_stok WHERE sl.id_user=".(int)$_SESSION['id_user']." ORDER BY sl.tanggal DESC LIMIT 7");
$totalKeluar = mysqli_fetch_assoc(mysqli_query($conn,"SELECT IFNULL(SUM(ABS(perubahan)),0) c FROM stok_log WHERE id_user=".(int)$_SESSION['id_user']." AND jenis='KELUAR' AND DATE(tanggal)=CURDATE()"))['c'];

include __DIR__ . '/../../includes/layout_start.php';
?>

<div class="row g-3 mb-4">
  <div class="col-sm-6">
    <div class="stat-card"><div class="s-ic mb-2" style="background:rgba(139,92,246,.1);color:#8b5cf6"><i class="fa-solid fa-fire-burner"></i></div>
      <div style="font-size:24px;font-weight:800"><?= number_format($totalKeluar) ?></div>
      <div style="font-size:12px;color:#6b7280">Bahan Terpakai Hari Ini</div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="d-flex gap-2">
      <a href="penggunaan.php" class="btn w-100 py-3" style="background:#8b5cf6;color:#fff;border-radius:9px;font-size:13px">
        <i class="fa-solid fa-fire-burner d-block mb-1" style="font-size:18px"></i>Input Penggunaan
      </a>
      <a href="sisa.php" class="btn w-100 py-3" style="background:#f59e0b;color:#fff;border-radius:9px;font-size:13px">
        <i class="fa-solid fa-scale-balanced d-block mb-1" style="font-size:18px"></i>Update Sisa
      </a>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card-box">
      <div class="c-head"><h5><i class="fa-solid fa-triangle-exclamation me-2" style="color:#f59e0b"></i>Status Stok</h5></div>
      <div class="c-body p-0">
        <table class="table mb-0">
          <thead><tr><th>Bahan</th><th>Jumlah</th><th>Status</th></tr></thead>
          <tbody>
          <?php while($s=mysqli_fetch_assoc($stokRendah)):
            $status=$s['jumlah']>=20?['Aman','sb-success']:($s['jumlah']>=5?['Hampir Habis','sb-warning']:['Kritis!','sb-danger']);
          ?>
          <tr>
            <td><?= htmlspecialchars($s['nama_bahan']) ?></td>
            <td><?= $s['jumlah'] ?> <?= $s['satuan'] ?></td>
            <td><span class="rbadge <?=$status[1]?>"><?= $status[0] ?></span></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card-box">
      <div class="c-head"><h5><i class="fa-solid fa-scroll me-2" style="color:#8b5cf6"></i>Aktivitas Saya</h5></div>
      <div class="c-body p-0">
        <table class="table mb-0">
          <thead><tr><th>Waktu</th><th>Bahan</th><th>Jenis</th><th>Jml</th></tr></thead>
          <tbody>
          <?php while($l=mysqli_fetch_assoc($myLogs)):
            $jmap=['MASUK'=>'sb-success','KELUAR'=>'sb-danger','RETURN'=>'sb-info'];
          ?>
          <tr>
            <td style="font-size:11px;color:#6b7280"><?= date('d/m H:i',strtotime($l['tanggal'])) ?></td>
            <td style="font-size:12px"><?= htmlspecialchars($l['nama_bahan']) ?></td>
            <td><span class="rbadge <?=$jmap[$l['jenis']]??''?>"><?= $l['jenis'] ?></span></td>
            <td style="font-weight:700"><?= $l['perubahan'] ?></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
