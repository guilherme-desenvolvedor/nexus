# NEXUS — primeiro teste na Vercel

Esta versão mantém o código original e acrescenta uma camada técnica de roteamento para testar o runtime comunitário `vercel-php`.

## Publicação
1. Envie o conteúdo desta pasta ao repositório GitHub conectado à Vercel (a raiz do projeto deve conter `vercel.json`, `index.php`, `api/`, `Front-end/` e `Back-end/`).
2. Em Vercel, deixe **Root Directory** na raiz do repositório.
3. Faça um novo deploy.
4. Acesse o domínio. A raiz redireciona para `/Front-end/index.php`.

## Limitações deste teste
- O runtime PHP é comunitário (`vercel-php@0.9.0`), não um runtime PHP oficial da Vercel.
- O MySQL configurado como `localhost`/`root` é o banco local e não estará disponível na Vercel. Portanto login, cadastro, carrinho com pedidos, painel e contato que dependam do banco podem falhar até configurar um MySQL externo.
- Sessões e persistência de arquivos precisam de validação no ambiente serverless.
- O roteador bloqueia acesso HTTP direto à pasta `DataBase/` e aos arquivos de configuração do back-end, mas isso não substitui uma revisão de segurança.
- `Back-end/mail_config.php` foi ajustado para ler `NEXUS_MAIL_HOST`, `NEXUS_MAIL_USER` e `NEXUS_MAIL_PASS` do ambiente, sem guardar a senha no código. Cadastre essas variáveis na Vercel para habilitar o e-mail. A senha de aplicativo que estava no ZIP original deve ser revogada/rotacionada, pois já estava escrita em texto puro.

Esta versão é para primeiro teste de roteamento e renderização, não uma confirmação de que todas as funcionalidades estão operacionais na Vercel.
