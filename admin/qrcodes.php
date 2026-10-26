<?php
require __DIR__ . '/_header.php';
$esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$padrao = $esquema . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\') . '/';
$url = trim($_GET['url'] ?? $padrao);
$de = max(1, (int)($_GET['de'] ?? 1)); $ate = min(200, max($de, (int)($_GET['ate'] ?? 10)));
$ok = filter_var($url, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $url);
?>
<div class="no-print">
  <h1 class="h4 mb-3">QR Codes das mesas</h1>
  <form method="get" class="card p-3 mb-3"><div class="row g-3 align-items-end">
    <div class="col-md-6"><label class="form-label" for="u">Endereço do cardápio (precisa ser o endereço público do site)</label><input id="u" name="url" class="form-control" value="<?= e($url) ?>"></div>
    <div class="col-6 col-md-2"><label class="form-label" for="d">Mesa inicial</label><input id="d" type="number" name="de" min="1" class="form-control" value="<?= $de ?>"></div>
    <div class="col-6 col-md-2"><label class="form-label" for="a">Mesa final</label><input id="a" type="number" name="ate" min="1" max="200" class="form-control" value="<?= $ate ?>"></div>
    <div class="col-md-2 d-grid"><button class="btn btn-zan">Gerar</button></div></div></form>
  <button class="btn btn-dark mb-3" onclick="window.print()">Imprimir</button>
  <?php if (preg_match('#//(localhost|127\.|192\.168\.)#', $url)): ?><div class="alert alert-warning">Esse endereço só funciona na sua rede local. Antes de imprimir, publique o site e use o endereço público.</div><?php endif; ?>
</div>
<?php if ($ok): ?>
<div class="row g-3">
<?php for ($m = $de; $m <= $ate; $m++): ?>
  <div class="col-6 col-md-4 col-lg-3"><div class="qr-card">
    <img class="logo" src="../assets/img/logo.png" alt="Zan Sushi"><div><div class="qr" data-url="<?= e(rtrim($url, '/') . '/?mesa=' . $m) ?>"></div></div>
    <div class="num">Mesa <?= $m ?></div><small>Aponte a câmera para ver o cardápio</small>
  </div></div>
<?php endfor; ?>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>document.querySelectorAll('.qr').forEach(el => new QRCode(el, {text: el.dataset.url, width: 150, height: 150, correctLevel: QRCode.CorrectLevel.M}));</script>
<?php else: ?><div class="alert alert-danger">Informe um endereço válido começando com http:// ou https://</div><?php endif; ?>
<?php require __DIR__ . '/_footer.php'; ?>
