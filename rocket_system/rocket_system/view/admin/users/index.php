<?php
require_once __DIR__ . '/../../../middleware/role_admin.php';
require_once __DIR__ . '/../../../config/database.php';
$pageTitle = 'Kelola User';

// Handle actions
$msg = '';
if(isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $u = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id_user=$id"));
    if($u && $u['role'] !== 'SUPER ADMIN') {
        $newStatus = $u['is_active'] ? 0 : 1;
        mysqli_query($conn,"UPDATE users SET is_active=$newStatus, updated_at=NOW() WHERE id_user=$id");
        $msg = $newStatus ? 'Akun berhasil diaktifkan.' : 'Akun berhasil dinonaktifkan.';
    }
    header("Location: index.php?msg=".urlencode($msg)); exit;
}
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $u = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id_user=$id"));
    if($u && $u['role'] !== 'SUPER ADMIN' && $id !== (int)$_SESSION['id_user']) {
        mysqli_query($conn,"DELETE FROM users WHERE id_user=$id");
        $msg = 'User berhasil dihapus.';
    } else { $msg = 'User tidak dapat dihapus.'; }
    header("Location: index.php?msg=".urlencode($msg)); exit;
}
if(isset($_GET['msg'])) $msg = $_GET['msg'];

$search = trim($_GET['q'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$sql = "SELECT * FROM users WHERE 1";
if($search) $sql .= " AND (nama LIKE '%".mysqli_real_escape_string($conn,$search)."%' OR username LIKE '%".mysqli_real_escape_string($conn,$search)."%')";
if($roleFilter) $sql .= " AND role='".mysqli_real_escape_string($conn,$roleFilter)."'";
$sql .= " ORDER BY created_at DESC";
$users = mysqli_query($conn, $sql);

include __DIR__ . '/../../../includes/layout_start.php';
?>

<?php if($msg): ?>
<div class="alert sb-success alert-auto d-flex align-items-center gap-2 mb-3" style="border-radius:9px;font-size:13px;padding:10px 14px">
  <i class="fa-solid fa-check-circle"></i><?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<div class="card-box">
  <div class="c-head">
    <h5><i class="fa-solid fa-users me-2" style="color:#3b82f6"></i>Daftar User</h5>
    <a href="create.php" class="btn btn-sm" style="background:#e63946;color:#fff;font-size:12px;border-radius:7px">
      <i class="fa-solid fa-plus me-1"></i>Tambah User
    </a>
  </div>
  <div class="c-body">
    <!-- Filter -->
    <form method="GET" class="row g-2 mb-3">
      <div class="col-sm-6">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama / username..." value="<?= htmlspecialchars($search) ?>" style="border-radius:7px">
      </div>
      <div class="col-sm-4">
        <select name="role" class="form-select form-select-sm" style="border-radius:7px">
          <option value="">Semua Role</option>
          <?php foreach(['SUPER ADMIN','SPV','KASIR','TRAINING','COOKER'] as $r): ?>
          <option value="<?=$r?>" <?= $roleFilter===$r?'selected':'' ?>><?=$r?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-2">
        <button type="submit" class="btn btn-sm btn-secondary w-100" style="border-radius:7px;font-size:12px">Filter</button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>#</th><th>Nama</th><th>Username</th><th>Role</th><th>Status</th><th>Dibuat</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php $no=1; while($u=mysqli_fetch_assoc($users)):
          $rmap=['SUPER ADMIN'=>'rb-sa','SPV'=>'rb-spv','KASIR'=>'rb-ka','TRAINING'=>'rb-tr','COOKER'=>'rb-co'];
          $rc=$rmap[$u['role']]??'';
        ?>
        <tr>
          <td><?=$no++?></td>
          <td><strong><?= htmlspecialchars($u['nama']) ?></strong></td>
          <td><code style="font-size:12px"><?= htmlspecialchars($u['username']) ?></code></td>
          <td><span class="rbadge <?=$rc?>"><?= $u['role'] ?></span></td>
          <td>
            <?php if($u['is_active']): ?>
              <span class="rbadge" style="background:#f0fdf4;color:#16a34a">Aktif</span>
            <?php else: ?>
              <span class="rbadge" style="background:#fef2f2;color:#dc2626">Nonaktif</span>
            <?php endif; ?>
          </td>
          <td style="color:#9ca3af;font-size:12px"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td>
            <div class="d-flex gap-1">
              <a href="edit.php?id=<?=$u['id_user']?>" class="btn btn-sm btn-outline-primary" style="font-size:11px;border-radius:6px">Edit</a>
              <?php if($u['role']!=='SUPER ADMIN'): ?>
              <a href="index.php?toggle=<?=$u['id_user']?>" class="btn btn-sm <?= $u['is_active']?'btn-outline-warning':'btn-outline-success' ?>" style="font-size:11px;border-radius:6px" onclick="return confirm('Yakin?')">
                <?= $u['is_active']?'Nonaktifkan':'Aktifkan' ?>
              </a>
              <a href="index.php?delete=<?=$u['id_user']?>" class="btn btn-sm btn-outline-danger" style="font-size:11px;border-radius:6px" onclick="return confirm('Hapus user ini?')">Hapus</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../../includes/layout_end.php'; ?>
