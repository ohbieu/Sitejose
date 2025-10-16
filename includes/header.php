<?php
require_once __DIR__ . '/auth.php';
// ensure session started so flash messages are available
if (session_status() === PHP_SESSION_NONE) session_start();
$user = current_user();
// capture flash (if any) and then remove to implement one-time flash
$flash = $_SESSION['flash'] ?? null;
if (isset($_SESSION['flash'])) unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Catálogo de Jogos</title>
  <link rel="stylesheet" href="/jogo3/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container">
    <div class="logo">
      <h1><a href="/jogo3/index.php">Catálogo de Jogos</a></h1>
    </div>
    <nav>
      <?php if ($user): ?>
        <span class="greeting">Olá, <?= htmlspecialchars($user['nome']) ?>!</span>
        <a href="/jogo3/index.php">Meus Jogos</a>
        <a href="/jogo3/add_jogo.php">Adicionar Jogo</a>
        <a href="/jogo3/usuarios.php">Usuários</a>
        <a href="/jogo3/perfil.php">Perfil</a>
        <?php if (!empty($user['avatar']) && file_exists(__DIR__ . '/../uploads/avatars/' . $user['avatar'])): ?>
          <a href="/jogo3/perfil.php"><img class="avatar" src="/jogo3/uploads/avatars/<?=htmlspecialchars($user['avatar'])?>" alt="avatar"></a>
        <?php else: ?>
          <a href="/jogo3/perfil.php"><span class="avatar" aria-hidden="true"></span></a>
        <?php endif; ?>
        <a href="/jogo3/logout.php" class="btn">Sair</a>
      <?php else: ?>
        <a href="/jogo3/register.php" class="btn">Registrar</a>
        <a href="/jogo3/login.php" class="btn">Entrar</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<?php if ($flash): ?>
  <div id="flash-toast" class="show" data-type="<?=htmlspecialchars($flash['type'])?>"><?=htmlspecialchars($flash['message'])?></div>
<?php endif; ?>
<main class="container">
