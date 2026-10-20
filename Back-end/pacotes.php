<?php
/**
 * Catálogo de pacotes da NEXUS — fonte única de verdade.
 *
 * Usado pelo carrinho (Front-end/carrinho.php) e pelo painel (Back-end/admin/pedidos.php).
 * O preço NUNCA vem do formulário do cliente: o servidor sempre resolve o preço
 * a partir da chave do pacote aqui definida.
 */

return [
    'starter' => [
        'nome'  => 'Starter',
        'preco' => 800.00,
        'desc'  => 'Landing page de 1 página, design personalizado, responsivo e formulário de contato.',
    ],
    'profissional' => [
        'nome'  => 'Profissional',
        'preco' => 1800.00,
        'desc'  => 'Site com até 5 páginas, agendamento, blog e integração com redes sociais.',
    ],
    'premium' => [
        'nome'  => 'Premium',
        'preco' => 3500.00,
        'desc'  => 'Site com páginas ilimitadas, painel administrativo e sistema robusto e escalável.',
    ],
];
