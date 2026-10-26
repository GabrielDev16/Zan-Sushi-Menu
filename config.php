<?php
// ====== AJUSTE AQUI ======
const DB_HOST = 'localhost';
const DB_NAME = 'zansushi';
const DB_USER = 'root';
const DB_PASS = '';
// =========================
session_start();

function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function preco($v): string { return $v === null ? '' : 'R$ ' . number_format((float)$v, 2, ',', '.'); }

function csrf_field(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419); exit('Sessão expirada. Volte e recarregue a página.');
    }
}
function flash(?string $msg = null, string $tipo = 'success') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $tipo]; return; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f;
}

/** Valida e salva a imagem enviada. Retorna o nome do arquivo ou null se nada foi enviado. */
function salvar_imagem(array $f): ?string {
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Falha no envio da imagem.');
    if ($f['size'] > 3 * 1024 * 1024) throw new RuntimeException('A imagem deve ter no máximo 3 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext || !getimagesize($f['tmp_name'])) throw new RuntimeException('Use uma imagem JPG, PNG ou WEBP.');
    $nome = bin2hex(random_bytes(12)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], __DIR__ . '/uploads/' . $nome)) throw new RuntimeException('Não foi possível salvar a imagem.');
    return $nome;
}
function apagar_imagem(?string $nome): void {
    if ($nome && preg_match('/^[a-f0-9]{24}\.(jpg|png|webp)$/', $nome)) @unlink(__DIR__ . '/uploads/' . $nome);
}
