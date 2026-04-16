<?php
require_once __DIR__ . '/../../middleware/role_spv.php';
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Riwayat Transaksi';

// ── FILTER ───────────────────────────────────────────────────
$fDari    = $_GET['dari']    ?? date('Y-m-01');
$fSampai  = $_GET['sampai']  ?? date('Y-m-d');
$fKasir   = (int)($_GET['kasir'] ?? 0);
$fMetode  = $_GET['metode']  ?? '';
$fStatus  = $_GET['status']  ?? '';
$page     = max(1,(int)($_GET['page'] ?? 1));
$perPage  = 20;
$offset   = ($page-1)*$perPage;

$where = ["DATE(t.tanggal) BETWEEN '$fDari' AND '$fSampai'"];
if ($fKasir)  $where[] = "t.id_kasir=$fKasir";
if ($fMetode) $where[] = "t.metode_pembayaran='" . mysqli_real_escape_string($conn,$fMetode) . "'";
if ($fStatus) $where[] = "t.status_pembayaran='" . mysqli_real_escape_string($conn,$fStatus) . "'";
$w = implode(' AND ', $where);

$total   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM transaksi t WHERE $w"))['c'];
$summary = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT IFNULL(SUM(total),0) total_rev, COUNT(*) total_trx
     FROM transaksi t WHERE $w AND status_pembayaran='PAID'"));
$trxList = mysqli_query($conn,
    "SELECT t.*, u.nama kasir FROM transaksi t
     JOIN users u ON t.id_kasir=u.id_user
     WHERE $w ORDER BY t.tanggal DESC
     LIMIT $perPage OFFSET $offset");

$kasirList = mysqli_query($conn,"SELECT id_user,nama FROM users WHERE role='KASIR' AND is_active=1");

include __DIR__ . '/../../includes/layout_start.php';
?>

<style>
.filter-bar{background:#fff;border-radius:12px;padding:14px 18px;margin-bottom:16px;
            box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.04)}
