<?php
require __DIR__ . '/_header.php';
$pdo = db();
$cats = $pdo->query('SELECT id, nome FROM categorias ORDER BY ordem, nome')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $acao = $_POST['acao'] ?? '';
    try {
        if ($acao === 'salvar') {
            $id = (int)($_POST['id'] ?? 0); $cat = (int)($_POST['categoria_id'] ?? 0);
            $nome = trim($_POST['nome'] ?? ''); $desc = trim($_POST['descricao'] ?? '');
            $p = trim($_POST['preco'] ?? ''); $preco = $p === '' ? null : (float)str_replace(',', '.', $p);
            $ordem = (int)($_POST['ordem'] ?? 0); $ativo = isset($_POST['ativo']) ? 1 : 0;
            if ($nome === '' || !$cat) throw new RuntimeException('Informe o nome e a categoria do produto.');
            $nova = salvar_imagem($_FILES['imagem'] ?? []);
            if ($id) {
                $st = $pdo->prepare('SELECT imagem FROM produtos WHERE id=?'); $st->execute([$id]); $antiga = $st->fetchColumn();
                $img = $nova ?? $antiga;
                $pdo->prepare('UPDATE produtos SET categoria_id=?, nome=?, descricao=?, preco=?, imagem=?, ordem=?, ativo=? WHERE id=?')
                    ->execute([$cat, $nome, $desc ?: null, $preco, $img, $ordem, $ativo, $id]);
                if ($nova) apagar_imagem($antiga);
                flash('Produto atualizado.');
            } else {
                $pdo->prepare('INSERT INTO produtos (categoria_id, nome, descricao, preco, imagem, ordem, ativo) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$cat, $nome, $desc ?: null, $preco, $nova, $ordem, $ativo]);
                flash('Produto criado.');
            }
        } elseif ($acao === 'apagar') {
            $id = (int)$_POST['id'];
            $st = $pdo->prepare('SELECT imagem FROM produtos WHERE id=?'); $st->execute([$id]); apagar_imagem($st->fetchColumn());
            $pdo->prepare('DELETE FROM produtos WHERE id=?')->execute([$id]); flash('Produto apagado.');
        }
    } catch (RuntimeException $ex) { flash($ex->getMessage(), 'danger'); header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'produtos.php')); exit; }
    header('Location: produtos.php'); exit;
}

$form = isset($_GET['novo']) || isset($_GET['editar']);
if ($form):
    $p = ['id' => 0, 'categoria_id' => (int)($_GET['cat'] ?? 0), 'nome' => '', 'descricao' => '', 'preco' => '', 'imagem' => '', 'ordem' => 0, 'ativo' => 1];
    if (isset($_GET['editar'])) { $st = $pdo->prepare('SELECT * FROM produtos WHERE id=?'); $st->execute([(int)$_GET['editar']]); $p = $st->fetch() ?: $p; }
