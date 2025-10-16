<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();
$gens = $pdo->query('SELECT * FROM generos')->fetchAll();
$plats = $pdo->query('SELECT * FROM plataformas')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nome = trim($_POST['nome'] ?? '');
    $genero_id = $_POST['genero_id'] ?? null;
    $plataforma_id = $_POST['plataforma_id'] ?? null;
    $nota = $_POST['nota'] ?? 0;
    $status = $_POST['status'] ?? 'Quero jogar';
    $favorito = !empty($_POST['favorito']) ? 1 : 0;

    // handle upload
    $capaName = null;
    if (!empty($_FILES['capa']['name'])){
        $ext = pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION);
        $capaName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest = __DIR__ . '/uploads/' . $capaName;
        if (!move_uploaded_file($_FILES['capa']['tmp_name'], $dest)){
            $error = 'Falha ao enviar imagem.';
        }
    }

    if (!$error ?? true){
        $stmt = $pdo->prepare('INSERT INTO jogos (nome,genero_id,plataforma_id,nota,status,favorito,capa,usuario_id) VALUES (:nome,:genero,:plataforma,:nota,:status,:fav,:capa,:uid)');
        $stmt->execute([
            ':nome'=>$nome,
            ':genero'=>$genero_id ?: null,
            ':plataforma'=>$plataforma_id ?: null,
            ':nota'=>floatval($nota),
            ':status'=>$status,
            ':fav'=>$favorito,
            ':capa'=>$capaName,
            ':uid'=>$user['id'],
        ]);
        header('Location: /jogo3/index.php'); exit;
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<h2>Adicionar Jogo</h2>
<?php if (!empty($error)): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <div class="form-row"><label>Nome</label><input type="text" name="nome" required></div>
  <div class="form-row"><label>Gênero</label>
    <div class="select-wrap">
      <select name="genero_id">
        <option value="">--</option>
        <?php foreach($gens as $g): ?><option value="<?=$g['id']?>"><?=htmlspecialchars($g['nome'])?></option><?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="form-row"><label>Plataforma</label>
    <div class="select-wrap">
      <select name="plataforma_id">
        <option value="">--</option>
        <?php foreach($plats as $p): ?><option value="<?=$p['id']?>"><?=htmlspecialchars($p['nome'])?></option><?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="form-row"><label>Nota (0-10)</label><input type="number" step="0.1" min="0" max="10" name="nota" value="0"></div>
  <div class="form-row"><label>Status</label>
    <div class="select-wrap">
      <select name="status">
        <option>Zerado</option>
        <option>Jogando</option>
        <option selected>Quero jogar</option>
      </select>
    </div>
  </div>
  <div class="form-row"><label>Favorito</label><input type="checkbox" name="favorito" value="1"></div>
  <div class="form-row"><label>Capa</label>
    <div class="file-input-wrapper">
      <label class="file-input-button" role="button">
        <!-- SVG icon: upload -->
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 3v10" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 10l7-7 7 7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 21H3" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Selecionar capa
      </label>
      <input class="file-input-hidden" type="file" name="capa" accept="image/*">
    </div>
  </div>
  <button class="btn" type="submit">Salvar</button>
</form>
</main>
<script src="/jogo3/js/app.js"></script>
</body>
</html>