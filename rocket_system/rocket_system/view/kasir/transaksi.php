<?php
require_once __DIR__ . '/../../middleware/auth.php';
if($_SESSION['role']!=='KASIR'){header("Location: ../../dashboard.php");exit;}
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Buat Transaksi';
$msg = '';

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['submit_trx'])) {
    $metode  = $_POST['metode_pembayaran']??'CASH';
    $items   = $_POST['items']??[];
    $id_promo_used = (int)($_POST['id_promo']??0);
    $diskon_input  = (float)($_POST['diskon_nilai']??0);
    
    if(empty($items)) {
        $msg = 'ERROR: Tambahkan minimal 1 menu.';
    } else {
        $no_struk = 'TRX-' . date('YmdHis') . '-' . rand(100,999);
        $subtotal_total = 0;
        
        foreach($items as $item) {
            if(!isset($item['id_menu'])||!isset($item['jumlah'])) continue;
            $menuRow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT harga FROM menu WHERE id_menu=".(int)$item['id_menu']));
            if($menuRow) $subtotal_total += $menuRow['harga'] * (int)$item['jumlah'];
        }

        // Validasi & hitung diskon dari promo
        $diskon = 0;
        if($id_promo_used > 0) {
            $today = date('Y-m-d');
            $promoRow = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT * FROM promo WHERE id_promo=$id_promo_used AND is_active=1
                 AND tanggal_mulai<='$today' AND tanggal_selesai>='$today'"));
            if($promoRow && $subtotal_total >= $promoRow['min_transaksi']) {
                if($promoRow['jenis']==='PERSEN') $diskon = $subtotal_total * ($promoRow['nilai']/100);
                elseif($promoRow['jenis']==='NOMINAL') $diskon = min($promoRow['nilai'], $subtotal_total);
                elseif($promoRow['jenis']==='BOGO') $diskon = min($diskon_input, $subtotal_total);
            }
        }
        $total = max(0, $subtotal_total - $diskon);
        
        $id_kasir = (int)$_SESSION['id_user'];
        mysqli_query($conn,"INSERT INTO transaksi (no_struk,id_kasir,total,status_pembayaran,metode_pembayaran) VALUES('$no_struk',$id_kasir,$total,'PAID','$metode')");
        $trxId = mysqli_insert_id($conn);
        
        foreach($items as $item) {
            if(!isset($item['id_menu'])||!isset($item['jumlah'])) continue;
            $mid = (int)$item['id_menu'];
            $qty = (int)$item['jumlah'];
            $menuRow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT harga FROM menu WHERE id_menu=$mid"));
            if($menuRow) {
                $harga   = $menuRow['harga'];
                $subtotal= $harga * $qty;
                mysqli_query($conn,"INSERT INTO detail_transaksi (id_transaksi,id_menu,jumlah,harga,subtotal) VALUES($trxId,$mid,$qty,$harga,$subtotal)");
            }
        }
        
        header("Location: riwayat.php?sukses=".urlencode("Transaksi $no_struk berhasil! Total: Rp ".number_format($total,0,',','.'))); exit;
    }
}

$menuList = mysqli_query($conn,"SELECT * FROM menu WHERE is_active=1 ORDER BY kategori,nama_menu");
$menus = [];
while($m=mysqli_fetch_assoc($menuList)) $menus[] = $m;
$byKategori = [];
foreach($menus as $m) $byKategori[$m['kategori']][] = $m;

// Ambil promo aktif yang masih berlaku
$today = date('Y-m-d');
$promoList = mysqli_query($conn,"SELECT * FROM promo WHERE is_active=1 AND tanggal_mulai<='$today' AND tanggal_selesai>='$today'");
$promos = [];
while($p=mysqli_fetch_assoc($promoList)) $promos[] = $p;

include __DIR__ . '/../../includes/layout_start.php';
?>

