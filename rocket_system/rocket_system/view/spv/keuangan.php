<?php
require_once __DIR__ . '/../../middleware/role_spv.php';
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Laporan Keuangan';

$fDari   = $_GET['dari']   ?? date('Y-m-01');
$fSampai = $_GET['sampai'] ?? date('Y-m-d');
$df = mysqli_real_escape_string($conn,$fDari);
$ds = mysqli_real_escape_string($conn,$fSampai);

// ── SUMMARY KEUANGAN ─────────────────────────────────────────
$penjualan = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT IFNULL(SUM(total),0) s, COUNT(*) c
     FROM transaksi WHERE status_pembayaran='PAID'
     AND DATE(tanggal) BETWEEN '$df' AND '$ds'"));

$returnData = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) c FROM return_barang
     WHERE DATE(tanggal) BETWEEN '$df' AND '$ds'"));

// Per metode pembayaran
$perMetode = mysqli_query($conn,
    "SELECT metode_pembayaran, COUNT(*) c, IFNULL(SUM(total),0) s
     FROM transaksi WHERE status_pembayaran='PAID'
     AND DATE(tanggal) BETWEEN '$df' AND '$ds'
     GROUP BY metode_pembayaran");

// Harian dalam range
$harian = mysqli_query($conn,
    "SELECT DATE(tanggal) tgl,
            COUNT(*) c,
            IFNULL(SUM(CASE WHEN status_pembayaran='PAID' THEN total END),0) paid,
            IFNULL(SUM(CASE WHEN status_pembayaran='FAILED' THEN total END),0) failed,
            COUNT(CASE WHEN status_pembayaran='PENDING' THEN 1 END) pending
     FROM transaksi
     WHERE DATE(tanggal) BETWEEN '$df' AND '$ds'
     GROUP BY DATE(tanggal) ORDER BY tgl DESC");

// Top 10 menu terjual
$topMenu = mysqli_query($conn,
    "SELECT m.nama_menu, m.kategori,
            SUM(dt.jumlah) total_qty,
            SUM(dt.subtotal) total_rev
     FROM detail_transaksi dt
     JOIN menu m ON dt.id_menu=m.id_menu
     JOIN transaksi t ON dt.id_transaksi=t.id_transaksi
     WHERE t.status_pembayaran='PAID'
     AND DATE(t.tanggal) BETWEEN '$df' AND '$ds'
     GROUP BY dt.id_menu
     ORDER BY total_qty DESC LIMIT 10");

// Per kasir
$perKasir = mysqli_query($conn,
    "SELECT u.nama, COUNT(*) c, IFNULL(SUM(t.total),0) s
     FROM transaksi t JOIN users u ON t.id_kasir=u.id_user
     WHERE t.status_pembayaran='PAID'
     AND DATE(t.tanggal) BETWEEN '$df' AND '$ds'
     GROUP BY t.id_kasir ORDER BY s DESC");

$totalRev   = (float)$penjualan['s'];
$totalTrx   = (int)$penjualan['c'];
$estimReturn= (int)$returnData['c'] * 50000;
$labaRugi   = $totalRev - $estimReturn;

include __DIR__ . '/../../includes/layout_start.php';
?>

<style>
.form-control,.form-select{border-radius:8px;font-size:12px;border:1.5px solid #e5e7eb;padding:5px 10px}
.filter-bar{background:#fff;border-radius:12px;padding:14px 18px;margin-bottom:16px;
            box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.04)}
.section-title{font-size:13px;font-weight:700;color:#374151;margin-bottom:10px;
               padding-bottom:8px;border-bottom:2px solid #f3f4f6}
.progress-bar-custom{height:8px;border-radius:4px;background:#e63946;transition:.3s}
</style>

<!-- Filter -->
<div class="filter-bar">
  <form method="GET" class="d-flex gap-3 align-items-end flex-wrap">
    <div>
      <label style="font-size:11px;font-weight:600;color:#6b7280;display:block">Dari</label>
      <input type="date" name="dari" class="form-control" value="<?=$fDari?>">
    </div>
    <div>
      <label style="font-size:11px;font-weight:600;color:#6b7280;display:block">Sampai</label>
      <input type="date" name="sampai" class="form-control" value="<?=$fSampai?>">
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-sm"
        style="background:#111827;color:#fff;border-radius:7px;font-size:12px;padding:6px 14px;border:none">
        <i class="fa-solid fa-filter me-1"></i>Terapkan
      </button>
      <!-- Shortcut periode -->
      <a href="?dari=<?=date('Y-m-d')?>&sampai=<?=date('Y-m-d')?>"
         class="btn btn-sm btn-outline-secondary" style="border-radius:7px;font-size:11px">Hari ini</a>
      <a href="?dari=<?=date('Y-m-01')?>&sampai=<?=date('Y-m-d')?>"
         class="btn btn-sm btn-outline-secondary" style="border-radius:7px;font-size:11px">Bulan ini</a>
      <a href="?dari=<?=date('Y-01-01')?>&sampai=<?=date('Y-12-31')?>"
         class="btn btn-sm btn-outline-secondary" style="border-radius:7px;font-size:11px">Tahun ini</a>
    </div>
  </form>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-3">
  <?php
  $cards = [
    ['Total Penjualan',  'Rp '.number_format($totalRev,0,',','.'),   'fa-sack-dollar',  '#10b981','rgba(16,185,129,.1)'],
    ['Jumlah Transaksi', number_format($totalTrx),                   'fa-receipt',      '#3b82f6','rgba(59,130,246,.1)'],
    ['Estimasi Return',  'Rp '.number_format($estimReturn,0,',','.'), 'fa-rotate-left',  '#f59e0b','rgba(245,158,11,.1)'],
    ['Laba Bersih',      'Rp '.number_format($labaRugi,0,',','.'),   'fa-chart-line',   $labaRugi>=0?'#10b981':'#dc2626',
      $labaRugi>=0?'rgba(16,185,129,.1)':'rgba(220,38,38,.1)'],
  ];
  foreach($cards as [$label,$val,$icon,$color,$bg]):
  ?>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="s-ic mb-2" style="background:<?=$bg?>;color:<?=$color?>">
        <i class="fa-solid <?=$icon?>"></i></div>
      <div style="font-size:15px;font-weight:800;color:#111827"><?=$val?></div>
      <div style="font-size:12px;color:#6b7280;margin-top:2px"><?=$label?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mb-3">
  <!-- Per Metode Pembayaran -->
  <div class="col-lg-4">
    <div class="card-box" style="height:100%">
      <div class="c-head">
        <h5><i class="fa-solid fa-credit-card me-2" style="color:#3b82f6"></i>Per Metode Bayar</h5>
      </div>
      <div class="c-body">
        <?php
        $metodeRows=[];
        while($m=mysqli_fetch_assoc($perMetode)) $metodeRows[]=$m;
        $totalMetode = array_sum(array_column($metodeRows,'s')) ?: 1;
        $mColors=['CASH'=>'#10b981','QRIS'=>'#3b82f6','TRANSFER'=>'#f59e0b'];
        foreach($metodeRows as $m):
          $pct = round(($m['s']/$totalMetode)*100);
        ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1">
            <span style="font-size:13px;font-weight:600"><?=$m['metode_pembayaran']?></span>
            <span style="font-size:12px;color:#6b7280"><?=$m['c']?> trx | Rp <?=number_format($m['s'],0,',','.')?></span>
          </div>
          <div style="background:#f3f4f6;border-radius:4px;height:8px">
            <div style="width:<?=$pct?>%;height:8px;border-radius:4px;background:<?=$mColors[$m['metode_pembayaran']]??'#6b7280'?>"></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($metodeRows)): ?>
        <p class="text-muted text-center" style="font-size:13px">Belum ada data</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Per Kasir -->
  <div class="col-lg-4">
    <div class="card-box" style="height:100%">
      <div class="c-head">
        <h5><i class="fa-solid fa-user-tie me-2" style="color:#8b5cf6"></i>Performa Kasir</h5>
      </div>
      <div class="c-body p-0">
        <table class="table mb-0">
          <thead><tr><th>Kasir</th><th>Trx</th><th>Total</th></tr></thead>
          <tbody>
          <?php
          $hasKasir=false;
          while($k=mysqli_fetch_assoc($perKasir)):
            $hasKasir=true;
          ?>
          <tr>
            <td style="font-size:13px;font-weight:600"><?=htmlspecialchars($k['nama'])?></td>
            <td style="font-size:12px"><?=$k['c']?></td>
            <td style="font-size:12px;font-weight:700">Rp <?=number_format($k['s'],0,',','.')?></td>
          </tr>
          <?php endwhile; ?>
          <?php if(!$hasKasir): ?>
          <tr><td colspan="3" class="text-center text-muted py-3" style="font-size:13px">Belum ada data</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Top Menu -->
  <div class="col-lg-4">
    <div class="card-box" style="height:100%">
      <div class="c-head">
        <h5><i class="fa-solid fa-fire me-2" style="color:#e63946"></i>Top 10 Menu Terjual</h5>
      </div>
      <div class="c-body p-0">
        <table class="table mb-0">
          <thead><tr><th>Menu</th><th>Qty</th><th>Revenue</th></tr></thead>
          <tbody>
          <?php
          $hasMenu=false; $no=1;
          while($tm=mysqli_fetch_assoc($topMenu)):
            $hasMenu=true;
          ?>
          <tr>
            <td style="font-size:12px">
              <span style="color:#9ca3af;margin-right:4px"><?=$no++?></span>
              <?=htmlspecialchars($tm['nama_menu'])?>
            </td>
            <td style="font-size:12px;font-weight:700"><?=$tm['total_qty']?></td>
            <td style="font-size:11px">Rp <?=number_format($tm['total_rev'],0,',','.')?></td>
          </tr>
          <?php endwhile; ?>
          <?php if(!$hasMenu): ?>
          <tr><td colspan="3" class="text-center text-muted py-3" style="font-size:13px">Belum ada data</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Tabel Harian -->
<div class="card-box">
  <div class="c-head">
    <h5><i class="fa-solid fa-calendar-days me-2" style="color:#10b981"></i>Rincian Harian</h5>
    <a href="laporan.php" class="btn btn-sm btn-outline-primary" style="font-size:11px;border-radius:6px">
      <i class="fa-solid fa-file-circle-plus me-1"></i>Buat Laporan
    </a>
  </div>
  <div class="c-body p-0" style="overflow-x:auto">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>Tanggal</th><th>Transaksi</th><th>Pendapatan</th><th>Pending</th><th>Failed</th></tr>
      </thead>
      <tbody>
      <?php
      $hasH=false; $grandTotal=0;
      while($h=mysqli_fetch_assoc($harian)):
        $hasH=true; $grandTotal+=$h['paid'];
      ?>
      <tr>
        <td style="font-weight:600"><?=date('d M Y',strtotime($h['tgl']))?></td>
        <td><?=$h['c']?> transaksi</td>
        <td style="font-weight:700;color:#10b981">Rp <?=number_format($h['paid'],0,',','.')?></td>
        <td style="color:#d97706"><?=$h['pending']?></td>
        <td style="color:#dc2626"><?=$h['failed']>0?number_format($h['failed'],0,',','.'):'-'?></td>
      </tr>
      <?php endwhile; ?>
      <?php if(!$hasH): ?>
      <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada data di periode ini</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
