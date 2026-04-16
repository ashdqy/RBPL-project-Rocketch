<?php
require_once __DIR__ . '/../../../middleware/role_admin.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/log_helper.php';
$pageTitle = 'Kelola Menu';
$msg = $msgType = '';
$id_admin = (int)$_SESSION['id_user'];

// ── HAPUS ────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT nama_menu FROM menu WHERE id_menu=$did"));
    if ($row) {
        mysqli_query($conn,"DELETE FROM menu WHERE id_menu=$did");
        log_activity($conn,$id_admin,'MENU','DELETE',"Hapus menu: {$row['nama_menu']} (ID: $did)");
        $msg = "Menu <strong>{$row['nama_menu']}</strong> dihapus."; $msgType = 'danger';
    }
}

// ── TOGGLE AKTIF ─────────────────────────────────────────────
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT nama_menu,is_active FROM menu WHERE id_menu=$tid"));
    if ($row) {
        $nv = $row['is_active'] ? 0 : 1;
        mysqli_query($conn,"UPDATE menu SET is_active=$nv WHERE id_menu=$tid");
        log_activity($conn,$id_admin,'MENU','UPDATE',
            "Toggle menu '{$row['nama_menu']}' → ".($nv?'AKTIF':'NONAKTIF')." (ID: $tid)");
        $msg = "Menu <strong>{$row['nama_menu']}</strong> ".($nv?'diaktifkan':'dinonaktifkan').".";
        $msgType = $nv ? 'success' : 'warning';
    }
}

