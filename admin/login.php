<?php
require __DIR__ . '/../config.php';
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $st = db()->prepare('SELECT * FROM admins WHERE usuario=?'); $st->execute([trim($_POST['usuario'] ?? '')]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['senha'] ?? '', $u['senha_hash'])) {
        session_regenerate_id(true); $_SESSION['admin'] = $u['id'];
        header('Location: produtos.php'); exit;
    }
    usleep(600000); // atrasa tentativas de força bruta
    $erro = 'Usuário ou senha incorretos.';
}
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Entrar | Zan Sushi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet"></head>
<body class="bg-black"><div class="login-box px-3">
  <img src="../assets/img/logo.png" alt="Zan Sushi" class="d-block mx-auto mb-4" style="height:90px">
  <form method="post" class="card p-4"><?= csrf_field() ?>
    <?php if ($erro): ?><div class="alert alert-danger py-2"><?= e($erro) ?></div><?php endif; ?>
    <label class="form-label" for="u">Usuário</label><input id="u" name="usuario" class="form-control mb-3" required autofocus>
    <label class="form-label" for="s">Senha</label><input id="s" type="password" name="senha" class="form-control mb-3" required>
    <button class="btn btn-zan">Entrar</button>
  </form></div></body></html>
