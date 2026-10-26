<?php
require __DIR__ . '/config.php';
$mesa = isset($_GET['mesa']) ? (int)$_GET['mesa'] : 0;
$cats = db()->query("SELECT c.* FROM categorias c WHERE c.ativo=1
  AND EXISTS (SELECT 1 FROM produtos p WHERE p.categoria_id=c.id AND p.ativo=1) ORDER BY c.ordem, c.nome")->fetchAll();
$stmt = db()->prepare("SELECT * FROM produtos WHERE categoria_id=? AND ativo=1 ORDER BY ordem, nome");
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#000000">
<title>Cardápio | Zan Sushi</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Shippori+Mincho:wght@600;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="menu">
<header class="topo">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between py-2">
      <img class="logo" src="assets/img/logo.png" alt="Zan Sushi">
      <?php if ($mesa > 0): ?><span class="mesa">Mesa <?= $mesa ?></span><?php endif; ?>
    </div>
    <nav class="chips" aria-label="Categorias">
      <?php foreach ($cats as $i => $c): ?>
        <a class="chip<?= $i === 0 ? ' ativo' : '' ?>" href="#cat-<?= (int)$c['id'] ?>"><?= e($c['nome']) ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
</header>

<main class="container">
<?php if (!$cats): ?>
  <p class="text-center text-secondary py-5">O cardápio está sendo atualizado. Volte em instantes.</p>
<?php endif; ?>
<?php foreach ($cats as $c): $stmt->execute([$c['id']]); ?>
  <section class="secao" id="cat-<?= (int)$c['id'] ?>">
    <h2><?= e($c['nome']) ?></h2>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
    <?php foreach ($stmt as $p): ?>
      <div class="col">
        <button type="button" class="item" data-bs-toggle="modal" data-bs-target="#detalhe"
          data-nome="<?= e($p['nome']) ?>" data-desc="<?= e($p['descricao']) ?>"
          data-preco="<?= e(preco($p['preco'])) ?>" data-img="<?= $p['imagem'] ? 'uploads/' . e($p['imagem']) : '' ?>">
          <?php if ($p['imagem']): ?>
            <img src="uploads/<?= e($p['imagem']) ?>" alt="<?= e($p['nome']) ?>" loading="lazy">
          <?php else: ?><div class="sem-foto">寿司</div><?php endif; ?>
          <div class="corpo">
            <h3 class="nome"><?= e($p['nome']) ?></h3>
            <?php if ($p['descricao']): ?><p class="desc"><?= e($p['descricao']) ?></p><?php endif; ?>
            <?php if ($p['preco'] !== null): ?><div class="preco"><?= e(preco($p['preco'])) ?></div><?php endif; ?>
          </div>
        </button>
      </div>
    <?php endforeach; ?>
    </div>
  </section>
<?php endforeach; ?>
</main>
<footer class="rodape container">Zan Sushi · Guarulhos</footer>

<div class="modal fade" id="detalhe" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header border-0 pb-0"><h3 class="modal-title fs-4" id="m-nome"></h3>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
    <div class="modal-body"><img id="m-img" class="mb-3 d-none" alt=""><p id="m-desc" class="mb-1"></p><div id="m-preco" class="preco-modal"></div></div>
  </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('detalhe').addEventListener('show.bs.modal', ev => {
  const d = ev.relatedTarget.dataset, img = document.getElementById('m-img');
  document.getElementById('m-nome').textContent = d.nome;
  document.getElementById('m-desc').textContent = d.desc;
  document.getElementById('m-preco').textContent = d.preco;
  img.classList.toggle('d-none', !d.img); img.src = d.img; img.alt = d.nome;
});
// Destaca a categoria visível enquanto o cliente rola
const chips = [...document.querySelectorAll('.chip')];
const io = new IntersectionObserver(es => es.forEach(en => {
  if (en.isIntersecting) chips.forEach(c => {
    const on = c.getAttribute('href') === '#' + en.target.id;
    c.classList.toggle('ativo', on);
    if (on) c.scrollIntoView({inline: 'center', block: 'nearest'});
  });
}), {rootMargin: '-130px 0px -70% 0px'});
document.querySelectorAll('.secao').forEach(s => io.observe(s));
</script>
</body>
</html>
