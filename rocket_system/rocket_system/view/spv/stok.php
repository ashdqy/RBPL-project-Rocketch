<?php
require_once __DIR__ . '/../../middleware/role_spv.php';
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Lihat Stok';

$stokList = mysqli_query($conn,"SELECT * FROM stok ORDER BY nama_bahan");
include __DIR__ . '/../../includes/layout_start.php';
?>

<div class="card-box">
  <div class="c-head"><h5><i class="fa-solid fa-boxes-stacked me-2" style="color:#f59e0b"></i>Data Stok Bahan</h5></div>
  <div class="c-body p-0">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Nama Bahan</th><th>Jumlah</th><th>Satuan</th><th>Status</th></tr></thead>
      <tbody>
      <?php $no=1; while($s=mysqli_fetch_assoc($stokList)):
        $status = $s['jumlah']>=20?['Aman','sb-success']:($s['jumlah']>=5?['Hampir Habis','sb-warning']:['Kritis','sb-danger']);
      ?>
      <tr>
        <td><?=$no++?></td>
        <td><strong><?= htmlspecialchars($s['nama_bahan']) ?></strong></td>
        <td style="font-weight:700"><?= number_format($s['jumlah']) ?></td>
        <td><?= htmlspecialchars($s['satuan']) ?></td>
        <td><span class="rbadge <?=$status[1]?>"><?= $status[0] ?></span></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
