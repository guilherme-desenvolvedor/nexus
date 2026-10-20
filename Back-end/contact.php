<?php
require __DIR__ . "/config.php";
require __DIR__ . "/mail_config.php";

use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . "/../PHPMailer-master/src/Exception.php";
require __DIR__ . "/../PHPMailer-master/src/PHPMailer.php";
require __DIR__ . "/../PHPMailer-master/src/SMTP.php";

/* Volta para a seção de contato do site; o popup lê ?status= e ?motivo= */
function voltar(string $status, string $motivo = ""): void {
    $url = "../Front-end/index.php?status=" . $status;
    if ($motivo !== "") $url .= "&motivo=" . $motivo;
    header("Location: " . $url . "#contato");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    voltar("error", "campos");
}

$nome = trim($_POST["nome"] ?? "");
$email = trim($_POST["email"] ?? "");
$pacote = trim($_POST["pacote"] ?? "");
$mensagem = trim($_POST["mensagem"] ?? "");

if (!$nome || !$email || !$pacote || !$mensagem) {
    voltar("error", "campos");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    voltar("error", "email");
}

/* SALVAR NO BANCO — se falhar, ainda tentamos o e-mail */
$salvou = false;
try {
    $stmt = $pdo->prepare("
        INSERT INTO contatos (nome, email, pacote, mensagem)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$nome, $email, $pacote, $mensagem]);
    $salvou = true;
} catch (Throwable $e) {
    /* segue para o envio do e-mail */
}

/* ENVIAR EMAIL */
$enviou = false;
try {
    $mail = new PHPMailer(true);
    $mail->CharSet = "UTF-8";

    $mail->isSMTP();
    $mail->Host       = NEXUS_MAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = NEXUS_MAIL_USER;
    $mail->Password   = NEXUS_MAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = NEXUS_MAIL_PORT;

    $mail->setFrom(NEXUS_MAIL_USER, "Site NEXUS");
    $mail->addAddress(NEXUS_MAIL_USER);
    $mail->addReplyTo($email, $nome);

    $mail->isHTML(true);
    $mail->Subject = "Novo contato pelo site NEXUS";

    $mail->Body = "
        <h3>Novo contato recebido</h3>
        <p><strong>Nome:</strong> " . htmlspecialchars($nome) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
        <p><strong>Pacote:</strong> " . htmlspecialchars($pacote) . "</p>
        <p><strong>Mensagem:</strong><br>" . nl2br(htmlspecialchars($mensagem)) . "</p>
    ";

    $mail->send();
    $enviou = true;
} catch (Throwable $e) {
    /* tratado abaixo */
}

/* Só dizemos "enviada" se a equipe realmente recebeu: e-mail OU painel de mensagens */
if ($enviou || $salvou) {
    voltar("success");
}
voltar("error", "envio");
