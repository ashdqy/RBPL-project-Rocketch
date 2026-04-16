<?php
require_once __DIR__ . '/../../middleware/role_admin.php';
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Log Sistem';

// ── FILTER ───────────────────────────────────────────────────
$fModul  = $_GET['modul']   ?? '';
$fAksi   = $_GET['aksi']    ?? '';
$fUser   = (int)($_GET['id_user'] ?? 0);
$fDari   = $_GET['dari']    ?? '';
$fSampai = $_GET['sampai']  ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30;
$offset  = ($page - 1) * $perPage;

$where = ['1=1'];
if ($fModul)  $where[] = "sl.modul='"  . mysqli_real_escape_string($conn,$fModul)  . "'";
if ($fAksi)   $where[] = "sl.aksi='"   . mysqli_real_escape_string($conn,$fAksi)   . "'";
if ($fUser)   $where[] = "sl.id_user=$fUser";
if ($fDari)   $where[] = "DATE(sl.tanggal)>='" . mysqli_real_escape_string($conn,$fDari)   . "'";
if ($fSampai) $where[] = "DATE(sl.tanggal)<='" . mysqli_real_escape_string($conn,$fSampai) . "'";
$w = implode(' AND ', $where);

$total   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM system_log sl WHERE $w"))['c'];
$logList = mysqli_query($conn,
    "SELECT sl.*, u.nama, u.role
     FROM system_log sl JOIN users u ON sl.id_user=u.id_user
     WHERE $w ORDER BY sl.tanggal DESC LIMIT $perPage OFFSET $offset");

// Opsi filter
$modOpts  = mysqli_query($conn,"SELECT DISTINCT modul FROM system_log ORDER BY modul");
$aksiOpts = mysqli_query($conn,"SELECT DISTINCT aksi  FROM system_log ORDER BY aksi");
$userOpts = mysqli_query($conn,
    "SELECT DISTINCT u.id_user, u.nama FROM system_log sl
     JOIN users u ON sl.id_user=u.id_user ORDER BY u.nama");

include __DIR__ . '/../../includes/layout_start.php';
?>

<style>
.filter-bar{background:#fff;border-radius:12px;padding:14px 18px;margin-bottom:16px;
            box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.04)}
.filter-bar .form-control,.filter-bar .form-select{
  border-radius:8px;font-size:12px;border:1.5px solid #e5e7eb;padding:5px 10px}
.mtag{display:inline-block;padding:2px 9px;border-radius:12px;font-size:10.5px;font-weight:700;letter-spacing:.2px}
.mt-promo{background:#fdf4ff;color:#9333ea}.mt-laporan{background:#eff6ff;color:#2563eb}
.mt-user{background:#f0fdf4;color:#16a34a}.mt-transaksi{background:#fffbeb;color:#d97706}
.mt-stok{background:#fff7ed;color:#ea580c}.mt-default{background:#f3f4f6;color:#374151}
.atag{display:inline-block;padding:2px 9px;border-radius:12px;font-size:10px;font-weight:700}
.at-create{background:#dcfce7;color:#16a34a}.at-update{background:#fef9c3;color:#b45309}
.at-delete{background:#fef2f2;color:#dc2626}.at-approve{background:#eff6ff;color:#2563eb}
.at-reject{background:#fef2f2;color:#dc2626}.at-other{background:#f3f4f6;color:#6b7280}
</style>

<!-- Filter Bar -->
<div class="filter-bar">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-auto">
      <label style="font-size:11px;font-weight:600;color:#6b7280;display:block">Modul</label>
      <select name="modul" class="form-select" style="min-width:110px">
        <option value="">Semua</option>
        <?php while($m=mysqli_fetch_assoc($modOpts)): ?>
        <option value="<?=$m['modul']?>" <?=$fModul===$m['modul']?'selected':''?>><?=$m['modul']?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-auto">
      <label style="font-size:11px;font-weight:600;color:#6b7280;display:block">Aksi</label>
      <select name="aksi" class="form-select" style="min-width:100px">
        <option value="">Semua</option>
        <?php while($a=mysqli_fetch_assoc($aksiOpts)): ?>
        <option value="<?=$a['aksi']?>" <?=$fAksi===$a['aksi']?'selected':''?>><?=$a['aksi']?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-auto">
      <label style="font-size:11px;font-weight:600;color:#6b7280;display:block">User</label>
      <select name="id_user" class="form-select" style="min-width:130px">
        <option value="">Semua</option>
        <?php while($u=mysqli_fetch_assoc($userOpts)): ?>
        <option value="<?=$u['id_user']?>" <?=$fUser==$u['id_user']?'selected':''?>><?=htmlspecialchars($u['nama'])?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-auto">
      <label style="font-size:11px;font-weight:600;color:#6b7280;display:block">Dari</label>
      <input type="date" name="dari" class="form-control" value="<?=htmlspecialchars($fDari)?>">
    </div>
    <div class="col-auto">
      <label style="font-size:11px;font-weight:600;color:#6b7280;display:block">Sampai</label>
      <input type="date" name="sampai" class="form-control" value="<?=htmlspecialchars($fSampai)?>">
    </div>
    <div class="col-auto d-flex gap-2">
      <button type="submit" class="btn btn-sm"
        style="background:#111827;color:#fff;border-radius:7px;font-size:12px;padding:6px 14px;border:none">
        <i class="fa-solid fa-filter me-1"></i>Filter
      </button>
      <a href="system_log.php" class="btn btn-sm btn-outline-secondary"
        style="border-radius:7px;font-size:12px">Reset</a>
    </div>
  </form>
</div>

<!-- Tabel Log -->
<div class="card-box">
  <div class="c-head">
    <h5><i class="fa-solid fa-shield-halved me-2" style="color:#e63946"></i>Log Aktivitas Sistem</h5>
    <span class="text-muted" style="font-size:12px"><?= number_format($total) ?> entri</span>
  </div>
  <div class="c-body p-0" style="overflow-x:auto">
    <table class="table mb-0" style="font-size:12.5px">
      <thead>
        <tr>
          <th>#</th><th>Waktu</th><th>User</th><th>Role</th>
          <th>Modul</th><th>Aksi</th><th>Deskripsi</th><th>IP</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $no = $offset + 1; $hasRow = false;
      while ($log = mysqli_fetch_assoc($logList)):
        $hasRow = true;
        $mc = match(strtoupper($log['modul'])) {
          'PROMO'     => 'mt-promo',
          'LAPORAN'   => 'mt-laporan',
          'USER'      => 'mt-user',
          'TRANSAKSI' => 'mt-transaksi',
          'STOK'      => 'mt-stok',
          default     => 'mt-default'
        };
        $ac = match(strtoupper($log['aksi'])) {
          'CREATE'  => 'at-create',
          'UPDATE'  => 'at-update',
          'DELETE'  => 'at-delete',
          'APPROVE' => 'at-approve',
          'REJECT'  => 'at-reject',
          default   => 'at-other'
        };
        $rmap = ['SUPER ADMIN'=>'rb-sa','SPV'=>'rb-spv','KASIR'=>'rb-ka','TRAINING'=>'rb-tr','COOKER'=>'rb-co'];
        $rc = $rmap[$log['role']] ?? '';
      ?>
      <tr>
        <td style="color:#9ca3af;font-size:12px"><?= $no++ ?></td>
        <td style="white-space:nowrap">
          <div style="font-size:12px;color:#374151"><?= date('d/m/Y',strtotime($log['tanggal'])) ?></div>
          <div style="font-size:11px;color:#9ca3af"><?= date('H:i:s',strtotime($log['tanggal'])) ?></div>
        </td>
        <td style="font-weight:600;font-size:13px"><?= htmlspecialchars($log['nama']) ?></td>
        <td><span class="rbadge <?= $rc ?>" style="font-size:9.5px"><?= $log['role'] ?></span></td>
        <td><span class="mtag <?= $mc ?>"><?= $log['modul'] ?></span></td>
        <td><span class="atag <?= $ac ?>"><?= $log['aksi'] ?></span></td>
        <td style="color:#374151;max-width:280px;font-size:12px">
          <?= htmlspecialchars($log['deskripsi']) ?>
        </td>
        <td style="font-size:11px;color:#9ca3af;font-family:monospace">
          <?= htmlspecialchars($log['ip_address']??'—') ?>
        </td>
      </tr>
      <?php endwhile; ?>
      <?php if (!$hasRow): ?>
      <tr><td colspan="8" class="text-center py-4 text-muted" style="font-size:13px">
        <i class="fa-solid fa-magnifying-glass me-2"></i>Tidak ada log ditemukan
      </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php
  $totalPage = ceil($total / $perPage);
  if ($totalPage > 1):
    $qArr = $_GET; unset($qArr['page']);
    $baseQ = http_build_query($qArr);
  ?>
  <div style="padding:10px 18px;border-top:1px solid #f3f4f6">
    <nav><ul class="pagination pagination-sm mb-0 flex-wrap gap-1">
      <?php for ($i=1; $i<=$totalPage; $i++): ?>
      <li class="page-item <?= $i==$page?'active':'' ?>">
        <a class="page-link" href="?<?= $baseQ ?>&page=<?= $i ?>"
           style="border-radius:6px;font-size:12px;<?= $i==$page?'background:#111827;border-color:#111827;color:#fff':'' ?>">
          <?= $i ?>
        </a>
      </li>
      <?php endfor; ?>
    </ul></nav>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
