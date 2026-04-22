<?php
require_once __DIR__ . '/../../middleware/role_spv.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/log_helper.php';
$pageTitle = 'Validasi Laporan';
$msg = $msgType = '';
$id_validator = (int)$_SESSION['id_user'];


if (isset($_GET['submit'])) {
    $sid = (int)$_GET['submit'];
    mysqli_query($conn,"UPDATE laporan SET status='DRAFT' WHERE id_laporan=$sid AND id_spv=$id_validator");
    $msg = 'Laporan berhasil dikirim untuk divalidasi.'; $msgType = 'success';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    $id_lap  = (int)$_POST['id_laporan'];
    $aksi    = $_POST['aksi']; 
    $catatan = trim($_POST['catatan'] ?? '');

    $statusMap = ['APPROVE' => 'FINAL', 'REJECT' => 'REJECTED', 'REVISI' => 'REVISI'];
    $newStatus = $statusMap[$aksi] ?? '';

    if ($newStatus && $id_lap) {
        $ns  = mysqli_real_escape_string($conn, $newStatus);
        $cat = mysqli_real_escape_string($conn, $catatan);
        $ak  = mysqli_real_escape_string($conn, $aksi);

        mysqli_query($conn,"UPDATE laporan SET status='$ns' WHERE id_laporan=$id_lap");
        mysqli_query($conn,
            "INSERT INTO laporan_validasi (id_laporan, id_validator, aksi, catatan)
             VALUES ($id_lap, $id_validator, '$ak', '$cat')");

        log_activity($conn, $id_validator, 'LAPORAN', $aksi,
            "Laporan ID $id_lap di-$aksi" . ($catatan ? ". Catatan: $catatan" : ''));

        $labelAksi = ['APPROVE'=>'disetujui ✅','REJECT'=>'ditolak ❌','REVISI'=>'dikembalikan untuk revisi 🔄'][$aksi] ?? $aksi;
        $msg = "Laporan berhasil <strong>$labelAksi</strong>.";
        $msgType = $aksi === 'APPROVE' ? 'success' : ($aksi === 'REJECT' ? 'danger' : 'warning');
    }
}

// ── DATA ──────────────────────────────────────────────────────
$draftList = mysqli_query($conn,
    "SELECT l.*, u.nama spv_nama, u.role spv_role
     FROM laporan l JOIN users u ON l.id_spv=u.id_user
     WHERE l.status IN ('DRAFT','REVISI')
     ORDER BY l.tanggal ASC");

$historyList = mysqli_query($conn,
    "SELECT l.jenis, l.periode, l.status,
            u.nama spv_nama,
            v.aksi, v.catatan, v.tanggal tgl_validasi,
            uv.nama validator
     FROM laporan_validasi v
     JOIN laporan l  ON v.id_laporan=l.id_laporan
     JOIN users u    ON l.id_spv=u.id_user
     JOIN users uv   ON v.id_validator=uv.id_user
     ORDER BY v.tanggal DESC
     LIMIT 40");

include __DIR__ . '/../../includes/layout_start.php';
?>

<style>
.form-control{border-radius:8px;font-size:13px;border:1.5px solid #e5e7eb}
.form-control:focus{border-color:#10b981;box-shadow:0 0 0 3px rgba(16,185,129,.15)}
.aksi-btn{border:none;border-radius:7px;padding:5px 11px;font-size:11px;font-weight:700;cursor:pointer;transition:.15s;white-space:nowrap}
.btn-approve{background:#dcfce7;color:#16a34a}.btn-approve:hover{background:#16a34a;color:#fff}
.btn-reject{background:#fef2f2;color:#dc2626}.btn-reject:hover{background:#dc2626;color:#fff}
.btn-revisi{background:#fef9c3;color:#b45309}.btn-revisi:hover{background:#b45309;color:#fff}
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

<!-- Modal Validasi -->
<div class="modal fade" id="modalValidasi" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:13px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15)">
      <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:16px 20px">
        <h6 class="modal-title fw-bold" id="modalTitle">Validasi Laporan</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body" style="padding:20px">
          <input type="hidden" name="id_laporan" id="inputId">
          <input type="hidden" name="aksi" id="inputAksi">
          <div class="mb-3 p-3 rounded-3" id="infoBox"
               style="background:#f9fafb;font-size:13px;color:#374151;border:1px solid #e5e7eb"></div>
          <div>
            <label style="font-size:13px;font-weight:600;color:#374151">
              Catatan <span id="catatanNote" style="font-weight:400;color:#9ca3af"></span>
            </label>
            <textarea name="catatan" id="inputCatatan" class="form-control mt-1" rows="3"
              placeholder="Tuliskan catatan..."></textarea>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:12px 20px">
          <button type="button" class="btn btn-sm btn-outline-secondary"
            data-bs-dismiss="modal" style="border-radius:7px">Batal</button>
          <button type="submit" id="btnKonfirmasi"
            class="btn btn-sm" style="border-radius:7px;padding:6px 18px;font-size:13px;border:none">
            Konfirmasi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Menunggu Validasi -->
<div class="card-box mb-3">
  <div class="c-head">
    <h5><i class="fa-solid fa-hourglass-half me-2" style="color:#f59e0b"></i>Menunggu Validasi</h5>
    <?php
    $cnt = mysqli_num_rows($draftList);
    if ($cnt > 0):
    ?><span class="rbadge sb-warning"><?= $cnt ?> laporan</span><?php endif; ?>
  </div>
  <div class="c-body p-0" style="overflow-x:auto">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Jenis</th><th>Periode</th><th>Dibuat Oleh</th><th>Role</th>
          <th>Penjualan</th><th>Laba/Rugi</th><th>Tgl Buat</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $hasRow = false;
      while ($l = mysqli_fetch_assoc($draftList)):
        $hasRow = true;
        $lr = (float)($l['laba_rugi'] ?? 0);
        $rmap = ['SUPER ADMIN'=>'rb-sa','SPV'=>'rb-spv','KASIR'=>'rb-ka','TRAINING'=>'rb-tr','COOKER'=>'rb-co'];
        $rc = $rmap[$l['spv_role']] ?? '';
        $infoTxt = "Jenis: {$l['jenis']} | Periode: {$l['periode']} | Dibuat oleh: {$l['spv_nama']} | Total Penjualan: Rp " . number_format($l['total_penjualan']??0,0,',','.');
      ?>
      <tr>
        <td><span class="rbadge sb-info"><?= $l['jenis'] ?></span></td>
        <td style="font-size:12px"><?= htmlspecialchars($l['periode']) ?></td>
        <td style="font-weight:600;font-size:13px"><?= htmlspecialchars($l['spv_nama']) ?></td>
        <td><span class="rbadge <?= $rc ?>"><?= $l['spv_role'] ?></span></td>
        <td style="font-size:12px">Rp <?= number_format($l['total_penjualan']??0,0,',','.') ?></td>
        <td style="font-weight:700;font-size:12px;color:<?= $lr>=0?'#16a34a':'#dc2626' ?>">
          <?= $lr>=0?'+':'' ?>Rp <?= number_format($lr,0,',','.') ?></td>
        <td style="font-size:11px;color:#6b7280"><?= date('d/m/Y H:i',strtotime($l['tanggal'])) ?></td>
        <td><span class="rbadge <?= $l['status']==='REVISI'?'sb-info':'sb-warning' ?>">
          <?= $l['status'] ?></span></td>
        <td>
          <div class="d-flex gap-1">
            <button class="aksi-btn btn-approve"
              onclick="bukaModal(<?=$l['id_laporan']?>,'APPROVE','<?=addslashes($infoTxt)?>')">
              <i class="fa-solid fa-check me-1"></i>Approve</button>
            <button class="aksi-btn btn-revisi"
              onclick="bukaModal(<?=$l['id_laporan']?>,'REVISI','<?=addslashes($infoTxt)?>')">
              <i class="fa-solid fa-rotate-left me-1"></i>Revisi</button>
            <button class="aksi-btn btn-reject"
              onclick="bukaModal(<?=$l['id_laporan']?>,'REJECT','<?=addslashes($infoTxt)?>')">
              <i class="fa-solid fa-xmark me-1"></i>Tolak</button>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
      <?php if (!$hasRow): ?>
      <tr><td colspan="9" class="text-center py-4 text-muted" style="font-size:13px">
        <i class="fa-solid fa-check-circle me-2" style="color:#10b981"></i>
        Tidak ada laporan yang perlu divalidasi
      </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Riwayat Validasi -->
<div class="card-box">
  <div class="c-head">
    <h5><i class="fa-solid fa-clock-rotate-left me-2" style="color:#6b7280"></i>Riwayat Validasi</h5>
  </div>
  <div class="c-body p-0" style="overflow-x:auto">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Laporan</th><th>Periode</th><th>Dibuat Oleh</th>
          <th>Validator</th><th>Aksi</th><th>Catatan</th><th>Tanggal</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $hasH = false;
      while ($h = mysqli_fetch_assoc($historyList)):
        $hasH = true;
        $aksiMap = [
          'APPROVE' => ['sb-success','fa-check','Disetujui'],
          'REJECT'  => ['sb-danger', 'fa-xmark','Ditolak'],
          'REVISI'  => ['sb-warning','fa-rotate-left','Revisi'],
        ];
        [$hc,$hi,$hl] = $aksiMap[$h['aksi']] ?? ['sb-info','fa-circle','?'];
      ?>
      <tr>
        <td><span class="rbadge sb-info"><?= $h['jenis'] ?></span></td>
        <td style="font-size:12px"><?= htmlspecialchars($h['periode']) ?></td>
        <td style="font-size:13px"><?= htmlspecialchars($h['spv_nama']) ?></td>
        <td style="font-weight:600;font-size:13px"><?= htmlspecialchars($h['validator']) ?></td>
        <td><span class="rbadge <?= $hc ?>">
          <i class="fa-solid <?= $hi ?> me-1"></i><?= $hl ?></span></td>
        <td style="font-size:12px;color:#6b7280;max-width:200px">
          <?= $h['catatan'] ? htmlspecialchars($h['catatan']) : '<span class="text-muted">—</span>' ?></td>
        <td style="font-size:11px;color:#6b7280"><?= date('d/m/Y H:i',strtotime($h['tgl_validasi'])) ?></td>
      </tr>
      <?php endwhile; ?>
      <?php if (!$hasH): ?>
      <tr><td colspan="7" class="text-center py-3 text-muted" style="font-size:13px">
        Belum ada riwayat validasi
      </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function bukaModal(id, aksi, info) {
  document.getElementById('inputId').value   = id;
  document.getElementById('inputAksi').value = aksi;
  document.getElementById('infoBox').textContent = info;
  document.getElementById('inputCatatan').value  = '';

  const cfg = {
    APPROVE: { title:'✅ Setujui Laporan',  bg:'#16a34a', note:'(opsional)' },
    REJECT:  { title:'❌ Tolak Laporan',    bg:'#dc2626', note:'(wajib diisi)' },
    REVISI:  { title:'🔄 Kembalikan Revisi',bg:'#d97706', note:'(wajib diisi)' },
  };
  const c = cfg[aksi];
  document.getElementById('modalTitle').textContent    = c.title;
  document.getElementById('catatanNote').textContent   = c.note;
  const btn = document.getElementById('btnKonfirmasi');
  btn.textContent      = aksi.charAt(0) + aksi.slice(1).toLowerCase();
  btn.style.background = c.bg;
  btn.style.color      = '#fff';

  new bootstrap.Modal(document.getElementById('modalValidasi')).show();
}
</script>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
