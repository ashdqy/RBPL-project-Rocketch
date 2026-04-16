<?php
require_once __DIR__ . '/../../middleware/auth.php';
if($_SESSION['role']!=='COOKER'){header("Location: ../../dashboard.php");exit;}
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Update Sisa Stok';
$msg = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $id_stok = (int)($_POST['id_stok']??0);
    $jumlah_baru = (int)($_POST['jumlah_baru']??0);
    $alasan = trim($_POST['alasan']??'Update sisa stok oleh cooker');
    
    if($id_stok) {
        $stok = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM stok WHERE id_stok=$id_stok"));
        $selisih = $jumlah_baru - $stok['jumlah'];
        $jenis   = $selisih >= 0 ? 'MASUK' : 'KELUAR';
        
        mysqli_query($conn,"UPDATE stok SET jumlah=$jumlah_baru WHERE id_stok=$id_stok");
        if($selisih !== 0) {
            $id_user = (int)$_SESSION['id_user'];
            mysqli_query($conn,"INSERT INTO stok_log (id_stok,perubahan,alasan,id_user,jenis) VALUES($id_stok,$selisih,'KOREKSI: ".mysqli_real_escape_string($conn,$alasan)."',$id_user,'$jenis')");
        }
        $msg = "Stok {$stok['nama_bahan']} diperbarui menjadi $jumlah_baru {$stok['satuan']}.";
    }
}

$stokList = mysqli_query($conn,"SELECT * FROM stok ORDER BY nama_bahan");
include __DIR__ . '/../../includes/layout_start.php';
?>

<?php if($msg): ?>
<div class="alert sb-success alert-auto d-flex gap-2 align-items-center mb-3" style="border-radius:9px;font-size:13px;padding:10px 14px">
  <i class="fa-solid fa-check-circle"></i><?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="card-box">
  <div class="c-head"><h5><i class="fa-solid fa-scale-balanced me-2" style="color:#f59e0b"></i>Koreksi / Update Jumlah Stok</h5></div>
  <div class="c-body">
    <p class="text-muted" style="font-size:13px">Gunakan fitur ini untuk mengoreksi jumlah stok berdasarkan hasil stock opname atau penghitungan fisik.</p>
    
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>Nama Bahan</th><th>Jumlah Sistem</th><th>Satuan</th><th>Update Jumlah</th></tr></thead>
        <tbody>
        <?php while($s=mysqli_fetch_assoc($stokList)): ?>
        <tr>
          <td><strong><?= htmlspecialchars($s['nama_bahan']) ?></strong></td>
          <td style="font-weight:700;color:<?= $s['jumlah']<5?'#dc2626':($s['jumlah']<20?'#d97706':'#16a34a') ?>"><?= number_format($s['jumlah']) ?></td>
          <td><?= htmlspecialchars($s['satuan']) ?></td>
          <td>
            <form method="POST" class="d-flex gap-2 align-items-center" style="min-width:200px">
              <input type="hidden" name="id_stok" value="<?=$s['id_stok']?>">
              <input type="hidden" name="alasan" value="Koreksi stock opname">
              <input type="number" name="jumlah_baru" class="form-control form-control-sm" style="width:90px;border-radius:6px" value="<?=$s['jumlah']?>" min="0">
              <button type="submit" class="btn btn-sm" style="background:#f59e0b;color:#fff;border-radius:6px;font-size:11px;white-space:nowrap">Update</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
