<?php
require_once __DIR__ . '/../../../middleware/role_admin.php';
require_once __DIR__ . '/../../../config/database.php';
$pageTitle = 'Edit User';
$error = '';

$id = (int)($_GET['id']??0);
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id_user=$id"));
if(!$user) { header("Location: index.php"); exit; }

if($_SERVER['REQUEST_METHOD']==='POST') {
    $nama     = trim($_POST['nama']??'');
    $role     = $_POST['role']??'';
    $is_active= (int)($_POST['is_active']??1);
    $newpass  = trim($_POST['password']??'');

    if(!$nama||!$role) { $error = 'Nama dan role wajib diisi.'; }
    else {
        $sql = "UPDATE users SET nama=?, role=?, is_active=?, updated_at=NOW()";
        $types = 'ssi';
        $params = [$nama,$role,$is_active];

        if($user['role']==='SUPER ADMIN') { $role='SUPER ADMIN'; $params[1]='SUPER ADMIN'; }

        if($newpass) {
            $hash = password_hash($newpass, PASSWORD_BCRYPT);
            $sql .= ", password=?"; $types .= 's'; $params[] = $hash;
        }
        $sql .= " WHERE id_user=?"; $types .= 'i'; $params[] = $id;
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        header("Location: index.php?msg=".urlencode("User berhasil diperbarui.")); exit;
    }
}

include __DIR__ . '/../../../includes/layout_start.php';
?>

<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card-box">
  <div class="c-head">
    <h5><i class="fa-solid fa-user-pen me-2" style="color:#3b82f6"></i>Edit User</h5>
    <a href="index.php" class="btn btn-sm btn-outline-secondary" style="font-size:12px;border-radius:7px">← Kembali</a>
  </div>
  <div class="c-body">
    <?php if($error): ?>
    <div class="alert" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:9px;font-size:13px;padding:10px 14px">
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="row g-3">
        <div class="col-sm-6">
          <label class="form-label" style="font-size:13px;font-weight:600">Nama Lengkap</label>
          <input type="text" name="nama" class="form-control" style="border-radius:8px" required value="<?= htmlspecialchars($_POST['nama']??$user['nama']) ?>">
        </div>
        <div class="col-sm-6">
          <label class="form-label" style="font-size:13px;font-weight:600">Username</label>
          <input type="text" class="form-control" style="border-radius:8px;background:#f9fafb" value="<?= htmlspecialchars($user['username']) ?>" disabled>
          <small class="text-muted" style="font-size:11px">Username tidak dapat diubah</small>
        </div>
        <div class="col-sm-6">
          <label class="form-label" style="font-size:13px;font-weight:600">Role</label>
          <?php if($user['role']==='SUPER ADMIN'): ?>
          <input type="text" class="form-control" value="SUPER ADMIN" disabled style="border-radius:8px;background:#f9fafb">
          <input type="hidden" name="role" value="SUPER ADMIN">
          <?php else: ?>
          <select name="role" class="form-select" style="border-radius:8px">
            <?php foreach(['SPV','KASIR','TRAINING','COOKER'] as $r): ?>
            <option value="<?=$r?>" <?= ($_POST['role']??$user['role'])===$r?'selected':'' ?>><?=$r?></option>
            <?php endforeach; ?>
          </select>
          <?php endif; ?>
        </div>
        <div class="col-sm-6">
          <label class="form-label" style="font-size:13px;font-weight:600">Status</label>
          <select name="is_active" class="form-select" style="border-radius:8px">
            <option value="1" <?= ($_POST['is_active']??$user['is_active'])?'selected':'' ?>>Aktif</option>
            <option value="0" <?= !($_POST['is_active']??$user['is_active'])?'selected':'' ?>>Nonaktif</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label" style="font-size:13px;font-weight:600">Reset Password <span class="text-muted">(kosongkan jika tidak ingin ubah)</span></label>
          <input type="password" name="password" class="form-control" style="border-radius:8px" placeholder="Password baru..." minlength="6">
        </div>
        <div class="col-12 d-flex gap-2 justify-content-end mt-2">
          <a href="index.php" class="btn btn-outline-secondary" style="border-radius:8px;font-size:13px">Batal</a>
          <button type="submit" class="btn" style="background:#e63946;color:#fff;border-radius:8px;font-size:13px">
            <i class="fa-solid fa-save me-1"></i>Simpan Perubahan
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
</div>
</div>

<?php include __DIR__ . '/../../../includes/layout_end.php'; ?>
