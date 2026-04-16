<?php
require_once __DIR__ . '/../../middleware/role_admin.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/log_helper.php';
$pageTitle = 'Kelola Promo';
$msg = $msgType = '';
$id_admin = (int)$_SESSION['id_user'];

// ── HAPUS ────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT nama_promo FROM promo WHERE id_promo=$did"));
    if ($row) {
        mysqli_query($conn,"DELETE FROM promo WHERE id_promo=$did");
        log_activity($conn, $id_admin, 'PROMO', 'DELETE', "Hapus promo: {$row['nama_promo']} (ID: $did)");
        $msg = "Promo <strong>{$row['nama_promo']}</strong> dihapus."; $msgType = 'danger';
    }
}

// ── TOGGLE AKTIF ─────────────────────────────────────────────
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT nama_promo,is_active FROM promo WHERE id_promo=$tid"));
    if ($row) {
        $nv = $row['is_active'] ? 0 : 1;
        mysqli_query($conn,"UPDATE promo SET is_active=$nv, updated_at=NOW() WHERE id_promo=$tid");
        log_activity($conn, $id_admin, 'PROMO', 'UPDATE',
            "Toggle promo '{$row['nama_promo']}' → ".($nv?'AKTIF':'NONAKTIF')." (ID: $tid)");
        $msg = "Promo <strong>{$row['nama_promo']}</strong> ".($nv?'diaktifkan':'dinonaktifkan').".";
        $msgType = $nv ? 'success' : 'warning';
    }
}

// ── SAVE ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_edit     = (int)($_POST['id_promo'] ?? 0);
    $nama        = mysqli_real_escape_string($conn, trim($_POST['nama_promo']    ?? ''));
    $jenis       = mysqli_real_escape_string($conn, $_POST['jenis']              ?? 'PERSEN');
    $nilai       = (float)($_POST['nilai']         ?? 0);
    $id_menu_raw = (int)($_POST['id_menu']          ?? 0);
    $id_menu     = $id_menu_raw ? $id_menu_raw : 'NULL';
    $min_trx     = (float)($_POST['min_transaksi']  ?? 0);
    $tgl_mulai   = mysqli_real_escape_string($conn, $_POST['tanggal_mulai']      ?? '');
    $tgl_selesai = mysqli_real_escape_string($conn, $_POST['tanggal_selesai']    ?? '');
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    if ($id_edit) {
        mysqli_query($conn,
            "UPDATE promo SET nama_promo='$nama', jenis='$jenis', nilai=$nilai,
             id_menu=$id_menu, min_transaksi=$min_trx,
             tanggal_mulai='$tgl_mulai', tanggal_selesai='$tgl_selesai',
             is_active=$is_active, updated_at=NOW()
             WHERE id_promo=$id_edit");
        log_activity($conn, $id_admin, 'PROMO', 'UPDATE', "Edit promo: $nama (ID: $id_edit)");
        $msg = "Promo <strong>$nama</strong> diperbarui."; $msgType = 'success';
    } else {
        mysqli_query($conn,
            "INSERT INTO promo (nama_promo,jenis,nilai,id_menu,min_transaksi,
             tanggal_mulai,tanggal_selesai,is_active,dibuat_oleh)
             VALUES ('$nama','$jenis',$nilai,$id_menu,$min_trx,
             '$tgl_mulai','$tgl_selesai',$is_active,$id_admin)");
        $newId = mysqli_insert_id($conn);
        log_activity($conn, $id_admin, 'PROMO', 'CREATE', "Buat promo baru: $nama (ID: $newId)");
        $msg = "Promo <strong>$nama</strong> ditambahkan."; $msgType = 'success';
    }
}

// ── EDIT MODE ────────────────────────────────────────────────
$editData = null;
if (isset($_GET['edit'])) {
    $editData = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM promo WHERE id_promo=".(int)$_GET['edit']));
}

