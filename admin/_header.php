<?php
require __DIR__ . '/../config.php';
if (empty($_SESSION['admin'])) { header('Location: login.php'); exit; }
$pagina = basename($_SERVER['SCRIPT_NAME']);
function nav($arq, $txt) { global $pagina; return '<li class="nav-item"><a class="nav-link' . ($pagina === $arq ? ' active' : '') . '" href="' . $arq . '">' . $txt . '</a></li>'; }
?>
<!doctype html>
<html lang="pt-BR"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Painel | Zan Sushi</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Shippori+Mincho:wght@600;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet">
<style>body{font-family:'DM Sans',system-ui,sans-serif;background:#f6f5f3}</style>
</head><body>
<nav class="navbar navbar-expand-md admin-nav no-print">
  <div class="container">
    <a class="navbar-brand" href="produtos.php"><img src="../assets/img/logo.png" alt="Zan Sushi" style="height:36px"></a>
    <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#nv" aria-label="Menu"><span class="navbar-toggler-icon" style="filter:invert(1)"></span></button>
    <div class="collapse navbar-collapse" id="nv">
      <ul class="navbar-nav me-auto"><?= nav('produtos.php', 'Produtos') . nav('categorias.php', 'Categorias') . nav('qrcodes.php', 'QR Codes') ?></ul>
      <a class="nav-link text-white-50 me-3" href="../index.php" target="_blank">Ver cardápio</a>
      <a class="btn btn-sm btn-outline-light" href="logout.php">Sair</a>
    </div>
  </div>
</nav>
<main class="container py-4">
<?php if ($f = flash()): ?><div class="alert alert-<?= $f[1] ?> no-print"><?= e($f[0]) ?></div><?php endif; ?>
