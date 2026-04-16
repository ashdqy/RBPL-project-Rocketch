<?php
require_once __DIR__ . '/../../../middleware/role_admin.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../includes/log_helper.php';
$pageTitle = 'Backup Database';
$id_admin  = (int)$_SESSION['id_user'];
$msg = $msgType = '';

// Ambil config database
$dbHost = 'localhost';
$dbName = 'rocket2';
$dbUser = 'root';
$dbPass = '';

// ── JALANKAN BACKUP ───────────────────────────────────────────
if (isset($_GET['backup'])) {
    $tabel    = $_GET['backup'] === 'all' ? null : $_GET['backup'];
    $filename = 'backup_' . ($tabel ?? 'full') . '_' . date('Ymd_His') . '.sql';
    $content  = "-- Rocket Chicken Backup\n";
    $content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    $content .= "-- Database: $dbName\n\n";
    $content .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

    $tables = $tabel ? [$tabel] : [];
    if (!$tabel) {
        $res = mysqli_query($conn,"SHOW TABLES");
        while($r=mysqli_fetch_row($res)) $tables[]=$r[0];
    }

    foreach ($tables as $tbl) {
        $tbl = mysqli_real_escape_string($conn,$tbl);
        // DDL
        $createRes = mysqli_fetch_row(mysqli_query($conn,"SHOW CREATE TABLE `$tbl`"));
        $content .= "-- Table: $tbl\n";
        $content .= "DROP TABLE IF EXISTS `$tbl`;\n";
        $content .= $createRes[1] . ";\n\n";

        // Data
        $dataRes = mysqli_query($conn,"SELECT * FROM `$tbl`");
        if (mysqli_num_rows($dataRes) > 0) {
            while ($row = mysqli_fetch_row($dataRes)) {
                $vals = array_map(function($v) use ($conn) {
                    return is_null($v) ? 'NULL' : "'".mysqli_real_escape_string($conn,$v)."'";
                }, $row);
                $content .= "INSERT INTO `$tbl` VALUES(" . implode(',', $vals) . ");\n";
            }
            $content .= "\n";
        }
    }

    // Simpan ke file
    $backupDir = __DIR__ . '/../../../backups/';
    if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
    file_put_contents($backupDir . $filename, $content);

    log_activity($conn, $id_admin, 'BACKUP', 'CREATE',
        "Backup database: " . ($tabel ?? 'FULL') . " → $filename");

    // Download langsung
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit;
}

// ── DAFTAR FILE BACKUP ────────────────────────────────────────
$backupDir  = __DIR__ . '/../../../backups/';
$backupFiles = [];
if (is_dir($backupDir)) {
    $files = glob($backupDir . '*.sql');
    if ($files) {
        rsort($files);
        foreach ($files as $f) {
            $backupFiles[] = [
                'name' => basename($f),
                'size' => filesize($f),
                'time' => filemtime($f),
            ];
        }
    }
}

// Tabel list
$tableList = [];
$res = mysqli_query($conn,"SHOW TABLES");
while($r=mysqli_fetch_row($res)) $tableList[]=$r[0];

// Statistik database
$dbStats = [];
foreach ($tableList as $tbl) {
    $cnt = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM `$tbl`"))['c'];
    $dbStats[$tbl] = $cnt;
}

include __DIR__ . '/../../../includes/layout_start.php';
?>

