<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: /jogo3/index.php'); exit; }
$stmt = $pdo->prepare('SELECT j.*, g.nome AS genero, p.nome AS plataforma FROM jogos j LEFT JOIN generos g ON j.genero_id=g.id LEFT JOIN plataformas p ON j.plataforma_id=p.id WHERE j.id=:id AND j.usuario_id=:uid');
$stmt->execute([':id'=>$id,':uid'=>$user['id']]);
$j = $stmt->fetch();
if (!$j){ header('Location: /jogo3/index.php'); exit; }
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<h2><?=htmlspecialchars($j['nome'])?></h2>
<?php if ($j['capa'] && file_exists(__DIR__ . '/uploads/' . $j['capa'])): ?>
  <img src="/jogo3/uploads/<?=htmlspecialchars($j['capa'])?>" style="max-width:400px;height:auto;display:block;margin-bottom:12px">
<?php endif; ?>
<ul>
  <li>Gênero: <?=htmlspecialchars($j['genero'] ?? '-')?></li>
  <li>Plataforma: <?=htmlspecialchars($j['plataforma'] ?? '-')?></li>
  <li>Nota: <?=htmlspecialchars($j['nota'])?></li>
  <li>Status: <?=htmlspecialchars($j['status'])?></li>
  <li>Cadastrado em: <?=htmlspecialchars($j['criado_em'])?></li>
</ul>
<a class="btn" href="/jogo3/index.php">Voltar</a>