<?php
require_once __DIR__ . '/../../middleware/auth.php';
if($_SESSION['role']!=='TRAINING'){header("Location: ../../dashboard.php");exit;}
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Input Barang Masuk';
$msg = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $id_stok = (int)($_POST['id_stok']??0);
    $jumlah  = (int)($_POST['jumlah']??0);
    $alasan  = trim($_POST['alasan']??'Barang masuk');
    $nama_baru = trim($_POST['nama_baru']??'');
    $satuan_baru = trim($_POST['satuan_baru']??'');

    if($nama_baru && $satuan_baru) {
        // New item
        mysqli_query($conn,"INSERT INTO stok (nama_bahan,jumlah,satuan) VALUES('".mysqli_real_escape_string($conn,$nama_baru)."',$jumlah,'".mysqli_real_escape_string($conn,$satuan_baru)."')");
        $id_stok = mysqli_insert_id($conn);
        $alasan = "Stok awal - ".($alasan ?: 'Barang baru ditambahkan');
        mysqli_query($conn,"INSERT INTO stok_log (id_stok,perubahan,alasan,id_user,jenis) VALUES($id_stok,$jumlah,'".mysqli_real_escape_string($conn,$alasan)."',".(int)$_SESSION['id_user'].",'MASUK')");
        $msg = "Bahan baru '$nama_baru' berhasil ditambahkan.";
    } elseif($id_stok && $jumlah > 0) {
        mysqli_query($conn,"UPDATE stok SET jumlah=jumlah+$jumlah WHERE id_stok=$id_stok");
        mysqli_query($conn,"INSERT INTO stok_log (id_stok,perubahan,alasan,id_user,jenis) VALUES($id_stok,$jumlah,'".mysqli_real_escape_string($conn,$alasan)."',".(int)$_SESSION['id_user'].",'MASUK')");
        $msg = "Stok berhasil diperbarui (+$jumlah).";
    } else {
        $msg = 'ERROR: Data tidak lengkap.';
    }
}

$stokList = mysqli_query($conn,"SELECT * FROM stok ORDER BY nama_bahan");
include __DIR__ . '/../../includes/layout_start.php';
?>

<?php if($msg): ?>
<div class="alert <?= strpos($msg,'ERROR')?'sb-danger':'sb-success' ?> alert-auto d-flex gap-2 align-items-center mb-3" style="border-radius:9px;font-size:13px;padding:10px 14px">
  <i class="fa-solid fa-<?= strpos($msg,'ERROR')?'exclamation-triangle':'check-circle' ?>"></i><?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="row g-3">
<div class="col-lg-5">
  <div class="card-box">
    <div class="c-head"><h5><i class="fa-solid fa-plus-circle me-2" style="color:#10b981"></i>Input Barang Masuk</h5></div>
    <div class="c-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label" style="font-size:13px;font-weight:600">Pilih Bahan (yang sudah ada)</label>
          <select name="id_stok" class="form-select" style="border-radius:8px">
            <option value="">-- Tambah bahan baru di bawah --</option>
            <?php $stokOpt=mysqli_query($conn,"SELECT * FROM stok ORDER BY nama_bahan"); while($s=mysqli_fetch_assoc($stokOpt)): ?>
            <option value="<?=$s['id_stok']?>"><?= htmlspecialchars($s['nama_bahan']) ?> (Sisa: <?=$s['jumlah']?> <?=$s['satuan']?>)</option>
            <?php endwhile; ?>
          </select>
        </div>
        <hr style="border-color:#f3f4f6"><p style="font-size:12px;color:#9ca3af;text-align:center">— ATAU tambah bahan baru —</p>
        <div class="mb-2">
          <label class="form-label" style="font-size:13px;font-weight:600">Nama Bahan Baru</label>
          <input type="text" name="nama_baru" class="form-control" style="border-radius:8px" placeholder="cth: Tepung Terigu">
        </div>
        <div class="mb-3">
          <label class="form-label" style="font-size:13px;font-weight:600">Satuan</label>
          <input type="text" name="satuan_baru" class="form-control" style="border-radius:8px" placeholder="cth: kg, liter, pcs">
        </div>
        <hr style="border-color:#f3f4f6">
        <div class="mb-2">
          <label class="form-label" style="font-size:13px;font-weight:600">Jumlah Masuk</label>
          <input type="number" name="jumlah" class="form-control" style="border-radius:8px" min="1" required placeholder="Jumlah">
        </div>
        <div class="mb-3">
          <label class="form-label" style="font-size:13px;font-weight:600">Keterangan</label>
          <input type="text" name="alasan" class="form-control" style="border-radius:8px" placeholder="Keterangan / sumber barang">
        </div>
        <button type="submit" class="btn w-100" style="background:#10b981;color:#fff;border-radius:8px;font-size:13px">
          <i class="fa-solid fa-save me-1"></i>Simpan
        </button>
      </form>
    </div>
  </div>
</div>

<div class="col-lg-7">
  <div class="card-box">
    <div class="c-head"><h5><i class="fa-solid fa-boxes-stacked me-2" style="color:#f59e0b"></i>Data Stok Saat Ini</h5></div>
    <div class="c-body p-0">
      <table class="table table-hover mb-0">
        <thead><tr><th>Nama Bahan</th><th>Jumlah</th><th>Satuan</th><th>Status</th></tr></thead>
        <tbody>
        <?php while($s=mysqli_fetch_assoc($stokList)):
          $status=$s['jumlah']>=20?['Aman','sb-success']:($s['jumlah']>=5?['Hampir Habis','sb-warning']:['Kritis','sb-danger']);
        ?>
        <tr>
          <td><?= htmlspecialchars($s['nama_bahan']) ?></td>
          <td style="font-weight:700"><?= number_format($s['jumlah']) ?></td>
          <td><?= htmlspecialchars($s['satuan']) ?></td>
          <td><span class="rbadge <?=$status[1]?>"><?= $status[0] ?></span></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
