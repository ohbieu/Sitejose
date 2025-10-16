<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();

// filters
$q = trim($_GET['q'] ?? '');
$genero = $_GET['genero'] ?? '';
$nota = $_GET['nota'] ?? '';

// fetch genres and platforms for filters
$gens = $pdo->query('SELECT * FROM generos')->fetchAll();
$plats = $pdo->query('SELECT * FROM plataformas')->fetchAll();

$params = [':uid' => $user['id']];
$sql = "SELECT j.*, g.nome AS genero, p.nome AS plataforma FROM jogos j LEFT JOIN generos g ON j.genero_id=g.id LEFT JOIN plataformas p ON j.plataforma_id=p.id WHERE j.usuario_id = :uid";
if ($q){ $sql .= " AND j.nome LIKE :q"; $params[':q'] = "%$q%"; }
if ($genero){ $sql .= " AND j.genero_id = :genero"; $params[':genero'] = $genero; }
if ($nota !== ''){ $sql .= " AND j.nota >= :nota"; $params[':nota'] = $nota; }
$sql .= ' ORDER BY j.criado_em DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
javascript:
$games = $stmt->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<h2>Meus Jogos</h2>
<form method="get" class="form-inline">
  <input type="text" name="q" placeholder="Buscar..." value="<?=htmlspecialchars($q)?>">
  <div class="select-wrap">
    <select name="genero">
      <option value="">Todos os gêneros</option>
      <?php foreach($gens as $g): ?>
        <option value="<?=$g['id']?>" <?= $genero==$g['id'] ? 'selected' : '' ?>><?=htmlspecialchars($g['nome'])?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="select-wrap">
    <select name="nota">
      <option value="">Qualquer nota</option>
      <?php for($n=0;$n<=10;$n++): ?>
        <option value="<?=$n?>" <?= $nota=="$n" ? 'selected' : '' ?>>>= <?=$n?></option>
      <?php endfor; ?>
    </select>
  </div>
  <button class="btn" type="submit">Filtrar</button>
</form>

<div class="card-grid">
  <?php if (!$games): ?>
    <p>Nenhum jogo encontrado.</p>
  <?php endif; ?>
  <?php foreach($games as $g): ?>
    <div class="card">
      <?php if ($g['capa'] && file_exists(__DIR__ . '/uploads/' . $g['capa'])): ?>
        <img src="/jogo3/uploads/<?=htmlspecialchars($g['capa'])?>" alt="capa">
      <?php else: ?>
        <img src="/jogo3/css/placeholder.png" alt="sem capa">
      <?php endif; ?>
      <div class="title"><?=htmlspecialchars($g['nome'])?></div>
      <div class="meta">Nota: <?=htmlspecialchars($g['nota'])?> • Status: <?=htmlspecialchars($g['status'])?></div>
      <div class="meta">Gênero: <?=htmlspecialchars($g['genero'] ?? '-')?> • Plataforma: <?=htmlspecialchars($g['plataforma'] ?? '-')?></div>
      <div style="margin-top:8px">
        <a href="/jogo3/detalhes.php?id=<?=$g['id']?>" class="btn">Ver</a>
        <a href="/jogo3/edit_jogo.php?id=<?=$g['id']?>" class="btn">Editar</a>
        <a href="/jogo3/delete_jogo.php?id=<?=$g['id']?>" class="btn" onclick="return confirm('Excluir?')">Excluir</a>
        <a href="#" class="toggle-fav" data-id="<?=$g['id']?>"><?= $g['favorito'] ? '★' : '☆' ?></a>
      </div>
    </div>
  <?php endforeach; ?>
</div>