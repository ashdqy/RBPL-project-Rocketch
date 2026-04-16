<?php
require_once __DIR__ . '/../../middleware/role_spv.php';
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Dashboard SPV';

// ── DATA HARI INI ─────────────────────────────────────────────
$today = date('Y-m-d');
$trxToday   = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) c, IFNULL(SUM(total),0) s FROM transaksi
     WHERE DATE(tanggal)='$today' AND status_pembayaran='PAID'"));
$trxPending = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) c FROM transaksi WHERE DATE(tanggal)='$today' AND status_pembayaran='PENDING'"))['c'];
$totalStok  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM stok"))['c'];
$stokKritis = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM stok WHERE jumlah < 10"))['c'];
$draftLap   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM laporan WHERE status='DRAFT'"))['c'];
$promoAktif = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) c FROM promo WHERE is_active=1
     AND tanggal_mulai<='$today' AND tanggal_selesai>='$today'"))['c'];

// Revenue 7 hari terakhir
$rev7 = mysqli_query($conn,
    "SELECT DATE(tanggal) tgl, IFNULL(SUM(total),0) rev
     FROM transaksi WHERE status_pembayaran='PAID'
     AND DATE(tanggal) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY DATE(tanggal) ORDER BY tgl ASC");
$rev7Data = [];
for ($i=6; $i>=0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $rev7Data[$d] = 0;
}
while ($r = mysqli_fetch_assoc($rev7)) $rev7Data[$r['tgl']] = (float)$r['rev'];

// Transaksi terbaru
$recentTrx = mysqli_query($conn,
    "SELECT t.*, u.nama FROM transaksi t JOIN users u ON t.id_kasir=u.id_user
     ORDER BY t.tanggal DESC LIMIT 7");

// Stok hampir habis
$stokRendah = mysqli_query($conn,
    "SELECT * FROM stok WHERE jumlah < 10 ORDER BY jumlah ASC LIMIT 5");

// Top menu terjual hari ini
$topMenu = mysqli_query($conn,
    "SELECT m.nama_menu, SUM(dt.jumlah) total_jual
     FROM detail_transaksi dt
     JOIN menu m ON dt.id_menu=m.id_menu
     JOIN transaksi t ON dt.id_transaksi=t.id_transaksi
     WHERE DATE(t.tanggal)='$today' AND t.status_pembayaran='PAID'
     GROUP BY dt.id_menu ORDER BY total_jual DESC LIMIT 5");

include __DIR__ . '/../../includes/layout_start.php';
?>

<style>
.mini-chart{height:60px;display:flex;align-items:flex-end;gap:3px}
.bar{background:linear-gradient(180deg,#10b981,#059669);border-radius:4px 4px 0 0;min-width:20px;transition:.3s;cursor:default;position:relative}
.bar:hover::after{content:attr(data-label);position:absolute;bottom:calc(100% + 4px);left:50%;transform:translateX(-50%);background:#111827;color:#fff;font-size:10px;padding:2px 6px;border-radius:5px;white-space:nowrap}
</style>

<!-- Stat Cards -->
<div class="row g-3 mb-3">
  <?php
  $cards = [
    ['Transaksi Hari Ini',    $trxToday['c'],          'fa-receipt',         '#10b981','rgba(16,185,129,.1)'],
    ['Pendapatan Hari Ini',   'Rp '.number_format($trxToday['s'],0,',','.'), 'fa-sack-dollar','#e63946','rgba(230,57,70,.1)'],
    ['Laporan Draft',         $draftLap,               'fa-file-alt',        '#f59e0b','rgba(245,158,11,.1)'],
    ['Promo Aktif',           $promoAktif,             'fa-tags',            '#8b5cf6','rgba(139,92,246,.1)'],
  ];
  foreach($cards as [$label,$val,$icon,$color,$bg]):
  ?>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="s-ic mb-2" style="background:<?=$bg?>;color:<?=$color?>">
        <i class="fa-solid <?=$icon?>"></i></div>
      <div style="font-size:<?=is_string($val)&&strlen($val)>8?'16px':'24px'?>;font-weight:800;color:#111827"><?=$val?></div>
      <div style="font-size:12px;color:#6b7280;margin-top:2px"><?=$label?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php if($stokKritis > 0): ?>
<div class="alert d-flex gap-2 align-items-center mb-3"
     style="border-radius:9px;font-size:13px;padding:10px 14px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca">
  <i class="fa-solid fa-triangle-exclamation"></i>
  <strong><?=$stokKritis?> bahan</strong> stok kritis (di bawah 10)! &nbsp;
  <a href="stok.php" style="color:#dc2626;font-weight:700">Cek Stok →</a>
</div>
<?php endif; ?>

<!-- Revenue Chart 7 hari -->
<div class="card-box mb-3">
  <div class="c-head">
    <h5><i class="fa-solid fa-chart-line me-2" style="color:#10b981"></i>Pendapatan 7 Hari Terakhir</h5>
    <span style="font-size:12px;color:#6b7280">Total: Rp <?= number_format(array_sum($rev7Data),0,',','.') ?></span>
  </div>
  <div class="c-body">
    <?php
    $maxRev = max(array_values($rev7Data)) ?: 1;
    ?>
    <div class="mini-chart" style="height:80px;align-items:flex-end;gap:6px">
      <?php foreach($rev7Data as $tgl => $rev): ?>
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px">
        <div class="bar" style="width:100%;height:<?= max(4, round(($rev/$maxRev)*70)) ?>px"
             data-label="Rp <?= number_format($rev,0,',','.') ?>"></div>
        <div style="font-size:10px;color:#9ca3af"><?= date('d/m',strtotime($tgl)) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Transaksi Terbaru -->
  <div class="col-lg-7">
    <div class="card-box">
      <div class="c-head">
        <h5><i class="fa-solid fa-receipt me-2" style="color:#10b981"></i>Transaksi Terbaru</h5>
        <a href="transaksi.php" class="btn btn-sm btn-outline-secondary" style="font-size:11px">Lihat Semua</a>
      </div>
      <div class="c-body p-0">
        <table class="table table-hover mb-0">
          <thead><tr><th>No Struk</th><th>Kasir</th><th>Total</th><th>Metode</th><th>Status</th></tr></thead>
          <tbody>
          <?php while($t=mysqli_fetch_assoc($recentTrx)):
            $sc=['PAID'=>'sb-success','PENDING'=>'sb-warning','FAILED'=>'sb-danger'][$t['status_pembayaran']]??'';
          ?>
          <tr>
            <td><code style="font-size:11px"><?= htmlspecialchars($t['no_struk']) ?></code></td>
            <td><?= htmlspecialchars($t['nama']) ?></td>
            <td>Rp <?= number_format($t['total'],0,',','.') ?></td>
            <td style="font-size:12px"><?= $t['metode_pembayaran'] ?></td>
            <td><span class="rbadge <?=$sc?>"><?= $t['status_pembayaran'] ?></span></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <!-- Stok Kritis -->
    <div class="card-box mb-3">
      <div class="c-head">
        <h5><i class="fa-solid fa-triangle-exclamation me-2" style="color:#f59e0b"></i>Stok Hampir Habis</h5>
        <a href="stok.php" class="btn btn-sm btn-outline-secondary" style="font-size:11px">Lihat Stok</a>
      </div>
      <div class="c-body p-0">
        <table class="table mb-0">
          <thead><tr><th>Bahan</th><th>Sisa</th></tr></thead>
          <tbody>
          <?php
          $hasStok = false;
          while($s=mysqli_fetch_assoc($stokRendah)):
            $hasStok = true;
          ?>
          <tr>
            <td><?= htmlspecialchars($s['nama_bahan']) ?></td>
            <td><span class="rbadge sb-danger"><?=$s['jumlah']?> <?=$s['satuan']?></span></td>
          </tr>
          <?php endwhile; ?>
          <?php if(!$hasStok): ?>
          <tr><td colspan="2" class="text-center text-muted py-3" style="font-size:13px">
            Semua stok aman ✓</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Top Menu Hari Ini -->
    <div class="card-box">
      <div class="c-head">
        <h5><i class="fa-solid fa-fire me-2" style="color:#e63946"></i>Top Menu Hari Ini</h5>
      </div>
      <div class="c-body p-0">
        <table class="table mb-0">
          <thead><tr><th>Menu</th><th>Terjual</th></tr></thead>
          <tbody>
          <?php
          $hasTop = false;
          while($tm=mysqli_fetch_assoc($topMenu)):
            $hasTop = true;
          ?>
          <tr>
            <td style="font-size:12px"><?= htmlspecialchars($tm['nama_menu']) ?></td>
            <td><span class="rbadge sb-success"><?=$tm['total_jual']?> pcs</span></td>
          </tr>
          <?php endwhile; ?>
          <?php if(!$hasTop): ?>
          <tr><td colspan="2" class="text-center text-muted py-3" style="font-size:13px">
            Belum ada transaksi hari ini</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
