<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['id_user'])) {
    header("Location: dashboard.php"); exit;
}

include 'config/database.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username=? AND is_active=1");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $res  = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];
            header("Location: dashboard.php"); exit;
        } else {
            $error = 'Username atau password salah, atau akun tidak aktif.';
        }
    } else {
        $error = 'Semua field wajib diisi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — Rocket Chicken</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body{background:linear-gradient(135deg,#111827 0%,#1f2937 50%,#111827 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif}
.login-card{background:#fff;border-radius:18px;padding:40px 36px;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.brand-box{text-align:center;margin-bottom:28px}
.brand-icon{width:60px;height:60px;border-radius:15px;background:linear-gradient(135deg,#e63946,#c1121f);display:inline-flex;align-items:center;justify-content:center;font-size:26px;color:#fff;margin-bottom:12px;box-shadow:0 8px 20px rgba(230,57,70,.35)}
.brand-title{font-size:20px;font-weight:800;color:#111827}
.brand-sub{color:#6b7280;font-size:13px}
.form-control{border-radius:9px;border:1.5px solid #e5e7eb;padding:11px 14px;font-size:14px;transition:.2s}
.form-control:focus{border-color:#e63946;box-shadow:0 0 0 3px rgba(230,57,70,.12)}
.form-label{font-size:13px;font-weight:600;color:#374151;margin-bottom:5px}
.btn-login{background:linear-gradient(135deg,#e63946,#c1121f);border:none;border-radius:9px;padding:11px;font-size:14px;font-weight:600;color:#fff;width:100%;box-shadow:0 4px 12px rgba(230,57,70,.3);transition:.2s}
.btn-login:hover{transform:translateY(-1px);box-shadow:0 6px 18px rgba(230,57,70,.4);color:#fff}
.input-icon{position:relative}
.input-icon i{position:absolute;top:50%;left:12px;transform:translateY(-50%);color:#9ca3af;font-size:13px}
.input-icon .form-control{padding-left:36px}
</style>
</head>
<body>
<div class="login-card">
  <div class="brand-box">
    <div class="brand-icon"><i class="fa-solid fa-fire-flame-curved"></i></div>
    <div class="brand-title">Rocket Chicken</div>
    <div class="brand-sub">Sistem Manajemen Internal</div>
  </div>

  <?php if($error): ?>
  <div class="alert alert-danger d-flex align-items-center gap-2 py-2" style="font-size:13px;border-radius:9px">
    <i class="fa-solid fa-circle-exclamation"></i><?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Username</label>
      <div class="input-icon">
        <i class="fa-solid fa-user"></i>
        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required value="<?= htmlspecialchars($_POST['username']??'') ?>">
      </div>
    </div>
    <div class="mb-4">
      <label class="form-label">Password</label>
      <div class="input-icon">
        <i class="fa-solid fa-lock"></i>
        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
      </div>
    </div>
    <button type="submit" class="btn btn-login"><i class="fa-solid fa-right-to-bracket me-2"></i>Masuk</button>
  </form>
  <p class="text-center text-muted mt-3 mb-0" style="font-size:12px">© <?= date('Y') ?> Rocket Chicken — Sistem Internal</p>
</div>
</body></html>
