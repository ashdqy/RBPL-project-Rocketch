<?php
require_once __DIR__ . '/../../../middleware/role_admin.php';
require_once __DIR__ . '/../../../config/database.php';
$pageTitle = 'Audit Transaksi';

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

$trxList = mysqli_query($conn,"
    SELECT t.*, u.nama as kasir, 
           (SELECT COUNT(*) FROM detail_transaksi dt WHERE dt.id_transaksi=t.id_transaksi) as jml_item
    FROM transaksi t JOIN users u ON t.id_kasir=u.id_user
    WHERE DATE(t.tanggal) BETWEEN '$from' AND '$to'
    ORDER BY t.tanggal DESC
");

$summary = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) as total, IFNULL(SUM(total),0) as revenue
    FROM transaksi WHERE status_pembayaran='PAID' AND DATE(tanggal) BETWEEN '$from' AND '$to'
"));

include __DIR__ . '/../../../includes/layout_start.php';
?>

<form method="GET" class="row g-2 mb-3">
  <div class="col-auto"><input type="date" name="from" value="<?=$from?>" class="form-control form-control-sm" style="border-radius:7px"></div>
  <div class="col-auto"><span class="align-self-center text-muted">s/d</span></div>
  <div class="col-auto"><input type="date" name="to" value="<?=$to?>" class="form-control form-control-sm" style="border-radius:7px"></div>
  <div class="col-auto"><button type="submit" class="btn btn-sm btn-secondary" style="border-radius:7px;font-size:12px">Filter</button></div>
</form>

<div class="row g-3 mb-3">
  <div class="col-sm-6">
    <div class="stat-card">
      <div class="s-ic mb-2" style="background:rgba(16,185,129,.1);color:#10b981"><i class="fa-solid fa-receipt"></i></div>
      <div style="font-size:22px;font-weight:800;color:#111827"><?= number_format($summary['total']) ?></div>
      <div style="font-size:12px;color:#6b7280">Transaksi PAID</div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="stat-card">
      <div class="s-ic mb-2" style="background:rgba(230,57,70,.1);color:#e63946"><i class="fa-solid fa-sack-dollar"></i></div>
      <div style="font-size:22px;font-weight:800;color:#111827">Rp <?= number_format($summary['revenue'],0,',','.') ?></div>
      <div style="font-size:12px;color:#6b7280">Total Pendapatan</div>
    </div>
  </div>
</div>

<div class="card-box">
  <div class="c-head">
    <h5><i class="fa-solid fa-receipt me-2" style="color:#10b981"></i>Daftar Transaksi</h5>
  </div>
  <div class="c-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead><tr><th>No Struk</th><th>Kasir</th><th>Tanggal</th><th>Item</th><th>Total</th><th>Metode</th><th>Status</th></tr></thead>
        <tbody>
        <?php while($t=mysqli_fetch_assoc($trxList)):
          $sc=['PAID'=>'sb-success','PENDING'=>'sb-warning','FAILED'=>'sb-danger'][$t['status_pembayaran']]??'';
        ?>
        <tr>
          <td><code style="font-size:11.5px"><?= htmlspecialchars($t['no_struk']) ?></code></td>
          <td><?= htmlspecialchars($t['kasir']) ?></td>
          <td style="font-size:12px;color:#6b7280"><?= date('d/m/Y H:i',strtotime($t['tanggal'])) ?></td>
          <td><?= $t['jml_item'] ?> item</td>
          <td style="font-weight:700">Rp <?= number_format($t['total'],0,',','.') ?></td>
          <td><?= $t['metode_pembayaran'] ?></td>
          <td><span class="rbadge <?=$sc?>"><?= $t['status_pembayaran'] ?></span></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../../includes/layout_end.php'; ?>
