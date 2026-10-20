<?php
/**
 * Configuração SMTP por variáveis de ambiente.
 * Cadastre estas variáveis nas configurações de ambiente da hospedagem:
 * NEXUS_MAIL_HOST, NEXUS_MAIL_USER e NEXUS_MAIL_PASS.
 * Não coloque senhas reais neste arquivo nem no GitHub.
 */
define('NEXUS_MAIL_HOST', getenv('NEXUS_MAIL_HOST') ?: 'smtp.gmail.com');
define('NEXUS_MAIL_USER', getenv('NEXUS_MAIL_USER') ?: '');
define('NEXUS_MAIL_PASS', getenv('NEXUS_MAIL_PASS') ?: '');
