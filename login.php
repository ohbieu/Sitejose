<?php
require_once __DIR__ . '/includes/db.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    if ($email && $senha){
        $stmt = $pdo->prepare('SELECT id,nome,senha FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute([':email'=>$email]);
        $user = $stmt->fetch();
        if ($user && $senha === $user['senha']){
            // success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            // flash message for UI
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Login realizado com sucesso!'];
            header('Location: /jogo3/index.php'); exit;
        } else {
            $error = 'Credenciais inválidas.';
        }
    } else {
        $error = 'Preencha e-mail e senha.';
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<h2>Entrar</h2>
<?php if (!empty($error)): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
<form method="post">
  <div class="form-row"><label>E-mail</label><input type="email" name="email" required></div>
  <div class="form-row"><label>Senha</label><input type="password" name="senha" required></div>
  <button class="btn" type="submit">Entrar</button>
</form>
</main>
<script src="/jogo3/js/app.js"></script>
</body>
</html>