<style>
.form-control,.form-select{border-radius:8px;font-size:13px;border:1.5px solid #e5e7eb}
.backup-item{background:#fff;border-radius:10px;padding:12px 16px;border:1px solid #e5e7eb;
             display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;transition:.2s}
.backup-item:hover{border-color:#e63946;box-shadow:0 2px 8px rgba(230,57,70,.1)}
</style>

<div class="row g-3">
  <!-- Backup Panel -->
  <div class="col-lg-5">
    <!-- Backup Cepat -->
    <div class="card-box mb-3">
      <div class="c-head">
        <h5><i class="fa-solid fa-floppy-disk me-2" style="color:#e63946"></i>Backup Database</h5>
      </div>
      <div class="c-body">
        <p style="font-size:13px;color:#6b7280;margin-bottom:16px">
          Download backup dalam format SQL. Backup bisa diimport kembali via phpMyAdmin.
        </p>

        <!-- Full backup -->
        <a href="?backup=all"
           class="btn w-100 mb-3"
           style="background:#e63946;color:#fff;border-radius:8px;font-size:13px;padding:10px;border:none"
           onclick="return confirm('Backup FULL database sekarang?')">
          <i class="fa-solid fa-database me-2"></i>Backup Full Database
        </a>

        <div style="border-top:1px solid #f3f4f6;padding-top:14px;margin-top:4px">
          <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px">
            Backup Per Tabel:
          </div>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach($tableList as $tbl): ?>
            <a href="?backup=<?=$tbl?>"
               class="btn btn-sm btn-outline-secondary"
               style="font-size:11px;border-radius:6px"
               onclick="return confirm('Backup tabel <?=$tbl?>?')">
              <i class="fa-solid fa-table me-1"></i><?=$tbl?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistik Tabel -->
    <div class="card-box">
      <div class="c-head">
        <h5><i class="fa-solid fa-table me-2" style="color:#3b82f6"></i>Statistik Tabel</h5>
      </div>
      <div class="c-body p-0">
        <table class="table mb-0">
          <thead><tr><th>Tabel</th><th>Jumlah Row</th></tr></thead>
          <tbody>
          <?php foreach($dbStats as $tbl=>$cnt): ?>
          <tr>
            <td style="font-size:13px"><code><?=$tbl?></code></td>
            <td style="font-weight:700;font-size:13px"><?=number_format($cnt)?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Riwayat Backup -->
  <div class="col-lg-7">
    <div class="card-box">
      <div class="c-head">
        <h5><i class="fa-solid fa-clock-rotate-left me-2" style="color:#6b7280"></i>
          Riwayat Backup
        </h5>
        <span class="text-muted" style="font-size:12px"><?=count($backupFiles)?> file</span>
      </div>
      <div class="c-body">
        <?php if(empty($backupFiles)): ?>
        <div class="text-center py-4 text-muted" style="font-size:13px">
          <i class="fa-solid fa-database me-2" style="color:#e63946"></i>
          Belum ada file backup. Klik "Backup Full Database" untuk membuat backup pertama.
        </div>
        <?php else: ?>
        <?php foreach($backupFiles as $f): ?>
        <div class="backup-item">
          <div class="d-flex align-items-center gap-3">
            <div style="width:38px;height:38px;background:rgba(230,57,70,.08);border-radius:9px;
                        display:flex;align-items:center;justify-content:center;color:#e63946;font-size:16px">
              <i class="fa-solid fa-file-code"></i>
            </div>
            <div>
              <div style="font-size:13px;font-weight:600;color:#111827"><?=$f['name']?></div>
              <div style="font-size:11px;color:#9ca3af">
                <?=date('d M Y H:i',  $f['time'])?> &nbsp;·&nbsp;
                <?=number_format($f['size']/1024,1)?> KB
              </div>
            </div>
          </div>
          <a href="?download=<?=urlencode($f['name'])?>"
             class="btn btn-sm btn-outline-primary" style="font-size:11px;border-radius:6px">
             <i class="fa-solid fa-download me-1"></i>Download
          </a>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Info -->
    <div class="card-box mt-3" style="border-left:3px solid #3b82f6">
      <div class="c-body" style="padding:14px 16px">
        <div style="font-size:12px;font-weight:700;color:#3b82f6;margin-bottom:8px">
          <i class="fa-solid fa-circle-info me-1"></i>Cara Restore Backup
        </div>
        <ol style="font-size:12px;color:#6b7280;margin:0;padding-left:16px;line-height:2">
          <li>Buka <strong>phpMyAdmin</strong></li>
          <li>Pilih database <code>rocket2</code></li>
          <li>Klik tab <strong>Import</strong></li>
          <li>Pilih file <code>.sql</code> yang didownload</li>
          <li>Klik <strong>Go</strong></li>
        </ol>
      </div>
    </div>
  </div>
</div>

<?php
// Handle download file backup yang sudah ada
if (isset($_GET['download'])) {
    $fname = basename($_GET['download']);
    $fpath = $backupDir . $fname;
    if (file_exists($fpath) && pathinfo($fname,PATHINFO_EXTENSION)==='sql') {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="'.$fname.'"');
        readfile($fpath);
        exit;
    }
}
?>

<?php include __DIR__ . '/../../../includes/layout_end.php'; ?>