?>
<h1 class="h4 mb-3"><?= $p['id'] ? 'Editar produto' : 'Novo produto' ?></h1>
<?php if (!$cats): ?><div class="alert alert-warning">Crie uma <a href="categorias.php">categoria</a> antes de cadastrar produtos.</div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="card p-3 p-md-4" style="max-width:720px"><?= csrf_field() ?>
  <input type="hidden" name="acao" value="salvar"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
  <div class="row g-3">
    <div class="col-md-8"><label class="form-label" for="n">Nome</label><input id="n" name="nome" class="form-control" maxlength="120" required value="<?= e($p['nome']) ?>"></div>
    <div class="col-md-4"><label class="form-label" for="c">Categoria</label>
      <select id="c" name="categoria_id" class="form-select" required>
        <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $c['id'] == $p['categoria_id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option><?php endforeach; ?>
      </select></div>
    <div class="col-12"><label class="form-label" for="d">Descrição breve</label><textarea id="d" name="descricao" class="form-control" rows="3" maxlength="300"><?= e($p['descricao']) ?></textarea></div>
    <div class="col-6 col-md-4"><label class="form-label" for="pr">Preço (opcional)</label><input id="pr" name="preco" class="form-control" inputmode="decimal" placeholder="29,90" value="<?= $p['preco'] !== null && $p['preco'] !== '' ? e(number_format((float)$p['preco'], 2, ',', '')) : '' ?>"></div>
    <div class="col-6 col-md-4"><label class="form-label" for="o">Posição</label><input id="o" type="number" name="ordem" class="form-control" value="<?= (int)$p['ordem'] ?>"></div>
    <div class="col-md-4 d-flex align-items-end"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="ativo" id="a" <?= $p['ativo'] ? 'checked' : '' ?>><label class="form-check-label" for="a">Visível no cardápio</label></div></div>
    <div class="col-12"><label class="form-label" for="i">Imagem (JPG, PNG ou WEBP, até 3 MB)</label>
      <?php if ($p['imagem']): ?><div class="mb-2"><img src="../uploads/<?= e($p['imagem']) ?>" class="thumb" alt="">  <small class="text-muted">Envie outra para substituir.</small></div><?php endif; ?>
      <input id="i" type="file" name="imagem" class="form-control" accept="image/jpeg,image/png,image/webp"></div>
  </div>
  <div class="mt-4"><button class="btn btn-zan">Salvar produto</button> <a href="produtos.php" class="btn btn-link">Cancelar</a></div>
</form>
<?php else:
    $filtro = (int)($_GET['cat'] ?? 0);
    $sql = 'SELECT p.*, c.nome cat FROM produtos p JOIN categorias c ON c.id=p.categoria_id' . ($filtro ? ' WHERE c.id=?' : '') . ' ORDER BY c.ordem, c.nome, p.ordem, p.nome';
    $st = $pdo->prepare($sql); $st->execute($filtro ? [$filtro] : []); $lista = $st->fetchAll();
?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <h1 class="h4 m-0">Produtos</h1>
  <div class="d-flex gap-2">
    <form method="get"><select name="cat" class="form-select" onchange="this.form.submit()" aria-label="Filtrar por categoria">
      <option value="">Todas as categorias</option>
      <?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $c['id'] == $filtro ? 'selected' : '' ?>><?= e($c['nome']) ?></option><?php endforeach; ?></select></form>
    <a class="btn btn-zan" href="?novo=1<?= $filtro ? '&cat=' . $filtro : '' ?>">+ Novo produto</a>
  </div>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
  <thead><tr><th>Foto</th><th>Produto</th><th>Categoria</th><th>Preço</th><th>Status</th><th></th></tr></thead><tbody>
  <?php foreach ($lista as $p): ?>
    <tr><td><?= $p['imagem'] ? '<img class="thumb" src="../uploads/' . e($p['imagem']) . '" alt="">' : '<div class="thumb"></div>' ?></td>
      <td><strong><?= e($p['nome']) ?></strong><br><small class="text-muted"><?= e(mb_strimwidth((string)$p['descricao'], 0, 70, '…')) ?></small></td>
      <td><?= e($p['cat']) ?></td><td><?= e(preco($p['preco'])) ?></td>
      <td><?= $p['ativo'] ? '<span class="badge text-bg-success">Visível</span>' : '<span class="badge text-bg-secondary">Oculto</span>' ?></td>
      <td class="text-end text-nowrap"><a class="btn btn-sm btn-outline-dark" href="?editar=<?= (int)$p['id'] ?>">Editar</a>
        <form method="post" class="d-inline" onsubmit="return confirm('Apagar este produto?')"><?= csrf_field() ?><input type="hidden" name="acao" value="apagar"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="btn btn-sm btn-outline-danger">Apagar</button></form></td></tr>
  <?php endforeach; if (!$lista): ?><tr><td colspan="6" class="text-center text-muted py-4">Nenhum produto ainda. Clique em "Novo produto".</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php endif; require __DIR__ . '/_footer.php'; ?>
