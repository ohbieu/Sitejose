<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/image_utils.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    if ($nome && $email && $senha){
        // handle optional avatar upload (validate & resize)
        $avatarName = null;
        if (!empty($_FILES['avatar']['name'])){
            $res = handle_avatar_upload($_FILES['avatar']);
            if ($res['success']) $avatarName = $res['filename'];
            else $uploadError = $res['error'];
        }
        // plain-text password on purpose for testing
        $stmt = $pdo->prepare('INSERT INTO usuarios (nome,email,senha,avatar) VALUES (:nome,:email,:senha,:avatar)');
        try{
            $stmt->execute([':nome'=>$nome,':email'=>$email,':senha'=>$senha,':avatar'=>$avatarName]);
            // flash message for UI (persist across redirect)
            session_start();
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Registro efetuado com sucesso! Faça login.'];
            header('Location: /jogo3/login.php'); exit;
        } catch (PDOException $e){
            $error = 'Erro ao registrar: ' . $e->getMessage();
        }
    } else {
        $error = 'Preencha todos os campos.';
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<h2>Registrar</h2>
<?php if (!empty($error)): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <div class="form-row"><label>Nome</label><input type="text" name="nome" required></div>
  <div class="form-row"><label>E-mail</label><input type="email" name="email" required></div>
  <div class="form-row"><label>Senha (texto simples)</label><input type="password" name="senha" required></div>
    <div class="form-row"><label>Avatar (opcional)</label><input type="file" name="avatar" accept="image/*"></div>
  <button class="btn" type="submit">Registrar</button>
</form>
</main>
<footer class="site-footer">
    <div class="container">&copy; <?= date('Y') ?> Catálogo de Jogos • Feito para testes</div>
</footer>
<script src="/jogo3/js/app.js"></script>
</body>
</html>