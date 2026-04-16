<?php
require_once __DIR__ . '/../../../middleware/role_admin.php';
require_once __DIR__ . '/../../../config/database.php';
$pageTitle = 'Tambah User';
$error = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $nama     = trim($_POST['nama']??'');
    $username = trim($_POST['username']??'');
    $password = trim($_POST['password']??'');
    $role     = $_POST['role']??'';

    if(!$nama||!$username||!$password||!$role) {
        $error = 'Semua field wajib diisi.';
    } else {
        $chk = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id_user FROM users WHERE username='".mysqli_real_escape_string($conn,$username)."'"));
        if($chk) { $error = 'Username sudah digunakan.'; }
        else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($conn,"INSERT INTO users (nama,username,password,role,is_active) VALUES(?,?,?,?,1)");
            mysqli_stmt_bind_param($stmt,'ssss',$nama,$username,$hash,$role);
            mysqli_stmt_execute($stmt);
            header("Location: index.php?msg=".urlencode("User $nama berhasil ditambahkan.")); exit;
        }
    }
}

include __DIR__ . '/../../../includes/layout_start.php';
?>

<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card-box">
  <div class="c-head">
    <h5><i class="fa-solid fa-user-plus me-2" style="color:#3b82f6"></i>Tambah User Baru</h5>
    <a href="index.php" class="btn btn-sm btn-outline-secondary" style="font-size:12px;border-radius:7px">← Kembali</a>
  </div>
  <div class="c-body">
    <?php if($error): ?>
    <div class="alert" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:9px;font-size:13px;padding:10px 14px">
      <i class="fa-solid fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="row g-3">
        <div class="col-sm-6">
          <label class="form-label" style="font-size:13px;font-weight:600">Nama Lengkap</label>
          <input type="text" name="nama" class="form-control" style="border-radius:8px" required value="<?= htmlspecialchars($_POST['nama']??'') ?>">
        </div>
        <div class="col-sm-6">
          <label class="form-label" style="font-size:13px;font-weight:600">Username</label>
          <input type="text" name="username" class="form-control" style="border-radius:8px" required value="<?= htmlspecialchars($_POST['username']??'') ?>">
        </div>
        <div class="col-sm-6">
          <label class="form-label" style="font-size:13px;font-weight:600">Password</label>
          <input type="password" name="password" class="form-control" style="border-radius:8px" required minlength="6" placeholder="Min. 6 karakter">
        </div>
        <div class="col-sm-6">
          <label class="form-label" style="font-size:13px;font-weight:600">Role</label>
          <select name="role" class="form-select" style="border-radius:8px" required>
            <option value="">-- Pilih Role --</option>
            <?php foreach(['SPV','KASIR','TRAINING','COOKER'] as $r): ?>
            <option value="<?=$r?>" <?= ($_POST['role']??'')===$r?'selected':'' ?>><?=$r?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 d-flex gap-2 justify-content-end mt-2">
          <a href="index.php" class="btn btn-outline-secondary" style="border-radius:8px;font-size:13px">Batal</a>
          <button type="submit" class="btn" style="background:#e63946;color:#fff;border-radius:8px;font-size:13px">
            <i class="fa-solid fa-save me-1"></i>Simpan User
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
</div>
</div>

<?php include __DIR__ . '/../../../includes/layout_end.php'; ?>
