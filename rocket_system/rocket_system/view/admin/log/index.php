<?php
require_once __DIR__ . '/../../../middleware/role_admin.php';
require_once __DIR__ . '/../../../config/database.php';
$pageTitle = 'Log Aktivitas Stok';

$logs = mysqli_query($conn,"
    SELECT sl.*, s.nama_bahan, u.nama as nama_user 
    FROM stok_log sl 
    JOIN stok s ON sl.id_stok=s.id_stok 
    JOIN users u ON sl.id_user=u.id_user 
    ORDER BY sl.tanggal DESC LIMIT 100
");

include __DIR__ . '/../../../includes/layout_start.php';
?>

<div class="card-box">
  <div class="c-head">
    <h5><i class="fa-solid fa-scroll me-2" style="color:#f59e0b"></i>Log Aktivitas Stok (100 Terakhir)</h5>
  </div>
  <div class="c-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>#</th><th>Tanggal</th><th>User</th><th>Bahan</th><th>Jenis</th><th>Perubahan</th><th>Keterangan</th></tr></thead>
        <tbody>
        <?php $no=1; while($l=mysqli_fetch_assoc($logs)):
          $jmap=['MASUK'=>['sb-success','fa-arrow-down'],'KELUAR'=>['sb-danger','fa-arrow-up'],'RETURN'=>['sb-info','fa-rotate-left']];
          [$sc,$ic]=$jmap[$l['jenis']]??['','fa-circle'];
        ?>
        <tr>
          <td><?=$no++?></td>
          <td style="font-size:12px;color:#6b7280"><?= date('d/m/Y H:i', strtotime($l['tanggal'])) ?></td>
          <td><?= htmlspecialchars($l['nama_user']) ?></td>
          <td><strong><?= htmlspecialchars($l['nama_bahan']) ?></strong></td>
          <td><span class="rbadge <?=$sc?>"><i class="fa-solid <?=$ic?> me-1"></i><?= $l['jenis'] ?></span></td>
          <td style="font-weight:700"><?= ($l['perubahan']>0?'+':'').number_format($l['perubahan']) ?></td>
          <td style="font-size:12px;color:#6b7280"><?= htmlspecialchars($l['alasan']) ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../../includes/layout_end.php'; ?>