.form-control,.form-select{border-radius:8px;font-size:12px;border:1.5px solid #e5e7eb;padding:5px 10px}
</style>

<!-- Filter -->
<div class="filter-bar">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-auto">
      <label style="font-size:11px;font-weight:600;color:#6b7280;display:block">Dari</label>
      <input type="date" name="dari" class="form-control" value="<?=$fDari?>">
    </div>
    <div class="col-auto">
      <label style="font-size:11px;font-weight:600;color:#6b7280;display:block">Sampai</label>
      <input type="date" name="sampai" class="form-control" value="<?=$fSampai?>">
    </div>
    <div class="col-auto">
      <label style="font-size:11px;font-weight:600;color:#6b7280;display:block">Kasir</label>
      <select name="kasir" class="form-select" style="min-width:120px">
        <option value="">Semua Kasir</option>
        <?php while($k=mysqli_fetch_assoc($kasirList)): ?>
        <option value="<?=$k['id_user']?>" <?=$fKasir==$k['id_user']?'selected':''?>>
          <?=htmlspecialchars($k['nama'])?>
        </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-auto">
      <label style="font-size:11px;font-weight:600;color:#6b7280;display:block">Metode</label>
      <select name="metode" class="form-select">
        <option value="">Semua</option>
        <option value="CASH"     <?=$fMetode==='CASH'    ?'selected':''?>>Cash</option>
        <option value="QRIS"     <?=$fMetode==='QRIS'    ?'selected':''?>>QRIS</option>
        <option value="TRANSFER" <?=$fMetode==='TRANSFER'?'selected':''?>>Transfer</option>
      </select>
    </div>
    <div class="col-auto">
      <label style="font-size:11px;font-weight:600;color:#6b7280;display:block">Status</label>
      <select name="status" class="form-select">
        <option value="">Semua</option>
        <option value="PAID"    <?=$fStatus==='PAID'   ?'selected':''?>>Paid</option>
        <option value="PENDING" <?=$fStatus==='PENDING'?'selected':''?>>Pending</option>
        <option value="FAILED"  <?=$fStatus==='FAILED' ?'selected':''?>>Failed</option>
      </select>
    </div>
    <div class="col-auto d-flex gap-2">
      <button type="submit" class="btn btn-sm"
        style="background:#111827;color:#fff;border-radius:7px;font-size:12px;padding:6px 14px;border:none">
        <i class="fa-solid fa-filter me-1"></i>Filter
      </button>
      <a href="transaksi.php" class="btn btn-sm btn-outline-secondary"
        style="border-radius:7px;font-size:12px">Reset</a>
    </div>
  </form>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-3">
  <div class="col-sm-4">
    <div class="stat-card d-flex align-items-center gap-3" style="padding:14px 18px">
      <div class="s-ic" style="background:rgba(16,185,129,.1);color:#10b981;width:42px;height:42px;font-size:17px">
        <i class="fa-solid fa-receipt"></i></div>
      <div>
        <div style="font-size:22px;font-weight:800;color:#111827"><?=number_format($total)?></div>
        <div style="font-size:12px;color:#6b7280">Total Transaksi</div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card d-flex align-items-center gap-3" style="padding:14px 18px">
      <div class="s-ic" style="background:rgba(230,57,70,.1);color:#e63946;width:42px;height:42px;font-size:17px">
        <i class="fa-solid fa-sack-dollar"></i></div>
      <div>
        <div style="font-size:16px;font-weight:800;color:#111827">
          Rp <?=number_format($summary['total_rev'],0,',','.')?></div>
        <div style="font-size:12px;color:#6b7280">Total Pendapatan (PAID)</div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card d-flex align-items-center gap-3" style="padding:14px 18px">
      <div class="s-ic" style="background:rgba(139,92,246,.1);color:#8b5cf6;width:42px;height:42px;font-size:17px">
        <i class="fa-solid fa-chart-simple"></i></div>
      <div>
        <div style="font-size:16px;font-weight:800;color:#111827">
          Rp <?= $summary['total_trx']>0 ? number_format($summary['total_rev']/$summary['total_trx'],0,',','.') : '0' ?>
        </div>
        <div style="font-size:12px;color:#6b7280">Rata-rata per Transaksi</div>
      </div>
    </div>
  </div>
</div>

<!-- Tabel -->
<div class="card-box">
  <div class="c-head">
    <h5><i class="fa-solid fa-clock-rotate-left me-2" style="color:#10b981"></i>
      Riwayat Transaksi
      <span style="font-size:12px;font-weight:400;color:#6b7280;margin-left:6px">
        <?=date('d/m/Y',strtotime($fDari))?> — <?=date('d/m/Y',strtotime($fSampai))?>
      </span>
    </h5>
    <span class="text-muted" style="font-size:12px"><?=number_format($total)?> transaksi</span>
  </div>
  <div class="c-body p-0" style="overflow-x:auto">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>#</th><th>No Struk</th><th>Kasir</th><th>Tanggal</th>
          <th>Total</th><th>Metode</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $no = $offset+1; $hasRow=false;
      while($t=mysqli_fetch_assoc($trxList)):
        $hasRow=true;
        $sc=['PAID'=>'sb-success','PENDING'=>'sb-warning','FAILED'=>'sb-danger'][$t['status_pembayaran']]??'';
        $mc=['CASH'=>'sb-info','QRIS'=>'sb-success','TRANSFER'=>'sb-warning'][$t['metode_pembayaran']]??'';
      ?>
      <tr>
        <td style="color:#9ca3af;font-size:12px"><?=$no++?></td>
        <td><code style="font-size:11px"><?=htmlspecialchars($t['no_struk'])?></code></td>
        <td style="font-size:13px"><?=htmlspecialchars($t['kasir'])?></td>
        <td style="font-size:12px;color:#6b7280">
          <?=date('d/m/Y',strtotime($t['tanggal']))?>
          <br><span style="font-size:11px"><?=date('H:i',strtotime($t['tanggal']))?></span>
        </td>
        <td style="font-weight:700">Rp <?=number_format($t['total'],0,',','.')?></td>
        <td><span class="rbadge <?=$mc?>"><?=$t['metode_pembayaran']?></span></td>
        <td><span class="rbadge <?=$sc?>"><?=$t['status_pembayaran']?></span></td>
        <td>
          <a href="/RC/rocket_system/rocket_system/view/admin/transaksi/detail.php?id=<?=$t['id_transaksi']?>"
             class="btn btn-sm btn-outline-secondary" style="font-size:10px;border-radius:5px">
             <i class="fa-solid fa-eye"></i></a>
        </td>
      </tr>
      <?php endwhile; ?>
      <?php if(!$hasRow): ?>
      <tr><td colspan="8" class="text-center py-4 text-muted" style="font-size:13px">
        Tidak ada transaksi di periode ini
      </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php
  $totalPage = ceil($total/$perPage);
  if($totalPage>1):
    $qArr=$_GET; unset($qArr['page']); $bq=http_build_query($qArr);
  ?>
  <div style="padding:10px 18px;border-top:1px solid #f3f4f6">
    <nav><ul class="pagination pagination-sm mb-0 flex-wrap gap-1">
      <?php for($i=1;$i<=$totalPage;$i++): ?>
      <li class="page-item <?=$i==$page?'active':''?>">
        <a class="page-link" href="?<?=$bq?>&page=<?=$i?>"
           style="border-radius:6px;font-size:12px;<?=$i==$page?'background:#111827;border-color:#111827;color:#fff':''?>">
          <?=$i?>
        </a>
      </li>
      <?php endfor; ?>
    </ul></nav>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