<?php if($msg): ?>
<div class="alert sb-danger mb-3" style="border-radius:9px;font-size:13px;padding:10px 14px"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<form method="POST" id="trxForm">
<div class="row g-3">
  <!-- Menu Selection -->
  <div class="col-lg-7">
    <div class="card-box">
      <div class="c-head"><h5><i class="fa-solid fa-utensils me-2" style="color:#e63946"></i>Pilih Menu</h5></div>
      <div class="c-body">
        <?php foreach($byKategori as $kat => $items): ?>
        <h6 style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin-bottom:8px"><?= htmlspecialchars($kat) ?></h6>
        <div class="row g-2 mb-3">
          <?php foreach($items as $m): ?>
          <div class="col-sm-4">
            <div class="p-2 border rounded" style="border-radius:8px!important;cursor:pointer;transition:.15s" 
                 onclick="addToCart(<?=$m['id_menu']?>,<?= htmlspecialchars(json_encode($m['nama_menu'])) ?>,<?=$m['harga']?>)"
                 onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background=''">
              <div style="font-size:12.5px;font-weight:600"><?= htmlspecialchars($m['nama_menu']) ?></div>
              <div style="font-size:11px;color:#10b981;font-weight:700">Rp <?= number_format($m['harga'],0,',','.') ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <?php if(empty($byKategori)): ?>
        <p class="text-muted text-center py-3" style="font-size:13px">Belum ada menu aktif. Tambahkan menu terlebih dahulu.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Cart -->
  <div class="col-lg-5">
    <div class="card-box" style="position:sticky;top:70px">
      <div class="c-head"><h5><i class="fa-solid fa-shopping-cart me-2" style="color:#10b981"></i>Keranjang</h5></div>
      <div class="c-body">
        <div id="cartItems" style="min-height:80px">
          <p class="text-muted text-center" id="emptyMsg" style="font-size:13px;padding:20px 0">Klik menu untuk menambahkan</p>
        </div>
        <hr>
        <div class="d-flex justify-content-between mb-1">
          <strong style="font-size:15px">Subtotal:</strong>
          <strong style="font-size:14px;color:#6b7280" id="subtotalDisplay">Rp 0</strong>
        </div>
        <!-- PROMO SECTION -->
        <div id="promoSection" style="display:none;margin-bottom:10px">
          <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:4px">Promo Tersedia:</div>
          <div id="promoList"></div>
        </div>
        <div class="d-flex justify-content-between mb-3">
          <strong style="font-size:15px">Total:</strong>
          <strong style="font-size:18px;color:#e63946" id="totalDisplay">Rp 0</strong>
        </div>
        <input type="hidden" name="id_promo" id="hiddenPromo" value="">
        <input type="hidden" name="diskon_nilai" id="hiddenDiskon" value="0">
        <div class="mb-3">
          <label class="form-label" style="font-size:12px;font-weight:600">Metode Pembayaran</label>
          <select name="metode_pembayaran" class="form-select form-select-sm" style="border-radius:7px">
            <option value="CASH">Cash</option>
            <option value="QRIS">QRIS</option>
            <option value="TRANSFER">Transfer</option>
          </select>
        </div>
        <button type="submit" name="submit_trx" id="btnSubmit" class="btn w-100" 
                style="background:#e63946;color:#fff;border-radius:8px;font-size:14px;padding:11px" disabled>
          <i class="fa-solid fa-check me-2"></i>Proses Transaksi
        </button>
        <div id="hiddenItems"></div>
      </div>
    </div>
  </div>
</div>
</form>

<script>
const cart = {};
const promos = <?= json_encode($promos) ?>;

function addToCart(id, nama, harga) {
  if (cart[id]) cart[id].jumlah++;
  else cart[id] = { id, nama, harga, jumlah: 1 };
  renderCart();
}

function removeItem(id) {
  delete cart[id];
  renderCart();
}

function changeQty(id, delta) {
  cart[id].jumlah += delta;
  if (cart[id].jumlah <= 0) delete cart[id];
  renderCart();
}

function applyPromo(idPromo) {
  // toggle: klik lagi = batal
  const cur = document.getElementById('hiddenPromo').value;
  document.getElementById('hiddenPromo').value = (cur == idPromo) ? '' : idPromo;
  renderCart();
}

