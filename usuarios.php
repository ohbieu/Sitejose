<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$sql = "SELECT u.id,u.nome,u.email,u.avatar,
  COUNT(j.id) AS total_jogos,
  COALESCE(SUM(j.favorito),0) AS favoritos,
  COALESCE(SUM(j.status='Zerado'),0) AS zerados,
  ROUND(AVG(j.nota),1) AS media
  FROM usuarios u
  LEFT JOIN jogos j ON j.usuario_id = u.id
  GROUP BY u.id
  ORDER BY u.nome ASC";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<h2>Usuários cadastrados</h2>
<?php if (!$users): ?>
  <p>Nenhum usuário encontrado.</p>
<?php else: ?>
  <div class="card-grid">
    <?php foreach($users as $u): ?>
      <div class="card">
        <?php if (!empty($u['avatar']) && file_exists(__DIR__ . '/uploads/avatars/' . $u['avatar'])): ?>
          <img src="/jogo3/uploads/avatars/<?=htmlspecialchars($u['avatar'])?>" alt="avatar" style="height:140px;object-fit:cover">
        <?php else: ?>
          <img src="/jogo3/css/placeholder.png" alt="sem avatar" style="height:140px;object-fit:cover">
        <?php endif; ?>
        <div class="title"><?=htmlspecialchars($u['nome'])?></div>
        <div class="meta">E-mail: <?=htmlspecialchars($u['email'])?></div>
        <div class="meta">Total jogos: <?=intval($u['total_jogos'])?> • Zerados: <?=intval($u['zerados'])?></div>
        <div class="meta">Favoritos: <?=intval($u['favoritos'])?> • Média: <?= $u['media'] !== null ? htmlspecialchars($u['media']) : '-' ?></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

</main>
<script src="/jogo3/js/app.js"></script>
</body>
</html>
