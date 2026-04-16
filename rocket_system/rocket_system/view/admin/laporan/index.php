<?php
require_once __DIR__ . '/../../../middleware/role_admin.php';
require_once __DIR__ . '/../../../config/database.php';
$pageTitle = 'Monitor Laporan';

$laporan = mysqli_query($conn,"
    SELECT l.*, u.nama as spv_nama 
    FROM laporan l JOIN users u ON l.id_spv=u.id_user 
    ORDER BY l.tanggal DESC
");

include __DIR__ . '/../../../includes/layout_start.php';
?>

<div class="card-box">
  <div class="c-head">
    <h5><i class="fa-solid fa-chart-line me-2" style="color:#8b5cf6"></i>Semua Laporan</h5>
  </div>
  <div class="c-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>Jenis</th><th>Periode</th><th>SPV</th><th>Total Penjualan</th><th>Laba/Rugi</th><th>Status</th><th>Tanggal</th></tr></thead>
        <tbody>
        <?php while($l=mysqli_fetch_assoc($laporan)):
          $sc=$l['status']==='FINAL'?'sb-success':'sb-warning';
        ?>
        <tr>
          <td><span class="rbadge sb-info"><?= $l['jenis'] ?></span></td>
          <td><?= htmlspecialchars($l['periode']) ?></td>
          <td><?= htmlspecialchars($l['spv_nama']) ?></td>
          <td>Rp <?= number_format($l['total_penjualan']??0,0,',','.') ?></td>
          <td style="color:<?= ($l['laba_rugi']??0)>=0?'#16a34a':'#dc2626' ?>;font-weight:700">
            Rp <?= number_format($l['laba_rugi']??0,0,',','.') ?></td>
          <td><span class="rbadge <?=$sc?>"><?= $l['status'] ?></span></td>
          <td style="font-size:12px;color:#6b7280"><?= date('d/m/Y',strtotime($l['tanggal'])) ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../../includes/layout_end.php'; ?>
