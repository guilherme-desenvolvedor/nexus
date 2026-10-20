<?php
/**
 * Entrada técnica para o runtime PHP da Vercel.
 * Encaminha solicitações aos arquivos já existentes no NEXUS.
 * Não substitui nem implementa a API de negócio do projeto.
 */
$projectRoot = realpath(__DIR__ . '/..');
if ($projectRoot === false) {
    http_response_code(500);
    exit('Erro ao localizar os arquivos do NEXUS.');
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH) ?: '/';
$requestPath = rawurldecode($requestPath);
if ($requestPath === '/' || $requestPath === '') {
    $target = $projectRoot . '/index.php';
} else {
    $relativePath = ltrim($requestPath, '/');
    // Compatibilidade com links antigos que apontem para páginas do Front-end na raiz.
    $candidate = $projectRoot . '/' . $relativePath;
    if (!is_file($candidate) && strpos($relativePath, '/') === false) {
        $frontCandidate = $projectRoot . '/Front-end/' . $relativePath;
        if (is_file($frontCandidate)) $candidate = $frontCandidate;
    }
    $target = $candidate;
}

$realTarget = realpath($target);
if ($realTarget === false || !str_starts_with($realTarget, $projectRoot . DIRECTORY_SEPARATOR)) {
    http_response_code(404);
    exit('Página não encontrada.');
}
$relative = str_replace('\\', '/', substr($realTarget, strlen($projectRoot) + 1));

// Não permita downloads públicos de dumps SQL, arquivos de configuração ou arquivos ocultos.
$blocked = str_starts_with($relative, 'DataBase/')
    || preg_match('~(^|/)\.~', $relative)
    || preg_match('~\.(sql|env)$~i', $relative)
    || in_array(strtolower($relative), ['back-end/config.php', 'back-end/mail_config.php'], true);
if ($blocked) {
    http_response_code(404);
    exit('Página não encontrada.');
}

if (is_dir($realTarget)) {
    http_response_code(404);
    exit('Página não encontrada.');
}

if (strtolower(pathinfo($realTarget, PATHINFO_EXTENSION)) === 'php') {
    // Preserve o caminho solicitado para que a aplicação continue gerando links relativos corretamente.
    $_SERVER['SCRIPT_FILENAME'] = $realTarget;
    $_SERVER['SCRIPT_NAME'] = $requestPath;
    $_SERVER['PHP_SELF'] = $requestPath;
    chdir(dirname($realTarget));
    require $realTarget;
    exit;
}

// Servir imagens, fontes e demais recursos estáticos presentes no repositório.
$mime = function_exists('mime_content_type') ? mime_content_type($realTarget) : false;
if ($mime) header('Content-Type: ' . $mime);
if (in_array(strtolower(pathinfo($realTarget, PATHINFO_EXTENSION)), ['css','js','png','jpg','jpeg','gif','svg','webp','ico','woff','woff2','ttf','map'], true)) {
    header('Cache-Control: public, max-age=3600');
}
header('Content-Length: ' . filesize($realTarget));
readfile($realTarget);
