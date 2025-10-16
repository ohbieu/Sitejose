<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: /jogo3/index.php'); exit; }
$stmt = $pdo->prepare('SELECT * FROM jogos WHERE id = :id AND usuario_id = :uid');
$stmt->execute([':id'=>$id,':uid'=>$user['id']]);
$j = $stmt->fetch();
if (!$j){ header('Location: /jogo3/index.php'); exit; }
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
    $capaName = $j['capa'];
    if (!empty($_FILES['capa']['name'])){
        $ext = pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION);
        $capaName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest = __DIR__ . '/uploads/' . $capaName;
        if (!move_uploaded_file($_FILES['capa']['tmp_name'], $dest)){
            $error = 'Falha ao enviar imagem.';
        } else {
            // remove old
            if ($j['capa'] && file_exists(__DIR__ . '/uploads/' . $j['capa'])){
                @unlink(__DIR__ . '/uploads/' . $j['capa']);
            }
        }
    }

    if (!$error ?? true){
        $stmt = $pdo->prepare('UPDATE jogos SET nome=:nome,genero_id=:genero,plataforma_id=:plataforma,nota=:nota,status=:status,favorito=:fav,capa=:capa WHERE id=:id AND usuario_id=:uid');
        $stmt->execute([
            ':nome'=>$nome,
            ':genero'=>$genero_id ?: null,
            ':plataforma'=>$plataforma_id ?: null,
            ':nota'=>floatval($nota),
            ':status'=>$status,
            ':fav'=>$favorito,
            ':capa'=>$capaName,
            ':id'=>$id,
            ':uid'=>$user['id']
        ]);
        header('Location: /jogo3/index.php'); exit;
    }
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<h2>Editar Jogo</h2>
<form method="post" enctype="multipart/form-data">
  <div class="form-row"><label>Nome</label><input type="text" name="nome" value="<?=htmlspecialchars($j['nome'])?>" required></div>
  <div class="form-row"><label>Gênero</label>
    <select name="genero_id">
      <option value="">--</option>
      <?php foreach($gens as $g): ?><option value="<?=$g['id']?>" <?= $j['genero_id']==$g['id'] ? 'selected' : '' ?>><?=htmlspecialchars($g['nome'])?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="form-row"><label>Plataforma</label>
    <select name="plataforma_id">
      <option value="">--</option>
      <?php foreach($plats as $p): ?><option value="<?=$p['id']?>" <?= $j['plataforma_id']==$p['id'] ? 'selected' : '' ?>><?=htmlspecialchars($p['nome'])?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="form-row"><label>Nota (0-10)</label><input type="number" step="0.1" min="0" max="10" name="nota" value="<?=htmlspecialchars($j['nota'])?>"></div>
  <div class="form-row"><label>Status</label>
    <select name="status">
      <option <?= $j['status']=='Zerado' ? 'selected' : '' ?>>Zerado</option>
      <option <?= $j['status']=='Jogando' ? 'selected' : '' ?>>Jogando</option>
      <option <?= $j['status']=='Quero jogar' ? 'selected' : '' ?>>Quero jogar</option>
    </select>
  </div>
  <div class="form-row"><label>Favorito</label><input type="checkbox" name="favorito" value="1" <?= $j['favorito'] ? 'checked' : '' ?>></div>
  <div class="form-row"><label>Capa (substituir)</label>
    <div class="file-input-wrapper">
      <label class="file-input-button" role="button">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 3v10" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 10l7-7 7 7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 21H3" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Selecionar nova capa
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