// ── DATA ─────────────────────────────────────────────────────
$promoList = mysqli_query($conn,
    "SELECT p.*, m.nama_menu, u.nama dibuat_nama
     FROM promo p
     LEFT JOIN menu m  ON p.id_menu=m.id_menu
     LEFT JOIN users u ON p.dibuat_oleh=u.id_user
     ORDER BY p.created_at DESC");

$menuList = mysqli_query($conn,"SELECT id_menu,nama_menu FROM menu WHERE is_active=1 ORDER BY nama_menu");
// Reset pointer untuk form edit
$menuArr = [];
while ($m = mysqli_fetch_assoc($menuList)) $menuArr[] = $m;

include __DIR__ . '/../../includes/layout_start.php';
?>

<style>
.form-label{font-size:13px;font-weight:600;color:#374151}
.form-control,.form-select{border-radius:8px;font-size:13px;border:1.5px solid #e5e7eb}
.form-control:focus,.form-select:focus{border-color:#e63946;box-shadow:0 0 0 3px rgba(230,57,70,.12)}
.promo-badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700}
.pb-persen{background:#eff6ff;color:#2563eb}
.pb-nominal{background:#f0fdf4;color:#16a34a}
.pb-bogo{background:#fdf4ff;color:#9333ea}
</style>

<?php if ($msg): ?>
<div class="alert alert-auto d-flex gap-2 align-items-center mb-3"
     style="border-radius:9px;font-size:13px;padding:10px 14px;
     background:<?=['success'=>'#f0fdf4','danger'=>'#fef2f2','warning'=>'#fffbeb'][$msgType]?>;
     color:<?=['success'=>'#16a34a','danger'=>'#dc2626','warning'=>'#d97706'][$msgType]?>;
     border:1px solid <?=['success'=>'#bbf7d0','danger'=>'#fecaca','warning'=>'#fde68a'][$msgType]?>">
  <i class="fa-solid fa-circle-info"></i><?= $msg ?>
</div>
<?php endif; ?>

<div class="row g-3">
  <!-- Form -->
  <div class="col-lg-4">
    <div class="card-box">
      <div class="c-head">
        <h5>
          <i class="fa-solid fa-<?= $editData?'pen-to-square':'plus'?> me-2" style="color:#e63946"></i>
          <?= $editData ? 'Edit Promo' : 'Tambah Promo' ?>
        </h5>
        <?php if ($editData): ?>
        <a href="promo.php" class="btn btn-sm btn-outline-secondary" style="font-size:11px">Batal</a>
        <?php endif; ?>
      </div>
      <div class="c-body">
        <form method="POST">
          <?php if ($editData): ?>
          <input type="hidden" name="id_promo" value="<?= $editData['id_promo'] ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">Nama Promo</label>
            <input type="text" name="nama_promo" class="form-control" required
              placeholder="cth: Promo Jumat Hemat"
              value="<?= htmlspecialchars($editData['nama_promo'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Jenis</label>
            <select name="jenis" id="selJenis" class="form-select" onchange="toggleNilai()" required>
              <option value="PERSEN"  <?= ($editData['jenis']??'')==='PERSEN' ?'selected':'' ?>>Diskon Persen (%)</option>
              <option value="NOMINAL" <?= ($editData['jenis']??'')==='NOMINAL'?'selected':'' ?>>Diskon Nominal (Rp)</option>
              <option value="BOGO"    <?= ($editData['jenis']??'')==='BOGO'   ?'selected':'' ?>>Buy 1 Get 1</option>
            </select>
          </div>

          <div class="mb-3" id="wrapNilai">
            <label class="form-label" id="lblNilai">Nilai Diskon</label>
            <div class="input-group">
              <span class="input-group-text" id="prefNilai"
                style="font-size:13px;border-radius:8px 0 0 8px;border:1.5px solid #e5e7eb;border-right:none">%</span>
              <input type="number" name="nilai" class="form-control"
                style="border-radius:0 8px 8px 0" min="0" step="0.01"
                value="<?= $editData['nilai'] ?? 0 ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Berlaku Untuk Menu</label>
            <select name="id_menu" class="form-select">
              <option value="">— Semua Menu —</option>
              <?php foreach ($menuArr as $m): ?>
              <option value="<?= $m['id_menu'] ?>"
                <?= ($editData['id_menu'] ?? '') == $m['id_menu'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['nama_menu']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Minimum Transaksi (Rp)</label>
            <input type="number" name="min_transaksi" class="form-control" min="0"
              placeholder="0 = tanpa minimum"
              value="<?= $editData['min_transaksi'] ?? 0 ?>">
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Tanggal Mulai</label>
              <input type="date" name="tanggal_mulai" class="form-control" required
                value="<?= $editData['tanggal_mulai'] ?? '' ?>">
            </div>
            <div class="col-6">
              <label class="form-label">Tanggal Selesai</label>
              <input type="date" name="tanggal_selesai" class="form-control" required
                value="<?= $editData['tanggal_selesai'] ?? '' ?>">
            </div>
          </div>

          <div class="mb-3 d-flex align-items-center gap-2">
            <input type="checkbox" name="is_active" id="cbActive" class="form-check-input"
              style="width:16px;height:16px;accent-color:#e63946"
              <?= ($editData ? $editData['is_active'] : 1) ? 'checked' : '' ?>>
            <label for="cbActive" class="form-check-label" style="font-size:13px">Aktifkan promo ini</label>
          </div>

          <button type="submit" class="btn w-100"
            style="background:#e63946;color:#fff;border-radius:8px;font-size:13px;padding:9px;border:none">
            <i class="fa-solid fa-save me-1"></i><?= $editData ? 'Simpan Perubahan' : 'Tambah Promo' ?>
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Tabel Promo -->
  <div class="col-lg-8">
    <div class="card-box">
      <div class="c-head">
        <h5><i class="fa-solid fa-tags me-2" style="color:#e63946"></i>Daftar Promo</h5>
      </div>
      <div class="c-body p-0" style="overflow-x:auto">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Nama</th><th>Jenis</th><th>Nilai</th><th>Menu</th>
              <th>Min Trx</th><th>Periode</th><th>Status</th><th>Dibuat</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $hasPr = false;
          while ($pr = mysqli_fetch_assoc($promoList)):
            $hasPr = true;
            $today   = date('Y-m-d');
            $expired = $pr['tanggal_selesai'] < $today;
            $jMap    = ['PERSEN'=>'pb-persen','NOMINAL'=>'pb-nominal','BOGO'=>'pb-bogo'];
            $pc      = $jMap[$pr['jenis']] ?? '';
            $nilaiTxt = $pr['jenis']==='BOGO' ? 'Buy 1 Get 1'
              : ($pr['jenis']==='PERSEN' ? number_format($pr['nilai'],0).'%'
              : 'Rp '.number_format($pr['nilai'],0,',','.'));
          ?>
          <tr style="<?= (!$pr['is_active']||$expired)?'opacity:.55':'' ?>">
            <td style="font-weight:600;font-size:13px"><?= htmlspecialchars($pr['nama_promo']) ?></td>
            <td><span class="promo-badge <?= $pc ?>"><?= $pr['jenis'] ?></span></td>
            <td style="font-weight:700;font-size:13px"><?= $nilaiTxt ?></td>
            <td style="font-size:12px">
              <?= $pr['nama_menu'] ? htmlspecialchars($pr['nama_menu']) : '<span class="text-muted">Semua</span>' ?>
            </td>
            <td style="font-size:12px">
              <?= $pr['min_transaksi']>0 ? 'Rp '.number_format($pr['min_transaksi'],0,',','.') : '—' ?>
            </td>
            <td style="font-size:11px;color:#6b7280">
              <?= date('d/m/Y',strtotime($pr['tanggal_mulai'])) ?> –<br>
              <?= date('d/m/Y',strtotime($pr['tanggal_selesai'])) ?>
              <?php if ($expired): ?>
              <br><span class="rbadge sb-danger" style="font-size:9px">Expired</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="rbadge <?= $pr['is_active']?'sb-success':'sb-danger' ?>">
                <?= $pr['is_active']?'Aktif':'Nonaktif' ?>
              </span>
            </td>
            <td style="font-size:11px;color:#9ca3af"><?= htmlspecialchars($pr['dibuat_nama']??'—') ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="?edit=<?= $pr['id_promo'] ?>"
                   class="btn btn-sm btn-outline-warning" style="font-size:10px;border-radius:5px" title="Edit">
                   <i class="fa-solid fa-pen"></i></a>
                <a href="?toggle=<?= $pr['id_promo'] ?>"
                   class="btn btn-sm <?= $pr['is_active']?'btn-outline-secondary':'btn-outline-success' ?>"
                   style="font-size:10px;border-radius:5px"
                   title="<?= $pr['is_active']?'Nonaktifkan':'Aktifkan' ?>">
                   <i class="fa-solid fa-power-off"></i></a>
                <a href="?delete=<?= $pr['id_promo'] ?>"
                   class="btn btn-sm btn-outline-danger" style="font-size:10px;border-radius:5px" title="Hapus"
                   onclick="return confirm('Hapus promo ini?')">
                   <i class="fa-solid fa-trash"></i></a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php if (!$hasPr): ?>
          <tr><td colspan="9" class="text-center py-4 text-muted" style="font-size:13px">
            <i class="fa-solid fa-tags me-2" style="color:#e63946"></i>Belum ada promo
          </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function toggleNilai() {
  const j = document.getElementById('selJenis').value;
  const w = document.getElementById('wrapNilai');
  if (j === 'BOGO') {
    w.style.display = 'none';
  } else {
    w.style.display = '';
    document.getElementById('lblNilai').textContent  = j==='PERSEN'?'Nilai Diskon (%)':'Nilai Diskon (Rp)';
    document.getElementById('prefNilai').textContent = j==='PERSEN'?'%':'Rp';
  }
}
toggleNilai();
</script>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