// ── SAVE ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_edit    = (int)($_POST['id_menu']    ?? 0);
    $nama       = mysqli_real_escape_string($conn, trim($_POST['nama_menu']  ?? ''));
    $harga      = (float)($_POST['harga']    ?? 0);
    $kategori   = mysqli_real_escape_string($conn, trim($_POST['kategori']   ?? ''));
    $id_stok    = (int)($_POST['id_stok']    ?? 0); // link ke bahan baku utama
    $is_active  = isset($_POST['is_active']) ? 1 : 0;

    if ($nama && $harga > 0 && $kategori) {
        $stokLink = $id_stok ?: 'NULL';
        if ($id_edit) {
            mysqli_query($conn,
                "UPDATE menu SET nama_menu='$nama', harga=$harga,
                 kategori='$kategori', is_active=$is_active
                 WHERE id_menu=$id_edit");
            log_activity($conn,$id_admin,'MENU','UPDATE',"Edit menu: $nama (ID: $id_edit)");
            $msg = "Menu <strong>$nama</strong> diperbarui."; $msgType = 'success';
        } else {
            mysqli_query($conn,
                "INSERT INTO menu (nama_menu, harga, kategori, is_active)
                 VALUES ('$nama', $harga, '$kategori', $is_active)");
            $newId = mysqli_insert_id($conn);
            log_activity($conn,$id_admin,'MENU','CREATE',
                "Tambah menu: $nama, kategori $kategori, harga Rp ".number_format($harga,0,',','.')." (ID: $newId)");
            $msg = "Menu <strong>$nama</strong> ditambahkan."; $msgType = 'success';
        }
    } else {
        $msg = 'Nama, harga, dan kategori wajib diisi.'; $msgType = 'danger';
    }
}

// ── EDIT MODE ────────────────────────────────────────────────
$editData = null;
if (isset($_GET['edit'])) {
    $editData = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM menu WHERE id_menu=".(int)$_GET['edit']));
}

// ── FILTER & DATA ────────────────────────────────────────────
$fKat  = $_GET['kat']  ?? '';
$fCari = $_GET['cari'] ?? '';
$fStok = $_GET['stok'] ?? ''; // filter: kritis / kosong / aman
$where = ['1=1'];
if ($fKat)  $where[] = "m.kategori='" . mysqli_real_escape_string($conn,$fKat) . "'";
if ($fCari) $where[] = "m.nama_menu LIKE '%" . mysqli_real_escape_string($conn,$fCari) . "%'";
$w = implode(' AND ', $where);

// Join dengan stok untuk sinkronisasi real-time
// Asumsi: menu kategori 'Ayam*' membutuhkan stok ayam, dst.
// Kita tampilkan warning jika stok total rendah
$menuList = mysqli_query($conn,"SELECT * FROM menu m WHERE $w ORDER BY m.kategori, m.nama_menu");

$katList   = mysqli_query($conn,"SELECT DISTINCT kategori FROM menu ORDER BY kategori");
$stokList  = mysqli_query($conn,"SELECT * FROM stok ORDER BY nama_bahan");
$totalMenu = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM menu"))['c'];
$totalAktif= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM menu WHERE is_active=1"))['c'];

// Ringkasan stok untuk sinkronisasi
$stokKritis = mysqli_query($conn,"SELECT * FROM stok WHERE jumlah < 10 ORDER BY jumlah ASC");
$stokKritisArr = [];
while($s=mysqli_fetch_assoc($stokKritis)) $stokKritisArr[] = $s;

$kategoriOpts = [
    'Ayam Goreng','Ayam Geprek','Chicken Strip','Steak',
    'Nasi Goreng','Burger','Snack','Minuman',
    'Paket Hemat','Paket Roma','Paket Cheesy',
    'Paket Geprek','Paket Sehat','Paket Kids','Lainnya'
];

// Ambil stok array untuk dropdown
$stokArr = [];
$stokRes = mysqli_query($conn,"SELECT * FROM stok ORDER BY nama_bahan");
while($s=mysqli_fetch_assoc($stokRes)) $stokArr[]=$s;

include __DIR__ . '/../../../includes/layout_start.php';
?>

<style>
.form-label{font-size:13px;font-weight:600;color:#374151}
.form-control,.form-select{border-radius:8px;font-size:13px;border:1.5px solid #e5e7eb}
.form-control:focus,.form-select:focus{border-color:#e63946;box-shadow:0 0 0 3px rgba(230,57,70,.12)}
.kat-badge{display:inline-block;padding:2px 9px;border-radius:12px;font-size:10.5px;font-weight:700}
.kb-ayam{background:#fff7ed;color:#ea580c}.kb-geprek{background:#fef2f2;color:#dc2626}
.kb-strip{background:#fdf4ff;color:#9333ea}.kb-steak{background:#f0fdf4;color:#16a34a}
.kb-nasi{background:#fffbeb;color:#d97706}.kb-burger{background:#eff6ff;color:#2563eb}
.kb-snack{background:#f0f9ff;color:#0284c7}.kb-minum{background:#ecfdf5;color:#059669}
.kb-paket{background:#faf5ff;color:#7c3aed}.kb-lain{background:#f3f4f6;color:#374151}
.stok-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:4px}
.dot-ok{background:#16a34a}.dot-warn{background:#d97706}.dot-kritis{background:#dc2626}
</style>

<?php if(!empty($stokKritisArr)): ?>
<div class="alert d-flex gap-2 align-items-center mb-3"
     style="border-radius:9px;font-size:13px;padding:10px 14px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca">
  <i class="fa-solid fa-triangle-exclamation"></i>
  <strong><?=count($stokKritisArr)?> bahan</strong> stok kritis:
  <?=implode(', ', array_map(fn($s)=>"<strong>{$s['nama_bahan']}</strong> ({$s['jumlah']} {$s['satuan']})", $stokKritisArr))?>
  — pertimbangkan menonaktifkan menu terkait.
</div>
<?php endif; ?>

<?php if ($msg): ?>
<div class="alert alert-auto d-flex gap-2 align-items-center mb-3"
     style="border-radius:9px;font-size:13px;padding:10px 14px;
     background:<?=['success'=>'#f0fdf4','danger'=>'#fef2f2','warning'=>'#fffbeb'][$msgType]?>;
     color:<?=['success'=>'#16a34a','danger'=>'#dc2626','warning'=>'#d97706'][$msgType]?>;
     border:1px solid <?=['success'=>'#bbf7d0','danger'=>'#fecaca','warning'=>'#fde68a'][$msgType]?>">
  <i class="fa-solid fa-circle-info"></i><?= $msg ?>
</div>
<?php endif; ?>

<!-- Stat Mini -->
<div class="row g-3 mb-3">
  <div class="col-sm-4">
    <div class="stat-card d-flex align-items-center gap-3" style="padding:14px 18px">
      <div class="s-ic" style="background:rgba(230,57,70,.1);color:#e63946;width:42px;height:42px;font-size:18px">
        <i class="fa-solid fa-utensils"></i></div>
      <div>
        <div style="font-size:22px;font-weight:800;color:#111827"><?=$totalMenu?></div>
        <div style="font-size:12px;color:#6b7280">Total Menu</div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card d-flex align-items-center gap-3" style="padding:14px 18px">
      <div class="s-ic" style="background:rgba(16,185,129,.1);color:#10b981;width:42px;height:42px;font-size:18px">
        <i class="fa-solid fa-circle-check"></i></div>
      <div>
        <div style="font-size:22px;font-weight:800;color:#111827"><?=$totalAktif?></div>
        <div style="font-size:12px;color:#6b7280">Menu Aktif</div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card d-flex align-items-center gap-3" style="padding:14px 18px">
      <div class="s-ic" style="background:rgba(220,38,38,.1);color:#dc2626;width:42px;height:42px;font-size:18px">
        <i class="fa-solid fa-triangle-exclamation"></i></div>
      <div>
        <div style="font-size:22px;font-weight:800;color:#111827"><?=count($stokKritisArr)?></div>
        <div style="font-size:12px;color:#6b7280">Bahan Stok Kritis</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Form -->
  <div class="col-lg-4">
    <div class="card-box">
      <div class="c-head">
        <h5>
          <i class="fa-solid fa-<?=$editData?'pen-to-square':'plus'?> me-2" style="color:#e63946"></i>
          <?=$editData?'Edit Menu':'Tambah Menu'?>
        </h5>
        <?php if($editData): ?>
        <a href="index.php" class="btn btn-sm btn-outline-secondary" style="font-size:11px">Batal</a>
        <?php endif; ?>
      </div>
      <div class="c-body">
        <form method="POST">
          <?php if($editData): ?>
          <input type="hidden" name="id_menu" value="<?=$editData['id_menu']?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">Nama Menu</label>
            <input type="text" name="nama_menu" class="form-control" required
              placeholder="cth: Ayam Goreng Original"
              value="<?=htmlspecialchars($editData['nama_menu']??'')?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Harga (Rp)</label>
            <div class="input-group">
              <span class="input-group-text"
                style="font-size:13px;border-radius:8px 0 0 8px;border:1.5px solid #e5e7eb;border-right:none;background:#f9fafb">
                Rp</span>
              <input type="number" name="harga" class="form-control"
                style="border-radius:0 8px 8px 0" min="0" step="100" required
                value="<?=$editData['harga']??''?>" placeholder="15000">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select name="kategori" class="form-select" required>
              <option value="">— Pilih —</option>
              <?php foreach($kategoriOpts as $k): ?>
              <option value="<?=$k?>" <?=($editData['kategori']??'')===$k?'selected':''?>>
                <?=$k?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3 d-flex align-items-center gap-2">
            <input type="checkbox" name="is_active" id="cbActive" class="form-check-input"
              style="width:16px;height:16px;accent-color:#e63946"
              <?=($editData?$editData['is_active']:1)?'checked':''?>>
            <label for="cbActive" class="form-check-label" style="font-size:13px">
              Tampilkan menu ini (aktif)
            </label>
          </div>

          <button type="submit" class="btn w-100"
            style="background:#e63946;color:#fff;border-radius:8px;font-size:13px;padding:9px;border:none">
            <i class="fa-solid fa-save me-1"></i>
            <?=$editData?'Simpan Perubahan':'Tambah Menu'?>
          </button>
        </form>
      </div>
    </div>

    <!-- Status Stok Realtime -->
    <?php if(!empty($stokArr)): ?>
    <div class="card-box mt-3">
      <div class="c-head">
        <h5><i class="fa-solid fa-boxes-stacked me-2" style="color:#f59e0b"></i>Status Stok Realtime</h5>
      </div>
      <div class="c-body p-0">
        <table class="table mb-0">
          <thead><tr><th>Bahan</th><th>Stok</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach($stokArr as $s):
            $status = $s['jumlah'] <= 0 ? 'dot-kritis' : ($s['jumlah'] < 10 ? 'dot-warn' : 'dot-ok');
            $label  = $s['jumlah'] <= 0 ? 'Habis' : ($s['jumlah'] < 10 ? 'Kritis' : 'Aman');
            $lc     = $s['jumlah'] <= 0 ? '#dc2626' : ($s['jumlah'] < 10 ? '#d97706' : '#16a34a');
          ?>
          <tr>
            <td style="font-size:12px"><?=htmlspecialchars($s['nama_bahan'])?></td>
            <td style="font-size:12px;font-weight:700"><?=$s['jumlah']?> <?=$s['satuan']?></td>
            <td><span class="stok-dot <?=$status?>"></span>
                <span style="font-size:11px;color:<?=$lc?>;font-weight:700"><?=$label?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Tabel Menu -->
  <div class="col-lg-8">
    <div class="card-box">
      <div class="c-head">
        <h5><i class="fa-solid fa-utensils me-2" style="color:#e63946"></i>Daftar Menu</h5>
      </div>

      <!-- Filter -->
      <div style="padding:12px 18px;border-bottom:1px solid #f3f4f6;background:#fafafa">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
          <select name="kat" class="form-select"
            style="max-width:160px;font-size:12px;border-radius:8px;border:1.5px solid #e5e7eb;padding:5px 10px">
            <option value="">Semua Kategori</option>
            <?php while($k=mysqli_fetch_assoc($katList)): ?>
            <option value="<?=$k['kategori']?>" <?=$fKat===$k['kategori']?'selected':''?>>
              <?=$k['kategori']?>
            </option>
            <?php endwhile; ?>
          </select>
          <input type="text" name="cari" class="form-control"
            style="max-width:180px;font-size:12px;border-radius:8px;border:1.5px solid #e5e7eb;padding:5px 10px"
            placeholder="Cari nama menu..." value="<?=htmlspecialchars($fCari)?>">
          <button type="submit" class="btn btn-sm"
            style="background:#111827;color:#fff;border-radius:7px;font-size:12px;padding:5px 12px;border:none">
            <i class="fa-solid fa-search me-1"></i>Cari
          </button>
          <?php if($fKat||$fCari): ?>
          <a href="index.php" class="btn btn-sm btn-outline-secondary" style="border-radius:7px;font-size:12px">Reset</a>
          <?php endif; ?>
        </form>
      </div>

      <div class="c-body p-0" style="overflow-x:auto">
        <table class="table table-hover mb-0">
          <thead>
            <tr><th>#</th><th>Nama Menu</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
          </thead>
          <tbody>
          <?php
          $no=1; $hasRow=false;
          while($m=mysqli_fetch_assoc($menuList)):
            $hasRow=true;
            $kat=strtolower($m['kategori']);
            $kc='kb-lain';
            if(str_contains($kat,'geprek'))      $kc='kb-geprek';
            elseif(str_contains($kat,'ayam'))    $kc='kb-ayam';
            elseif(str_contains($kat,'strip'))   $kc='kb-strip';
            elseif(str_contains($kat,'steak'))   $kc='kb-steak';
            elseif(str_contains($kat,'nasi'))    $kc='kb-nasi';
            elseif(str_contains($kat,'burger'))  $kc='kb-burger';
            elseif(str_contains($kat,'snack'))   $kc='kb-snack';
            elseif(str_contains($kat,'minum'))   $kc='kb-minum';
            elseif(str_contains($kat,'paket'))   $kc='kb-paket';

            // Sinkronisasi stok: cek apakah bahan terkait kritis
            // Logika sederhana: jika kategori mengandung 'ayam', cek stok bahan ayam
            $stokWarning = false;
            foreach($stokKritisArr as $sk) {
                $nb = strtolower($sk['nama_bahan']);
                if ((str_contains($kat,'ayam')||str_contains($kat,'geprek')||str_contains($kat,'strip')||str_contains($kat,'steak'))
                    && str_contains($nb,'ayam')) { $stokWarning=true; break; }
                if (str_contains($kat,'nasi') && str_contains($nb,'beras')) { $stokWarning=true; break; }
                if (str_contains($kat,'minum') && str_contains($nb,'minum')) { $stokWarning=true; break; }
            }
          ?>
          <tr style="<?=!$m['is_active']?'opacity:.55':''?>">
            <td style="color:#9ca3af;font-size:12px"><?=$no++?></td>
            <td style="font-weight:600;font-size:13px">
              <?=htmlspecialchars($m['nama_menu'])?>
              <?php if($stokWarning): ?>
              <i class="fa-solid fa-triangle-exclamation ms-1" style="color:#f59e0b;font-size:11px" title="Bahan terkait stok kritis"></i>
              <?php endif; ?>
            </td>
            <td><span class="kat-badge <?=$kc?>"><?=htmlspecialchars($m['kategori'])?></span></td>
            <td style="font-weight:700;font-size:13px">Rp <?=number_format($m['harga'],0,',','.')?></td>
            <td>
              <?php if($stokWarning): ?>
              <span class="rbadge sb-warning" style="font-size:10px"><i class="fa-solid fa-triangle-exclamation me-1"></i>Kritis</span>
              <?php else: ?>
              <span class="rbadge sb-success" style="font-size:10px">Aman</span>
              <?php endif; ?>
            </td>
            <td><span class="rbadge <?=$m['is_active']?'sb-success':'sb-danger'?>">
              <?=$m['is_active']?'Aktif':'Nonaktif'?></span></td>
            <td>
              <div class="d-flex gap-1">
                <a href="?edit=<?=$m['id_menu']?>"
                   class="btn btn-sm btn-outline-warning" style="font-size:10px;border-radius:5px">
                   <i class="fa-solid fa-pen"></i></a>
                <a href="?toggle=<?=$m['id_menu']?>"
                   class="btn btn-sm <?=$m['is_active']?'btn-outline-secondary':'btn-outline-success'?>"
                   style="font-size:10px;border-radius:5px">
                   <i class="fa-solid fa-power-off"></i></a>
                <a href="?delete=<?=$m['id_menu']?>"
                   class="btn btn-sm btn-outline-danger" style="font-size:10px;border-radius:5px"
                   onclick="return confirm('Hapus menu ini?')">
                   <i class="fa-solid fa-trash"></i></a>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
          <?php if(!$hasRow): ?>
          <tr><td colspan="7" class="text-center py-4 text-muted" style="font-size:13px">
            <i class="fa-solid fa-utensils me-2" style="color:#e63946"></i>
            <?=($fKat||$fCari)?'Tidak ada menu yang cocok':'Belum ada menu'?>
          </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../../includes/layout_end.php'; ?>
