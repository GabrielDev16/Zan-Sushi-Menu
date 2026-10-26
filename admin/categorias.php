<?php
require __DIR__ . '/_header.php';
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $acao = $_POST['acao'] ?? '';
    if ($acao === 'salvar') {
        $nome = trim($_POST['nome'] ?? ''); $ordem = (int)($_POST['ordem'] ?? 0); $ativo = isset($_POST['ativo']) ? 1 : 0; $id = (int)($_POST['id'] ?? 0);
        if ($nome === '') flash('Informe o nome da categoria.', 'danger');
        elseif ($id) { $pdo->prepare('UPDATE categorias SET nome=?, ordem=?, ativo=? WHERE id=?')->execute([$nome, $ordem, $ativo, $id]); flash('Categoria atualizada.'); }
        else { $pdo->prepare('INSERT INTO categorias (nome, ordem, ativo) VALUES (?,?,?)')->execute([$nome, $ordem, $ativo]); flash('Categoria criada.'); }
    } elseif ($acao === 'apagar') {
        $id = (int)$_POST['id'];
        $st = $pdo->prepare('SELECT imagem FROM produtos WHERE categoria_id=?'); $st->execute([$id]);
        foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $img) apagar_imagem($img);
        $pdo->prepare('DELETE FROM categorias WHERE id=?')->execute([$id]); flash('Categoria e seus produtos foram apagados.');
    }
    header('Location: categorias.php'); exit;
}
$edit = null;
if (isset($_GET['editar'])) { $st = $pdo->prepare('SELECT * FROM categorias WHERE id=?'); $st->execute([(int)$_GET['editar']]); $edit = $st->fetch() ?: null; }
$lista = $pdo->query('SELECT c.*, (SELECT COUNT(*) FROM produtos p WHERE p.categoria_id=c.id) qtd FROM categorias c ORDER BY ordem, nome')->fetchAll();
?>
<div class="row g-4">
  <div class="col-lg-4">
    <form method="post" class="card p-3"><?= csrf_field() ?>
      <h2 class="h5"><?= $edit ? 'Editar categoria' : 'Nova categoria' ?></h2>
      <input type="hidden" name="acao" value="salvar"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <label class="form-label" for="n">Nome</label>
      <input id="n" name="nome" class="form-control mb-3" maxlength="80" required value="<?= e($edit['nome'] ?? '') ?>">
      <label class="form-label" for="o">Posição no cardápio</label>
      <input id="o" type="number" name="ordem" class="form-control mb-3" value="<?= (int)($edit['ordem'] ?? 0) ?>">
      <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="ativo" id="a" <?= ($edit['ativo'] ?? 1) ? 'checked' : '' ?>><label class="form-check-label" for="a">Visível para os clientes</label></div>
      <button class="btn btn-zan">Salvar categoria</button>
      <?php if ($edit): ?><a href="categorias.php" class="btn btn-link">Cancelar</a><?php endif; ?>
    </form>
  </div>
  <div class="col-lg-8">
    <div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
      <thead><tr><th>Posição</th><th>Categoria</th><th>Produtos</th><th>Status</th><th></th></tr></thead><tbody>
      <?php foreach ($lista as $c): ?>
        <tr><td><?= (int)$c['ordem'] ?></td><td><?= e($c['nome']) ?></td><td><?= (int)$c['qtd'] ?></td>
          <td><?= $c['ativo'] ? '<span class="badge text-bg-success">Visível</span>' : '<span class="badge text-bg-secondary">Oculta</span>' ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-dark" href="?editar=<?= (int)$c['id'] ?>">Editar</a>
            <form method="post" class="d-inline" onsubmit="return confirm('Apagar \'<?= e(addslashes($c['nome'])) ?>\' e todos os <?= (int)$c['qtd'] ?> produtos dela?')">
              <?= csrf_field() ?><input type="hidden" name="acao" value="apagar"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Apagar</button></form>
          </td></tr>
      <?php endforeach; if (!$lista): ?><tr><td colspan="5" class="text-center text-muted py-4">Crie a primeira categoria ao lado.</td></tr><?php endif; ?>
      </tbody></table></div></div>
  </div>
</div>
<?php require __DIR__ . '/_footer.php'; ?>
