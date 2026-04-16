<?php
require_once __DIR__ . '/../../middleware/auth.php';
if($_SESSION['role']!=='TRAINING'){header("Location: ../../dashboard.php");exit;}
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Input Return Barang';
$msg = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $id_stok = (int)($_POST['id_stok']??0);
    $jumlah  = (int)($_POST['jumlah']??0);
    $alasan  = trim($_POST['alasan']??'Return barang');
    $status  = $_POST['status']??'LAYAK';

    if($id_stok && $jumlah > 0) {
        $id_user = (int)$_SESSION['id_user'];
        // Cek stok tersedia
        $stokRow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT jumlah,nama_bahan FROM stok WHERE id_stok=$id_stok"));
        
        mysqli_query($conn,"INSERT INTO return_barang (id_stok,id_user,jumlah,alasan,status) VALUES($id_stok,$id_user,$jumlah,'".mysqli_real_escape_string($conn,$alasan)."','$status')");
        
        if($status === 'LAYAK') {
            // Barang layak: kembalikan ke stok (tambah)
            mysqli_query($conn,"UPDATE stok SET jumlah=jumlah+$jumlah WHERE id_stok=$id_stok");
            mysqli_query($conn,"INSERT INTO stok_log (id_stok,perubahan,alasan,id_user,jenis) VALUES($id_stok,$jumlah,'RETURN LAYAK: ".mysqli_real_escape_string($conn,$alasan)."',$id_user,'RETURN')");
            $msg = "Return berhasil. Stok {$stokRow['nama_bahan']} bertambah +$jumlah.";
        } else {
            // Barang tidak layak/buang: KURANGI stok karena barang keluar (dibuang)
            $kurang = min($jumlah, $stokRow['jumlah']);
            mysqli_query($conn,"UPDATE stok SET jumlah=jumlah-$kurang WHERE id_stok=$id_stok");
            mysqli_query($conn,"INSERT INTO stok_log (id_stok,perubahan,alasan,id_user,jenis) VALUES($id_stok,-$kurang,'BUANG/TIDAK LAYAK: ".mysqli_real_escape_string($conn,$alasan)."',$id_user,'KELUAR')");
            $msg = "Return dicatat. Barang tidak layak — stok {$stokRow['nama_bahan']} berkurang -$kurang (dibuang).";
        }
    } else { $msg = 'ERROR: Data tidak lengkap.'; }
}

$returnList = mysqli_query($conn,"SELECT rb.*, s.nama_bahan, u.nama FROM return_barang rb JOIN stok s ON rb.id_stok=s.id_stok JOIN users u ON rb.id_user=u.id_user ORDER BY rb.tanggal DESC LIMIT 20");
include __DIR__ . '/../../includes/layout_start.php';
?>

<?php if($msg): ?>
<div class="alert <?= strpos($msg,'ERROR')?'sb-danger':'sb-success' ?> alert-auto d-flex gap-2 align-items-center mb-3" style="border-radius:9px;font-size:13px;padding:10px 14px">
  <i class="fa-solid fa-<?= strpos($msg,'ERROR')?'exclamation-triangle':'check-circle' ?>"></i><?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="row g-3">
<div class="col-lg-4">
  <div class="card-box">
    <div class="c-head"><h5><i class="fa-solid fa-rotate-left me-2" style="color:#3b82f6"></i>Input Return</h5></div>
    <div class="c-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label" style="font-size:13px;font-weight:600">Pilih Bahan</label>
          <select name="id_stok" class="form-select" style="border-radius:8px" required>
            <option value="">-- Pilih Bahan --</option>
            <?php $stokOpt=mysqli_query($conn,"SELECT * FROM stok ORDER BY nama_bahan"); while($s=mysqli_fetch_assoc($stokOpt)): ?>
            <option value="<?=$s['id_stok']?>"><?= htmlspecialchars($s['nama_bahan']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label" style="font-size:13px;font-weight:600">Jumlah Return</label>
          <input type="number" name="jumlah" class="form-control" style="border-radius:8px" min="1" required>
        </div>
        <div class="mb-3">
          <label class="form-label" style="font-size:13px;font-weight:600">Status Barang</label>
          <select name="status" class="form-select" style="border-radius:8px">
            <option value="LAYAK">LAYAK (kembalikan ke stok)</option>
            <option value="TIDAK_LAYAK">TIDAK LAYAK (buang)</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label" style="font-size:13px;font-weight:600">Alasan Return</label>
          <textarea name="alasan" class="form-control" style="border-radius:8px" rows="3" placeholder="Jelaskan alasan return..."></textarea>
        </div>
        <button type="submit" class="btn w-100" style="background:#3b82f6;color:#fff;border-radius:8px;font-size:13px">
          <i class="fa-solid fa-save me-1"></i>Simpan Return
        </button>
      </form>
    </div>
  </div>
</div>

<div class="col-lg-8">
  <div class="card-box">
    <div class="c-head"><h5><i class="fa-solid fa-list me-2" style="color:#3b82f6"></i>Riwayat Return (20 Terakhir)</h5></div>
    <div class="c-body p-0">
      <table class="table table-hover mb-0">
        <thead><tr><th>Waktu</th><th>Bahan</th><th>Jml</th><th>Status</th><th>Alasan</th></tr></thead>
        <tbody>
        <?php while($r=mysqli_fetch_assoc($returnList)):
          $sc=$r['status']==='LAYAK'?'sb-success':'sb-danger';
        ?>
        <tr>
          <td style="font-size:12px;color:#6b7280"><?= date('d/m H:i',strtotime($r['tanggal'])) ?></td>
          <td><?= htmlspecialchars($r['nama_bahan']) ?></td>
          <td style="font-weight:700"><?= number_format($r['jumlah']) ?></td>
          <td><span class="rbadge <?=$sc?>"><?= $r['status'] ?></span></td>
          <td style="font-size:12px;color:#6b7280"><?= htmlspecialchars(substr($r['alasan']??'',0,35)) ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
