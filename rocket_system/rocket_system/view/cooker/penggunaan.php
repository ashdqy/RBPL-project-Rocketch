<?php
require_once __DIR__ . '/../../middleware/auth.php';
if($_SESSION['role']!=='COOKER'){header("Location: ../../dashboard.php");exit;}
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Input Penggunaan Bahan';
$msg = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $id_stok  = (int)($_POST['id_stok']??0);
    $jumlah   = (int)($_POST['jumlah']??0);
    $alasan   = trim($_POST['alasan']??'Penggunaan memasak');

    if($id_stok && $jumlah > 0) {
        $stok = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM stok WHERE id_stok=$id_stok"));
        if($stok && $stok['jumlah'] >= $jumlah) {
            mysqli_query($conn,"UPDATE stok SET jumlah=jumlah-$jumlah WHERE id_stok=$id_stok");
            $neg = -$jumlah;
            $id_user = (int)$_SESSION['id_user'];
            mysqli_query($conn,"INSERT INTO stok_log (id_stok,perubahan,alasan,id_user,jenis) VALUES($id_stok,$neg,'".mysqli_real_escape_string($conn,$alasan)."',$id_user,'KELUAR')");
            $msg = "Penggunaan $jumlah {$stok['satuan']} {$stok['nama_bahan']} berhasil dicatat.";
        } else {
            $msg = "ERROR: Stok tidak mencukupi. Sisa: ".($stok['jumlah']??0)." {$stok['satuan']}";
        }
    } else { $msg = 'ERROR: Data tidak lengkap.'; }
}

$stokList = mysqli_query($conn,"SELECT * FROM stok WHERE jumlah>0 ORDER BY nama_bahan");
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
    <div class="c-head"><h5><i class="fa-solid fa-fire-burner me-2" style="color:#8b5cf6"></i>Catat Penggunaan Bahan</h5></div>
    <div class="c-body">
      <form method="POST">
        <div class="mb-3">
          <label class="form-label" style="font-size:13px;font-weight:600">Pilih Bahan</label>
          <select name="id_stok" class="form-select" style="border-radius:8px" required onchange="updateSisa(this)">
            <option value="">-- Pilih Bahan --</option>
            <?php $stokOpt=mysqli_query($conn,"SELECT * FROM stok WHERE jumlah>0 ORDER BY nama_bahan"); while($s=mysqli_fetch_assoc($stokOpt)): ?>
            <option value="<?=$s['id_stok']?>" data-sisa="<?=$s['jumlah']?>" data-sat="<?= htmlspecialchars($s['satuan']) ?>">
              <?= htmlspecialchars($s['nama_bahan']) ?> (Sisa: <?=$s['jumlah']?> <?=$s['satuan']?>)
            </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="mb-1">
          <label class="form-label" style="font-size:13px;font-weight:600">Jumlah Digunakan</label>
          <input type="number" name="jumlah" id="jumlahInput" class="form-control" style="border-radius:8px" min="1" required>
        </div>
        <div class="mb-3">
          <small id="sisaInfo" class="text-muted" style="font-size:11px"></small>
        </div>
        <div class="mb-3">
          <label class="form-label" style="font-size:13px;font-weight:600">Keterangan</label>
          <input type="text" name="alasan" class="form-control" style="border-radius:8px" placeholder="cth: Masak ayam goreng batch pagi">
        </div>
        <button type="submit" class="btn w-100" style="background:#8b5cf6;color:#fff;border-radius:8px;font-size:13px">
          <i class="fa-solid fa-save me-1"></i>Simpan Penggunaan
        </button>
      </form>
    </div>
  </div>
</div>

<div class="col-lg-8">
  <div class="card-box">
    <div class="c-head"><h5><i class="fa-solid fa-boxes-stacked me-2" style="color:#f59e0b"></i>Stok Tersedia</h5></div>
    <div class="c-body p-0">
      <table class="table table-hover mb-0">
        <thead><tr><th>Nama Bahan</th><th>Tersedia</th><th>Satuan</th><th>Status</th></tr></thead>
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

<script>
function updateSisa(sel) {
  const opt = sel.options[sel.selectedIndex];
  const sisa = opt.dataset.sisa;
  const sat  = opt.dataset.sat;
  const info = document.getElementById('sisaInfo');
  const input = document.getElementById('jumlahInput');
  if(sisa) {
    info.textContent = `Sisa stok: ${sisa} ${sat}`;
    input.max = sisa;
  } else {
    info.textContent = '';
    input.removeAttribute('max');
  }
}
</script>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
