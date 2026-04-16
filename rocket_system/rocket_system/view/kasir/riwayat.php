<?php
require_once __DIR__ . '/../../middleware/auth.php';
if($_SESSION['role']!=='KASIR'){header("Location: ../../dashboard.php");exit;}
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Riwayat Transaksi';
$sukses = $_GET['sukses']??'';

$trxList = mysqli_query($conn,"SELECT * FROM transaksi WHERE id_kasir=".(int)$_SESSION['id_user']." ORDER BY tanggal DESC LIMIT 50");
include __DIR__ . '/../../includes/layout_start.php';
?>

<?php if($sukses): ?>
<div class="alert sb-success alert-auto d-flex gap-2 align-items-center mb-3" style="border-radius:9px;font-size:13px;padding:10px 14px">
  <i class="fa-solid fa-check-circle"></i><?= htmlspecialchars($sukses) ?>
</div>
<?php endif; ?>

<div class="card-box">
  <div class="c-head">
    <h5><i class="fa-solid fa-clock-rotate-left me-2" style="color:#10b981"></i>Riwayat Transaksi Saya</h5>
  </div>
  <div class="c-body p-0">
    <table class="table table-hover mb-0">
      <thead><tr><th>No Struk</th><th>Tanggal</th><th>Total</th><th>Metode</th><th>Status</th></tr></thead>
      <tbody>
      <?php while($t=mysqli_fetch_assoc($trxList)):
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
