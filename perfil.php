<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();
$stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM jogos WHERE usuario_id = :uid');
$stmt->execute([':uid'=>$user['id']]);
$total = $stmt->fetchColumn();
// stats: zerados and average
$zerados = $pdo->prepare('SELECT COUNT(*) FROM jogos WHERE usuario_id=:uid AND status = "Zerado"');
$zerados->execute([':uid'=>$user['id']]);
$zer = $zerados->fetchColumn();
$media = $pdo->prepare('SELECT AVG(nota) FROM jogos WHERE usuario_id=:uid');
$media->execute([':uid'=>$user['id']]);
$avg = $media->fetchColumn();
// distribution by genre
$distStmt = $pdo->prepare('SELECT g.nome, COUNT(*) as cnt FROM jogos j LEFT JOIN generos g ON j.genero_id=g.id WHERE j.usuario_id = :uid GROUP BY g.nome');
$distStmt->execute([':uid'=>$user['id']]);
$distribution = $distStmt->fetchAll();

// counts of Jogando and Zerado
$statusStmt = $pdo->prepare('SELECT status, COUNT(*) as cnt FROM jogos WHERE usuario_id = :uid GROUP BY status');
$statusStmt->execute([':uid'=>$user['id']]);
$statusCounts = $statusStmt->fetchAll();

// handle avatar upload (validate & resize)
require_once __DIR__ . '/includes/image_utils.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])){
	$res = handle_avatar_upload($_FILES['avatar']);
	if (!$res['success']){
		$uploadError = $res['error'];
	} else {
		$newName = $res['filename'];
		// remove old avatar safely
		$old = $user['avatar'] ?? null;
		if ($old){
			$oldBase = basename($old);
			$oldPath = __DIR__ . '/uploads/avatars/' . $oldBase;
			if (file_exists($oldPath)) @unlink($oldPath);
		}
		// update db
		$ustmt = $pdo->prepare('UPDATE usuarios SET avatar = :a WHERE id = :id');
		$ustmt->execute([':a'=>$newName, ':id'=>$user['id']]);
		// refresh $user and redirect
		$user = current_user();
		header('Location: /jogo3/perfil.php'); exit;
	}
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])){
	// require confirmation (JS confirm on form) and server-side protect
	$uid = $user['id'];
	// remove avatar file
	$avatar = $user['avatar'] ?? null;
	try{
		$pdo->beginTransaction();
		// delete jogos of user
		$d1 = $pdo->prepare('DELETE FROM jogos WHERE usuario_id = :uid');
		$d1->execute([':uid'=>$uid]);
		// delete user
		$d2 = $pdo->prepare('DELETE FROM usuarios WHERE id = :uid');
		$d2->execute([':uid'=>$uid]);
		$pdo->commit();

		if ($avatar){
			$avatarPath = __DIR__ . '/uploads/avatars/' . basename($avatar);
			if (file_exists($avatarPath)) @unlink($avatarPath);
		}

		// prepare flash message but we must restart session to avoid losing flash after destroy
		$flash = ['type'=>'success','message'=>'Conta apagada com sucesso.'];

		// destroy session safely
		$_SESSION = [];
		if (ini_get('session.use_cookies')){
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params['path'], $params['domain'], $params['secure'], $params['httponly']);
		}
		session_destroy();

		// start a fresh session to carry the flash message to the next page
		session_start();
		$_SESSION['flash'] = $flash;
		header('Location: /jogo3/register.php'); exit;
	} catch (Exception $e){
		if ($pdo->inTransaction()) $pdo->rollBack();
		$error = 'Erro ao apagar conta: ' . $e->getMessage();
	}
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<h2>Perfil</h2>
<div style="display:flex;gap:20px;align-items:flex-start">
	<div style="max-width:220px">
		<?php if (!empty($user['avatar']) && file_exists(__DIR__ . '/uploads/avatars/' . $user['avatar'])): ?>
			<img src="/jogo3/uploads/avatars/<?=htmlspecialchars($user['avatar'])?>" style="width:180px;height:180px;object-fit:cover;border-radius:8px;border:1px solid rgba(255,255,255,0.04)">
		<?php else: ?>
			<div style="width:180px;height:180px;background:#081018;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#6b8296">Sem avatar</div>
		<?php endif; ?>
			<form method="post" enctype="multipart/form-data" style="margin-top:10px">
				<div class="form-row">
					<label>Enviar avatar</label>
					<div class="file-input-wrapper">
						<label class="file-input-button" role="button">
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 3v10" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 10l7-7 7 7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 21H3" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							Escolher arquivo
						</label>
									<input class="file-input-hidden" type="file" name="avatar" accept="image/*">
					</div>
				</div>
				<button class="btn" type="submit">Enviar</button>
				<?php if (!empty($uploadError)): ?><div class="error"><?=htmlspecialchars($uploadError)?></div><?php endif; ?>
			</form>
	</div>
	<div>
		<p>Nome: <?=htmlspecialchars($user['nome'])?></p>
		<p>Total de jogos: <?=intval($total)?></p>
		<p>Jogos zerados: <?=intval($zer)?></p>
		<p>Média de notas: <?= $avg !== null ? number_format($avg,1) : '-' ?></p>

		<h3>Estado</h3>
		<ul>
			<?php foreach($statusCounts as $s): ?>
				<li><?=htmlspecialchars($s['status'])?>: <?=intval($s['cnt'])?></li>
			<?php endforeach; ?>
		</ul>

		<!-- Delete account -->
		<div style="margin-top:18px">
			<form method="post" onsubmit="return confirm('Tem certeza que deseja apagar sua conta? Isso removerá todos os seus jogos e não pode ser desfeito.');">
				<input type="hidden" name="delete_account" value="1">
				<button type="submit" class="btn danger">Apagar conta</button>
			</form>
		</div>

		<h3>Distribuição por gênero</h3>
		<ul>
			<?php foreach($distribution as $d): ?>
				<li><?=htmlspecialchars($d['nome'] ?? 'Sem gênero')?>: <?=intval($d['cnt'])?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>