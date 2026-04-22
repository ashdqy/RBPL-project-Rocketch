<?php
require_once __DIR__ . '/../../middleware/role_spv.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/log_helper.php';
$pageTitle = 'Generate Laporan';
$msg = $msgType = '';
$id_spv = (int)$_SESSION['id_user'];


if (isset($_GET['export'], $_GET['id'])) {
    $id_lap = (int)$_GET['id'];
    $lap = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT l.*, u.nama spv FROM laporan l
         JOIN users u ON l.id_spv=u.id_user
         WHERE l.id_laporan=$id_lap"));
    if (!$lap) { header('Location: laporan.php'); exit; }

    if ($_GET['export'] === 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="laporan_'.str_replace(' ','_',$lap['periode']).'.xls"');
        echo "<table border='1' cellpadding='6' style='font-family:Arial;font-size:12px'>";
        echo "<tr><td colspan='2' style='background:#e63946;color:#fff;font-size:14px;font-weight:bold'>LAPORAN ".strtoupper($lap['jenis'])." - ".strtoupper($lap['periode'])."</td></tr>";
        echo "<tr><td colspan='2'></td></tr>";
        echo "<tr><td><b>SPV</b></td><td>".$lap['spv']."</td></tr>";
        echo "<tr><td><b>Jenis</b></td><td>".$lap['jenis']."</td></tr>";
        echo "<tr><td><b>Periode</b></td><td>".$lap['periode']."</td></tr>";
        echo "<tr><td><b>Status</b></td><td>".$lap['status']."</td></tr>";
        echo "<tr><td colspan='2'></td></tr>";
        $lblPenjualan   = $lap['jenis']==="STOK" ? "Total Stok Masuk"       : "Total Penjualan";
        $lblPengeluaran = $lap['jenis']==="STOK" ? "Total Stok Keluar"      : "Total Pengeluaran";
        $lblReturn      = $lap['jenis']==="STOK" ? "Selisih (Masuk-Keluar)" : "Total Return";
        $valPenjualan   = $lap['jenis']==="STOK" ? number_format($lap['total_penjualan']??0,0,',','.').' unit'  : 'Rp '.number_format($lap['total_penjualan']??0,0,',','.');
        $valPengeluaran = $lap['jenis']==="STOK" ? number_format($lap['total_pengeluaran']??0,0,',','.').' unit' : 'Rp '.number_format($lap['total_pengeluaran']??0,0,',','.');
        $valReturn      = $lap['jenis']==="STOK" ? number_format($lap['total_return']??0,0,',','.').' unit'      : 'Rp '.number_format($lap['total_return']??0,0,',','.');
        echo "<tr><td><b>$lblPenjualan</b></td><td>$valPenjualan</td></tr>";
        echo "<tr><td><b>$lblPengeluaran</b></td><td>$valPengeluaran</td></tr>";
        echo "<tr><td><b>$lblReturn</b></td><td>$valReturn</td></tr>";
        echo '<tr><td><b>Laba / Rugi</b></td><td>Rp '.number_format($lap['laba_rugi']??0,0,',','.').'</td></tr>';
        echo "<tr><td colspan='2'></td></tr>";
        echo "<tr><td><b>Dicetak</b></td><td>".date('d M Y H:i')."</td></tr>";
        echo "</table>";
        exit;
    }

    if ($_GET['export'] === 'pdf') {
        $lr = (float)($lap['laba_rugi'] ?? 0);
        ?><!DOCTYPE html><html><head><meta charset="UTF-8">
        <title>Laporan <?= htmlspecialchars($lap['periode']) ?></title>
        <style>
          *{margin:0;padding:0;box-sizing:border-box}
          body{font-family:Arial,sans-serif;padding:36px;color:#111;font-size:13px}
          .header{display:flex;align-items:center;gap:14px;border-bottom:3px solid #e63946;padding-bottom:14px;margin-bottom:20px}
          .logo{width:48px;height:48px;background:#e63946;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:22px;color:#fff}
          .brand h2{font-size:18px;color:#111827;margin-bottom:2px}
          .brand p{font-size:12px;color:#6b7280}
          h3{font-size:15px;color:#374151;margin-bottom:14px}
          table{width:100%;border-collapse:collapse}
          th,td{padding:9px 12px;text-align:left;border-bottom:1px solid #f3f4f6;font-size:13px}
          th{background:#f9fafb;font-weight:600;color:#374151;width:40%}
          .badge{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700}
          .badge-final{background:#dcfce7;color:#16a34a}
          .badge-draft{background:#fef9c3;color:#b45309}
          .badge-reject{background:#fef2f2;color:#dc2626}
          .laba{color:#16a34a;font-weight:700}
          .rugi{color:#dc2626;font-weight:700}
          .footer{margin-top:28px;font-size:11px;color:#9ca3af;text-align:right;border-top:1px solid #f3f4f6;padding-top:10px}
        </style></head><body>
        <div class="header">
          <div class="logo">🔥</div>
          <div class="brand"><h2>Rocket Chicken</h2><p>Sistem Manajemen Internal</p></div>
        </div>
        <h3>Laporan <?= $lap['jenis'] ?> — Periode <?= htmlspecialchars($lap['periode']) ?></h3>
        <table>
          <tr><th>SPV Pembuat</th><td><?= htmlspecialchars($lap['spv']) ?></td></tr>
          <tr><th>Jenis Laporan</th><td><?= $lap['jenis'] ?></td></tr>
          <tr><th>Periode</th><td><?= htmlspecialchars($lap['periode']) ?></td></tr>
          <tr><th>Status</th><td>
            <span class="badge badge-<?= strtolower($lap['status']) === 'final' ? 'final' : (strtolower($lap['status']) === 'rejected' ? 'reject' : 'draft') ?>">
              <?= $lap['status'] ?></span></td></tr>
          <tr><th><?= $lap['jenis']==='STOK'?'Total Stok Masuk':'Total Penjualan' ?></th><td><?= $lap['jenis']==='STOK'?number_format($lap['total_penjualan']??0,0,',','.').' unit':'Rp '.number_format($lap['total_penjualan']??0,0,',','.') ?></td></tr>
          <tr><th><?= $lap['jenis']==='STOK'?'Total Stok Keluar':'Total Pengeluaran' ?></th><td><?= $lap['jenis']==='STOK'?number_format($lap['total_pengeluaran']??0,0,',','.').' unit':'Rp '.number_format($lap['total_pengeluaran']??0,0,',','.') ?></td></tr>
          <tr><th><?= $lap['jenis']==='STOK'?'Return/Buang':'Total Return' ?></th><td><?= $lap['jenis']==='STOK'?number_format($lap['total_return']??0,0,',','.').' unit':'Rp '.number_format($lap['total_return']??0,0,',','.') ?></td></tr>
          <tr><th>Laba / Rugi</th><td class="<?= $lr>=0?'laba':'rugi' ?>">
            <?= $lr>=0?'+':'' ?>Rp <?= number_format($lr,0,',','.') ?></td></tr>
          <tr><th>Tanggal Dibuat</th><td><?= date('d M Y H:i', strtotime($lap['tanggal'])) ?></td></tr>
        </table>
        <div class="footer">Dicetak: <?= date('d M Y H:i') ?> &nbsp;—&nbsp; Rocket Chicken System</div>
        <script>window.onload=function(){window.print()}</script>
        </body></html><?php
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis      = $_POST['jenis']      ?? 'KEUANGAN';
    $periode    = trim($_POST['periode']    ?? '');
    $tgl_dari   = trim($_POST['tgl_dari']   ?? '');
    $tgl_sampai = trim($_POST['tgl_sampai'] ?? '');
    $finalize   = isset($_POST['finalize']) ? 'FINAL' : 'DRAFT';

   
    $dateFilter   = '';
    $periodeLabel = $periode;

    if ($tgl_dari && $tgl_sampai) {
        $df = mysqli_real_escape_string($conn, $tgl_dari);
        $ds = mysqli_real_escape_string($conn, $tgl_sampai);
        $dateFilter   = "DATE(tanggal) BETWEEN '$df' AND '$ds'";
        $periodeLabel = "$tgl_dari s/d $tgl_sampai";
    } elseif ($periode) {
        $parts = explode('-', $periode);
        if (count($parts) === 3) {
            $dateFilter = "DATE(tanggal) = '$periode'";
        } elseif (count($parts) === 2) {
            $dateFilter = "YEAR(tanggal)='{$parts[0]}' AND MONTH(tanggal)='{$parts[1]}'";
        } else {
            $dateFilter = "YEAR(tanggal)='$periode'";
        }
    }

    $totalPenjualan = 0; $totalReturn = 0; $totalPengeluaran = 0;
    $stokSnapshot = []; 

    if ($jenis === 'STOK') {
        
        $stokRows = mysqli_query($conn, "SELECT * FROM stok ORDER BY nama_bahan");
        while($s = mysqli_fetch_assoc($stokRows)) $stokSnapshot[] = $s;
        
        if($dateFilter) {
            $rKeluar = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT IFNULL(SUM(ABS(perubahan)),0) s FROM stok_log WHERE jenis='KELUAR' AND $dateFilter"));
            $totalPengeluaran = (float)$rKeluar['s'];
            $rMasuk = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT IFNULL(SUM(perubahan),0) s FROM stok_log WHERE jenis='MASUK' AND $dateFilter"));
            $totalPenjualan = (float)$rMasuk['s']; 
        }
    } elseif ($dateFilter) {
        $r = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT IFNULL(SUM(total),0) s FROM transaksi WHERE status_pembayaran='PAID' AND $dateFilter"));
        $totalPenjualan = (float)$r['s'];

        $r2 = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) c FROM return_barang WHERE $dateFilter"));
        $totalReturn = (float)$r2['c'] * 50000;
    }

    if ($jenis === 'STOK') {
        $labaRugi = $totalPenjualan - $totalPengeluaran; 
    } else {
        $labaRugi = $totalPenjualan - $totalReturn;
    }

    $j  = mysqli_real_escape_string($conn, $jenis);
    $p  = mysqli_real_escape_string($conn, $periodeLabel);
    $st = mysqli_real_escape_string($conn, $finalize);

    mysqli_query($conn,
        "INSERT INTO laporan (jenis, periode, id_spv, total_penjualan, total_pengeluaran, total_return, laba_rugi, status)
         VALUES ('$j','$p',$id_spv,$totalPenjualan,$totalPengeluaran,$totalReturn,$labaRugi,'$st')");
    $newId = mysqli_insert_id($conn);

    log_activity($conn, $id_spv, 'LAPORAN', 'CREATE',
        "Generate laporan $jenis periode $periodeLabel, status $finalize (ID: $newId)");

    $msg = "Laporan berhasil dibuat dengan status <strong>$finalize</strong>.";
    $msgType = 'success';
}


$laporanList = mysqli_query($conn,
    "SELECT l.*, u.nama spv FROM laporan l
     JOIN users u ON l.id_spv=u.id_user
     WHERE l.id_spv=$id_spv
     ORDER BY l.tanggal DESC");

include __DIR__ . '/../../includes/layout_start.php';
?>

<style>
.form-label{font-size:13px;font-weight:600;color:#374151}
.form-control,.form-select{border-radius:8px;font-size:13px;border:1.5px solid #e5e7eb}
.form-control:focus,.form-select:focus{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.15)}
.tab-pill{display:inline-flex;background:#f3f4f6;border-radius:9px;padding:3px;gap:2px;margin-bottom:14px}
.tab-pill button{border:none;background:transparent;border-radius:7px;padding:5px 14px;font-size:12px;font-weight:600;color:#6b7280;cursor:pointer;transition:.15s}
.tab-pill button.active{background:#fff;color:#8b5cf6;box-shadow:0 1px 3px rgba(0,0,0,.1)}
</style>

<?php if ($msg): ?>
<div class="alert alert-auto d-flex gap-2 align-items-center mb-3"
     style="border-radius:9px;font-size:13px;padding:10px 14px;
     background:<?=$msgType==='success'?'#f0fdf4':'#fef2f2'?>;
     color:<?=$msgType==='success'?'#16a34a':'#dc2626'?>;
     border:1px solid <?=$msgType==='success'?'#bbf7d0':'#fecaca'?>">
  <i class="fa-solid fa-check-circle"></i><?= $msg ?>
</div>
<?php endif; ?>

<div class="row g-3">
  <!-- ── FORM ── -->
  <div class="col-lg-4">
    <div class="card-box">
      <div class="c-head">
        <h5><i class="fa-solid fa-plus me-2" style="color:#8b5cf6"></i>Buat Laporan Baru</h5>
      </div>
      <div class="c-body">
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Jenis Laporan</label>
            <select name="jenis" class="form-select" required>
              <option value="KEUANGAN" selected>Keuangan</option>
              <option value="HARIAN">Harian</option>
              <option value="STOK">Stok</option>
            </select>
          </div>

          <div class="mb-1">
            <label class="form-label">Mode Periode</label>
            <div class="tab-pill">
              <button type="button" class="active" onclick="switchPeriode('preset',this)">Preset</button>
              <button type="button" onclick="switchPeriode('range',this)">Range Tanggal</button>
            </div>
          </div>

          <div id="blok-preset" class="mb-3">
            <input type="text" name="periode" class="form-control"
              placeholder="cth: 2026-03 atau 2026-03-04 atau 2026">
            <small class="text-muted" style="font-size:11px">
              Harian: YYYY-MM-DD &nbsp;|&nbsp; Bulanan: YYYY-MM &nbsp;|&nbsp; Tahunan: YYYY
            </small>
          </div>

          <div id="blok-range" class="mb-3 d-none">
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label">Dari</label>
                <input type="date" name="tgl_dari" class="form-control">
              </div>
              <div class="col-6">
                <label class="form-label">Sampai</label>
                <input type="date" name="tgl_sampai" class="form-control">
              </div>
            </div>
          </div>

          <div class="mb-3 p-3 rounded-3" style="background:#faf5ff;border:1.5px dashed #c4b5fd">
            <div class="d-flex align-items-center gap-2">
              <input type="checkbox" name="finalize" id="finalize" class="form-check-input"
                style="width:16px;height:16px;accent-color:#8b5cf6">
              <label for="finalize" class="form-check-label"
                style="font-size:13px;font-weight:600;color:#6d28d9">Langsung Finalisasi (FINAL)</label>
            </div>
            <small class="text-muted d-block mt-1" style="font-size:11px">
              Jika tidak dicentang, laporan disimpan sebagai DRAFT
            </small>
          </div>

          <button type="submit" class="btn w-100"
            style="background:#8b5cf6;color:#fff;border-radius:8px;font-size:13px;padding:9px">
            <i class="fa-solid fa-file-circle-plus me-1"></i>Generate Laporan
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- ── DAFTAR LAPORAN ── -->
  <div class="col-lg-8">
    <div class="card-box">
      <div class="c-head">
        <h5><i class="fa-solid fa-list me-2" style="color:#8b5cf6"></i>Laporan Saya</h5>
      </div>
      <div class="c-body p-0" style="overflow-x:auto">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Jenis</th><th>Periode</th><th>Penjualan / Stok Masuk</th>
              <th>Pengeluaran / Stok Keluar</th><th>Return / Selisih</th><th>Status</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $hasRow = false;
          while ($l = mysqli_fetch_assoc($laporanList)):
            $hasRow = true;
            $statusMap = [
              'DRAFT'    => ['sb-warning', 'DRAFT'],
              'FINAL'    => ['sb-success', 'FINAL'],
              'REJECTED' => ['sb-danger',  'DITOLAK'],
              'REVISI'   => ['sb-info',    'REVISI'],
            ];
            [$sc, $sl] = $statusMap[$l['status']] ?? ['sb-warning','DRAFT'];
            $lr = (float)($l['laba_rugi'] ?? 0);
          ?>
          <tr>
            <td><span class="rbadge sb-info"><?= $l['jenis'] ?></span></td>
            <td style="font-size:12px"><?= htmlspecialchars($l['periode']) ?></td>
            <td style="font-size:12px"><?= $l['jenis']==='STOK'?number_format($l['total_penjualan']??0,0,',','.').' unit masuk':'Rp '.number_format($l['total_penjualan']??0,0,',','.') ?></td>
            <td style="font-size:12px;color:#dc2626"><?= $l['jenis']==='STOK'?number_format($l['total_pengeluaran']??0,0,',','.').' unit keluar':'Rp '.number_format($l['total_return']??0,0,',','.') ?></td>
            <td style="font-weight:700;font-size:12px;color:<?= $lr>=0?'#16a34a':'#dc2626' ?>">
              <?= $l['jenis']==='STOK'?'Selisih: '.(($l['total_penjualan']??0)-($l['total_pengeluaran']??0)).' unit':(($lr>=0?'+':'').'Rp '.number_format($lr,0,',','.')) ?>
            </td>
            <td><span class="rbadge <?= $sc ?>"><?= $sl ?></span></td>
            <td>
              <div class="d-flex gap-1 flex-wrap">
                <a href="?export=pdf&id=<?= $l['id_laporan'] ?>" target="_blank"
                   class="btn btn-sm btn-outline-danger" style="font-size:10px;border-radius:5px" title="Export PDF">
                   <i class="fa-solid fa-file-pdf"></i></a>
                <a href="?export=excel&id=<?= $l['id_laporan'] ?>"
                   class="btn btn-sm btn-outline-success" style="font-size:10px;border-radius:5px" title="Export Excel">
                   <i class="fa-solid fa-file-excel"></i></a>
                <?php if (in_array($l['status'], ['DRAFT','REVISI'])): ?>
                <a href="validasi.php?submit=<?= $l['id_laporan'] ?>"
                   class="btn btn-sm btn-outline-primary" style="font-size:10px;border-radius:5px"
                   title="Kirim ke validasi"
                   onclick="return confirm('Kirim laporan ini untuk divalidasi?')">
                   <i class="fa-solid fa-paper-plane"></i></a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php if (!$hasRow): ?>
          <tr><td colspan="7" class="text-center py-4 text-muted" style="font-size:13px">
            <i class="fa-solid fa-file-circle-plus me-2" style="color:#8b5cf6"></i>Belum ada laporan dibuat
          </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function switchPeriode(mode, btn) {
  document.querySelectorAll('.tab-pill button').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('blok-preset').classList.toggle('d-none', mode !== 'preset');
  document.getElementById('blok-range').classList.toggle('d-none', mode !== 'range');
}
</script>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
