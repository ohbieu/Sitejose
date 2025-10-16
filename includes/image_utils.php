<?php

function handle_avatar_upload(array $file): array {
    // 1. Verifica se o arquivo foi enviado corretamente
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'Arquivo inválido.'];
    }

    // 2. Limite de tamanho
    $maxBytes = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'error' => 'Arquivo muito grande (máx 2MB).'];
    }

    // 3. Valida se é uma imagem e extrai informações
    $info = @getimagesize($file['tmp_name']);
    if (!$info) {
        return ['success' => false, 'error' => 'Arquivo não é uma imagem válida.'];
    }

    [$width, $height, $type] = $info;

    // 4. Tipos permitidos
    $allowedTypes = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
    ];

    // Suporte condicional ao WEBP
    if (defined('IMAGETYPE_WEBP')) {
        $allowedTypes[IMAGETYPE_WEBP] = 'webp';
    }

    if (!isset($allowedTypes[$type])) {
        return ['success' => false, 'error' => 'Tipo de imagem não suportado.'];
    }

    $ext = $allowedTypes[$type];

    // 5. Criação da imagem a partir do tipo
    switch ($type) {
        case IMAGETYPE_JPEG:
            $src = imagecreatefromjpeg($file['tmp_name']);
            break;
        case IMAGETYPE_PNG:
            $src = imagecreatefrompng($file['tmp_name']);
            break;
        case IMAGETYPE_GIF:
            $src = imagecreatefromgif($file['tmp_name']);
            break;
        case IMAGETYPE_WEBP:
            $src = function_exists('imagecreatefromwebp') ? imagecreatefromwebp($file['tmp_name']) : false;
            break;
        default:
            return ['success' => false, 'error' => 'Formato de imagem não suportado.'];
    }

    if (!$src) {
        return ['success' => false, 'error' => 'Não foi possível processar a imagem.'];
    }

    // 6. Crop centrado e redimensionamento para 300x300
    $size = min($width, $height);
    $src_x = intval(($width - $size) / 2);
    $src_y = intval(($height - $size) / 2);
    $dst_size = 300;

    $dst = imagecreatetruecolor($dst_size, $dst_size);

    // 7. Preservar transparência para PNG e GIF
    if (in_array($ext, ['png', 'gif'])) {
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }

    imagecopyresampled($dst, $src, 0, 0, $src_x, $src_y, $dst_size, $dst_size, $size, $size);

    // 8. Nome do arquivo e caminho final
    $newName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads/avatars/';
    $outPath = $uploadDir . $newName;

    // Garante que o diretório existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // 9. Salva a imagem final
    $saved = match ($ext) {
        'jpg'  => imagejpeg($dst, $outPath, 90),
        'png'  => imagepng($dst, $outPath),
        'gif'  => imagegif($dst, $outPath),
        'webp' => function_exists('imagewebp') ? imagewebp($dst, $outPath, 80) : imagejpeg($dst, $outPath, 90),
        default => false
    };

    // 10. Limpeza
    imagedestroy($src);
    imagedestroy($dst);

    if (!$saved) {
        return ['success' => false, 'error' => 'Falha ao salvar avatar.'];
    }

    return ['success' => true, 'filename' => $newName];
}
