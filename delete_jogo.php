<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$user = current_user();
$id = $_GET['id'] ?? null;
if ($id){
    $stmt = $pdo->prepare('SELECT capa FROM jogos WHERE id=:id AND usuario_id=:uid');
    $stmt->execute([':id'=>$id,':uid'=>$user['id']]);
    $j = $stmt->fetch();
    if ($j){
        if ($j['capa'] && file_exists(__DIR__ . '/uploads/' . $j['capa'])){@unlink(__DIR__ . '/uploads/' . $j['capa']);}
        $pdo->prepare('DELETE FROM jogos WHERE id=:id AND usuario_id=:uid')->execute([':id'=>$id,':uid'=>$user['id']]);
    }
}
header('Location: /jogo3/index.php'); exit;