function renderCart() {
  const container = document.getElementById('cartItems');
  const hidden = document.getElementById('hiddenItems');
  const btnSubmit = document.getElementById('btnSubmit');
  
  let subtotal = 0;
  let html = '';
  let hiddenHtml = '';
  let i = 0;
  // collect id_menu in cart for BOGO check
  const cartMenuIds = {};
  
  for (const id in cart) {
    const item = cart[id];
    const sub = item.harga * item.jumlah;
    subtotal += sub;
    cartMenuIds[id] = item.jumlah;
    html += `<div class="d-flex justify-content-between align-items-center mb-2" style="font-size:13px">
      <div>
        <div style="font-weight:600">${item.nama}</div>
        <div style="color:#6b7280;font-size:11px">Rp ${item.harga.toLocaleString('id-ID')} × ${item.jumlah}</div>
      </div>
      <div class="d-flex align-items-center gap-1">
        <button type="button" onclick="changeQty(${id},-1)" class="btn btn-sm btn-outline-secondary" style="width:24px;height:24px;padding:0;font-size:12px;border-radius:5px">-</button>
        <span style="min-width:20px;text-align:center">${item.jumlah}</span>
        <button type="button" onclick="changeQty(${id},1)" class="btn btn-sm btn-outline-secondary" style="width:24px;height:24px;padding:0;font-size:12px;border-radius:5px">+</button>
        <button type="button" onclick="removeItem(${id})" class="btn btn-sm btn-outline-danger" style="width:24px;height:24px;padding:0;font-size:10px;border-radius:5px">✕</button>
      </div>
    </div>`;
    hiddenHtml += `<input type="hidden" name="items[${i}][id_menu]" value="${id}">
                   <input type="hidden" name="items[${i}][jumlah]" value="${item.jumlah}">`;
    i++;
  }
  
  if (i === 0) {
    container.innerHTML = '<p class="text-muted text-center" style="font-size:13px;padding:20px 0">Klik menu untuk menambahkan</p>';
    btnSubmit.disabled = true;
  } else {
    container.innerHTML = html;
    btnSubmit.disabled = false;
  }
  
  hidden.innerHTML = hiddenHtml;

  // --- PROMO LOGIC ---
  document.getElementById('subtotalDisplay').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');

  const selectedPromoId = document.getElementById('hiddenPromo').value;
  let diskon = 0;
  let promoHtml = '';
  let hasEligible = false;

  promos.forEach(p => {
    // Cek minimum transaksi
    if (subtotal < parseFloat(p.min_transaksi)) return;
    // Cek apakah menu berlaku
    if (p.id_menu && !cartMenuIds[p.id_menu]) return;

    hasEligible = true;
    const isSelected = String(p.id_promo) === String(selectedPromoId);
    let nilaiTxt = '';
    if (p.jenis === 'PERSEN') nilaiTxt = `Diskon ${parseFloat(p.nilai)}%`;
    else if (p.jenis === 'NOMINAL') nilaiTxt = `Diskon Rp ${parseFloat(p.nilai).toLocaleString('id-ID')}`;
    else nilaiTxt = 'Buy 1 Get 1';

    promoHtml += `<div onclick="applyPromo(${p.id_promo})" style="cursor:pointer;padding:7px 10px;border-radius:8px;margin-bottom:5px;border:2px solid ${isSelected?'#10b981':'#e5e7eb'};background:${isSelected?'#f0fdf4':'#fff'};font-size:12px">
      <div style="font-weight:700;color:${isSelected?'#16a34a':'#374151'}">${p.nama_promo}</div>
      <div style="color:#6b7280">${nilaiTxt} · Min Rp ${parseFloat(p.min_transaksi).toLocaleString('id-ID')}</div>
      ${isSelected?'<div style="color:#10b981;font-size:11px;font-weight:700">✓ Diterapkan — klik lagi untuk batal</div>':'<div style="color:#9ca3af;font-size:11px">Klik untuk terapkan</div>'}
    </div>`;

    if (isSelected) {
      if (p.jenis === 'PERSEN') diskon = subtotal * (parseFloat(p.nilai) / 100);
      else if (p.jenis === 'NOMINAL') diskon = Math.min(parseFloat(p.nilai), subtotal);
      else if (p.jenis === 'BOGO' && p.id_menu && cartMenuIds[p.id_menu] >= 2) {
        // gratis 1 item (harga item termurah yang berlaku)
        const menuItem = cart[p.id_menu];
        if (menuItem) diskon = menuItem.harga;
      }
    }
  });

  document.getElementById('hiddenDiskon').value = diskon;

  const promoSection = document.getElementById('promoSection');
  if (i > 0 && hasEligible) {
    promoSection.style.display = '';
    document.getElementById('promoList').innerHTML = promoHtml;
  } else {
    promoSection.style.display = 'none';
  }

  const total = Math.max(0, subtotal - diskon);
  document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
}
</script>

<?php include __DIR__ . '/../../includes/layout_end.php'; ?>
