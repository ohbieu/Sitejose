<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();
header('Content-Type: application/json');
$id = $_POST['id'] ?? null;
if (!$id){ echo json_encode(['success'=>false,'message'=>'id ausente']); exit; }
$stmt = $pdo->prepare('SELECT favorito FROM jogos WHERE id=:id AND usuario_id=:uid');
$stmt->execute([':id'=>$id,':uid'=>$user['id']]);
$j = $stmt->fetch();
if (!$j){ echo json_encode(['success'=>false,'message'=>'jogo não encontrado']); exit; }
$new = $j['favorito'] ? 0 : 1;
$pdo->prepare('UPDATE jogos SET favorito=:f WHERE id=:id AND usuario_id=:uid')->execute([':f'=>$new,':id'=>$id,':uid'=>$user['id']]);
echo json_encode(['success'=>true,'favorito'=>$new]);
