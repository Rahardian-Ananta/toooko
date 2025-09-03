<?php
require __DIR__ . '/../config.php';

$merchantOrderId = $_GET['merchantOrderId'] ?? '';
$resultCode = $_GET['resultCode'] ?? '';
$reference = $_GET['reference'] ?? '';

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Status Pesanan</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#0b1020;color:#f3f4f6;margin:0;padding:0}
    .wrap{max-width:720px;margin:40px auto;padding:24px}
    .card{background:#111827;border:1px solid #1f2937;border-radius:16px;padding:24px}
    .muted{opacity:.8}
    .badge{display:inline-block;padding:6px 10px;border-radius:12px;font-weight:700}
    .ok{background:#065f46;color:#ecfdf5}
    .no{background:#991b1b;color:#fee2e2}
    .wait{background:#7c3aed;color:#ede9fe}
    .btn{display:inline-block;margin-top:16px;background:#34d399;color:#06251a;padding:12px 14px;border-radius:12px;text-decoration:none;font-weight:700}
    .small{font-size:12px;opacity:.75}
    pre{white-space:pre-wrap}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h2>Status Pesanan</h2>
      <p class="muted">Order ID: <strong><?=htmlspecialchars($merchantOrderId)?></strong><br>Reference: <strong><?=htmlspecialchars($reference)?></strong></p>
      <div id="statusBox"><span class="badge wait">Menunggu konfirmasi</span></div>
      <div id="action"></div>
      <p class="small">Catatan: halaman ini akan memeriksa status setiap 3 detik hingga sukses/failed.</p>
      <hr>
      <p class="small">Info redirect (jangan gunakan untuk update status): resultCode=<code><?=htmlspecialchars($resultCode)?></code></p>
    </div>
  </div>

<script>
const orderId = <?= json_encode($merchantOrderId) ?>;
async function poll(){
  const r = await fetch('./order_status.php?orderId=' + encodeURIComponent(orderId));
  const d = await r.json();
  const box = document.getElementById('statusBox');
  const action = document.getElementById('action');
  if(!d.ok){ box.innerHTML = '<span class="badge no">Tidak ditemukan</span>'; return; }
  if(d.status === 'PAID'){
    box.innerHTML = '<span class="badge ok">Sudah dibayar</span>';
    action.innerHTML = '<a class="btn" href="./download.php?token=' + encodeURIComponent(d.downloadToken) + '">Download Produk</a>';
  } else if (d.status === 'FAILED') {
    box.innerHTML = '<span class="badge no">Gagal / Dibatalkan</span>';
  } else {
    box.innerHTML = '<span class="badge wait">Pending</span>';
    setTimeout(poll, 3000);
  }
}
poll();
</script>
</body>
</html>
