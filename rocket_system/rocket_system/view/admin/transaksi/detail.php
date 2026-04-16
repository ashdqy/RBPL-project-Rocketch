<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/rocket_system/config/app.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/rocket_system/middleware/role_admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/rocket_system/config/database.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT t.*, u.nama as nama_kasir FROM transaksi t JOIN users u ON t.id_kasir=u.id_user WHERE t.id_transaksi=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$trx = $stmt->get_result()->fetch_assoc();
if (!$trx) { header("Location: index.php"); exit; }

$details = $conn->query("SELECT dt.*, m.nama_menu FROM detail_transaksi dt JOIN menu m ON dt.id_menu=m.id_menu WHERE dt.id_transaksi=$id");

$pageTitle  = 'Detail Transaksi';
$activeMenu = 'Monitor Transaksi';
require_once $_SERVER['DOCUMENT_ROOT'] . '/rocket_system/view/layout/header.php';
?>
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-receipt me-2"></i>Detail Transaksi</span>
        <a href="index.php" class="btn btn-sm btn-outline-secondary">← Kembali</a>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-5">No Struk</dt><dd class="col-7"><?= htmlspecialchars($trx['no_struk']) ?></dd>
            <dt class="col-5">Kasir</dt><dd class="col-7"><?= htmlspecialchars($trx['nama_kasir']) ?></dd>
            <dt class="col-5">Tanggal</dt><dd class="col-7"><?= date('d/m/Y H:i:s', strtotime($trx['tanggal'])) ?></dd>
            <dt class="col-5">Metode</dt><dd class="col-7"><?= $trx['metode_pembayaran'] ?></dd>
            <dt class="col-5">Status</dt><dd class="col-7">
                <?php $bc=['PAID'=>'success','PENDING'=>'warning','FAILED'=>'danger']; ?>
                <span class="badge bg-<?= $bc[$trx['status_pembayaran']] ?>"><?= $trx['status_pembayaran'] ?></span>
            </dd>
        </dl>
        <table class="table table-bordered">
            <thead class="table-light"><tr><th>Menu</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead>
            <tbody>
            <?php while($d = $details->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($d['nama_menu']) ?></td>
                <td><?= $d['jumlah'] ?></td>
                <td>Rp <?= number_format($d['harga'],0,',','.') ?></td>
                <td>Rp <?= number_format($d['subtotal'],0,',','.') ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold"><td colspan="3" class="text-end">TOTAL</td><td>Rp <?= number_format($trx['total'],0,',','.') ?></td></tr>
            </tfoot>
        </table>
    </div>
</div>
</div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/rocket_system/view/layout/footer.php'; ?>
