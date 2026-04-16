<?php
require_once __DIR__ . '/../../middleware/role_admin.php';
require_once __DIR__ . '/../../config/database.php';

$pageTitle = 'Dashboard Admin';

// Stats
$totalUsers    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM users WHERE is_active=1"))['c'];
$totalTrx      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM transaksi"))['c'];
$totalStok     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM stok"))['c'];
$totalLaporan  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM laporan"))['c'];
$todayRevenue  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT IFNULL(SUM(total),0) s FROM transaksi WHERE DATE(tanggal)=CURDATE() AND status_pembayaran='PAID'"))['s'];
$pendingLap    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM laporan WHERE status='DRAFT'"))['c'];

// Recent users
$recentUsers   = mysqli_query($conn,"SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
// Recent transaksi
$recentTrx     = mysqli_query($conn,"SELECT t.*, u.nama FROM transaksi t JOIN users u ON t.id_kasir=u.id_user ORDER BY t.tanggal DESC LIMIT 5");

include __DIR__ . '/../../includes/layout_start.php';
?>

 <!-- //$_SERVER['PHP_SELF']  
 // $depth 
 // $root  -->

 

<div class="row g-3 mb-4">
  <!-- Stat Cards -->
  <?php
  $stats=[
    ['Pengguna Aktif',      $totalUsers,   'fa-users',           '#3b82f6','rgba(59,130,246,.1)'],
    ['Total Transaksi',     $totalTrx,     'fa-receipt',         '#10b981','rgba(16,185,129,.1)'],
    ['Item Stok',           $totalStok,    'fa-boxes-stacked',   '#f59e0b','rgba(245,158,11,.1)'],
    ['Laporan Draft',       $pendingLap,   'fa-file-alt',        '#e63946','rgba(230,57,70,.1)'],
  ];
  foreach($stats as [$label,$val,$icon,$color,$bg]):
  ?>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="s-ic" style="background:<?=$bg?>;color:<?=$color?>"><i class="fa-solid <?=$icon?>"></i></div>
      </div>
      <div style="font-size:26px;font-weight:800;color:#111827"><?= number_format($val) ?></div>
      <div style="font-size:12.5px;color:#6b7280;margin-top:3px"><?= $label ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="stat-card d-flex align-items-center gap-3">
      <div class="s-ic" style="background:rgba(230,57,70,.1);color:#e63946;width:52px;height:52px;font-size:22px"><i class="fa-solid fa-sack-dollar"></i></div>
      <div>
        <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px">Pendapatan Hari Ini</div>
        <div style="font-size:28px;font-weight:800;color:#111827">Rp <?= number_format($todayRevenue,0,',','.') ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Recent Users -->
  <div class="col-lg-6">
    <div class="card-box">
      <div class="c-head">
        <h5><i class="fa-solid fa-users me-2" style="color:#3b82f6"></i>User Terbaru</h5>
        <a href="users/index.php" class="btn btn-sm btn-outline-secondary" style="font-size:11px">Lihat Semua</a>
      </div>
      <div class="c-body p-0">
        <table class="table table-hover mb-0">
          <thead><tr><th>Nama</th><th>Username</th><th>Role</th></tr></thead>
          <tbody>
          <?php while($u=mysqli_fetch_assoc($recentUsers)): 
            $rmap=['SUPER ADMIN'=>'rb-sa','SPV'=>'rb-spv','KASIR'=>'rb-ka','TRAINING'=>'rb-tr','COOKER'=>'rb-co'];
            $rc=$rmap[$u['role']]??'';
          ?>
          <tr>
            <td><?= htmlspecialchars($u['nama']) ?></td>
            <td><code style="font-size:12px"><?= htmlspecialchars($u['username']) ?></code></td>
            <td><span class="rbadge <?=$rc?>"><?= $u['role'] ?></span></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Recent Transactions -->
  <div class="col-lg-6">
    <div class="card-box">
      <div class="c-head">
        <h5><i class="fa-solid fa-receipt me-2" style="color:#10b981"></i>Transaksi Terakhir</h5>
        <a href="transaksi/index.php" class="btn btn-sm btn-outline-secondary" style="font-size:11px">Lihat Semua</a>
      </div>
      <div class="c-body p-0">
        <table class="table table-hover mb-0">
          <thead><tr><th>No Struk</th><th>Kasir</th><th>Total</th><th>Status</th></tr></thead>
          <tbody>
          <?php while($t=mysqli_fetch_assoc($recentTrx)): 
            $sc=['PAID'=>'sb-success','PENDING'=>'sb-warning','FAILED'=>'sb-danger'][$t['status_pembayaran']]??'';
          ?>
          <tr>
            <td><code style="font-size:11px"><?= htmlspecialchars($t['no_struk']) ?></code></td>
            <td><?= htmlspecialchars($t['nama']) ?></td>
            <td>Rp <?= number_format($t['total'],0,',','.') ?></td>
            <td><span class="rbadge <?=$sc?>"><?= $t['status_pembayaran'] ?></span></td>
          </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
