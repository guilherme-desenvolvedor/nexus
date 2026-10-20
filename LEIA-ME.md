# NEXUS 2.0 — Projeto recuperado

Recuperado em 08/09/2026, depois que a pasta `C:\xampp\htdocs\Nexus2.0` foi
apagada junto com a reinstalação do XAMPP.

## De onde veio cada coisa

| Fonte | O que forneceu |
|---|---|
| Histórico de 2 sessões do Claude Code (`C--xampp-htdocs-Nexus2-0`, ago/2026) | Todo o `Back-end/` e `Back-end/admin/`, o `navbar.php`, e a maior parte das páginas do front-end |
| `Downloads/nexus-*.html` (protótipos estáticos) | Trechos finais das páginas do front-end + o `dashboard.css` do painel admin |
| Banco `nexus_db` **ao vivo** (MySQL do XAMPP novo, ainda rodando) | `DataBase/nexus_db.sql` (dump real de 08/09) |

## Estrutura

```
Nexus2.0/
├── Front-end/
│   ├── navbar.php          ← completo
│   ├── index.php           ← completo (home + pacotes + contato + footer)
│   ├── sobre_nos.php       ← ~95% (ver "Pendências")
│   ├── portifolio.php      ← completo (do protótipo, com navbar.php ligado)
│   ├── Login.php           ← PARCIAL (ver "Pendências")
│   └── imagens/equipe/     ← faltam as fotos (ver arquivo dentro da pasta)
├── Back-end/
│   ├── config.php          ← conexão PDO (nexus_db / root / sem senha)
│   ├── contact.php         ← formulário de contato → grava em `contatos` + envia e-mail
│   ├── mail_config.php     ← credenciais SMTP  ⚠️ ver "Segurança"
│   ├── .htaccess           ← bloqueia acesso direto a config/mail_config
│   └── admin/
│       ├── login.php / logout.php / auth.php
│       ├── dashboard.php / projetos.php / projetos_action.php
│       ├── clientes.php / mensagens.php / configuracoes.php / alterar_senha.php
│       ├── faturamento.php / pagamentos.php / analise.php
│       └── dashboard.css   ← estilo de todo o painel
├── DataBase/
│   ├── nexus_db.sql        ← importe no phpMyAdmin se precisar recriar o banco
│   ├── .htaccess
│   └── migrations/001_cadastro_cpf_varchar.sql
└── PHPMailer-master/       ← baixar do GitHub (ver arquivo dentro da pasta)
```

## Como rodar

Já existe uma cópia funcionando em `C:\Users\joaov\Desktop\htdocs\Nexus2.0`
(o `htdocs` do XAMPP atual). Esta pasta em `Documentos` é a **cópia-mestre/backup** —
edite aqui e copie para o `htdocs`, ou trabalhe direto no `htdocs` e copie de volta.

1. **PHPMailer** já está instalado (`PHPMailer-master/`, extraído do seu
   `Downloads/PHPMailer-master.zip`).
2. O banco `nexus_db` já existe no MySQL. Se algum dia sumir, importe
   `DataBase/nexus_db.sql` pelo phpMyAdmin.
3. Site: `http://localhost/Nexus2.0/Front-end/index.php`.
4. Painel: `http://localhost/Nexus2.0/Back-end/admin/login.php`
   — usuário `admin@nexus.com`. A senha é um hash antigo; se não lembrar,
   rode uma vez um script com
   `password_hash('suasenha', PASSWORD_DEFAULT)` e faça
   `UPDATE admins SET password='<hash>' WHERE email='admin@nexus.com';`.

## Fotos da equipe — OK

As 6 fotos foram colocadas em `Front-end/imagens/equipe/` e renomeadas para os
nomes que o `sobre_nos.php` espera:
`guilherme-pereira-gestor.jpg`, `joao-vitor-dev.jpg`, `arthur-pereira-dev.jpg`,
`bernardo-costa-analista.jpg`, `emanulle-teixeira-marketing.jpg`,
`danillo-fernandes-marketing.jpg`. A Sophia (card 05) segue sem foto —
o card mostra "FOTO AQUI"; é só adicionar `sophia-....jpg` e apontar o `<img>`.

## Intro em scroll — vídeo do símbolo ∞ (guardado pra depois)

A seção de scroll com o símbolo ∞ (transição vídeo→site) foi **tirada do
`sobre_nos.php` em 08/09/2026** pra a apresentação, porque o vídeo ainda não
ficou pronto. A página voltou ao normal: hero "QUEM SOMOS" → "Nossa Origem".

Todo o código (CSS + HTML + JS) + as instruções pra reativar estão em
**`Front-end/_intro-scroll-infinito.md`**. A imagem de referência pro Google
Flow continua em `Front-end/imagens/logo-infinito.png`.

## Reconstruído a partir dos vídeos "nexus 1/2/3" (Vídeos/NVIDIA/Desktop)

- **`Front-end/Login.php`** — refeito por completo: tela de login + cadastro em
  4 passos (Pessoal / Endereço / Acesso / Revisão), com máscara de CPF/telefone/CEP,
  validação de CPF, medidor de força da senha, e busca de endereço por CEP (ViaCEP).
  A lógica PHP (login, cadastro → grava na tabela `cadastro`, sessão `nexus_user`)
  veio do histórico e está intacta. O visual foi remontado a partir dos vídeos —
  bem fiel, mas pode ter pequenas diferenças de espaçamento/cor em relação ao original.
- **`Front-end/sobre_nos.php`** — os 7 membros (Guilherme, João, Arthur, Bernardo,
  Sophia, Emanuelle, Danillo) com cargos, textos e Instagram reais + a seção final
  "Vamos construir juntos?" e o rodapé.

## Carrinho de compras / Pedidos (adicionado em 09/09/2026)

O site agora tem um **carrinho de compras** ligado à conta do usuário.

**Fluxo do cliente**
1. Na home (`index.php`), cada pacote tem o botão **"Adicionar ao carrinho"**.
2. O ícone de carrinho na `navbar.php` mostra a quantidade e leva para `carrinho.php`.
3. Em `carrinho.php` o usuário ajusta quantidades, escreve observações e clica em
   **"Finalizar pedido"** (se não estiver logado, é mandado para o login e volta).
4. O pedido é gravado e o usuário acompanha o **andamento** (linha do tempo:
   Recebido → Em análise → Em produção → Concluído) na mesma página, seção
   **"Meus pedidos"**. Também acessível pelo menu do usuário e pelo perfil (`Login.php`).

O carrinho em si fica na sessão (`$_SESSION['nexus_cart']`); só vira registro no
banco quando o pedido é finalizado. O **preço nunca vem do formulário** — o servidor
sempre resolve pelo catálogo em `Back-end/pacotes.php` (fonte única de preços).

**Painel admin** — nova aba **Pedidos** (`Back-end/admin/pedidos.php`): lista os
pedidos, KPIs, filtro por status e botões para avançar o andamento / cancelar /
reabrir. Ações em `pedidos_action.php`. O link foi adicionado à sidebar de todas as
páginas do painel.

**Banco** — rode **uma vez** `DataBase/migrations/002_pedidos.sql` no phpMyAdmin
(banco `nexus_db` → aba SQL). Cria as tabelas `pedidos` e `pedido_itens`. Enquanto
não rodar, o site continua funcionando (o carrinho monta, mas "Finalizar" avisa que
falhou) e o painel mostra um aviso amigável. O dump `DataBase/nexus_db.sql` já foi
atualizado com essas tabelas para importações novas.

> ⚠️ O `C:\xampp\mysql\bin` está **vazio** nesta máquina — o MySQL não sobe. Antes de
> testar o carrinho, restaure os binários do MySQL/MariaDB no XAMPP.

Arquivos novos: `Front-end/carrinho.php`, `Back-end/pacotes.php`,
`Back-end/admin/pedidos.php`, `Back-end/admin/pedidos_action.php`,
`DataBase/migrations/002_pedidos.sql`.

## Correções nesta rodada

- **Painel admin › Projetos › botão "Excluir"** — não apagava nada. O nome do
  projeto era injetado no `onclick` sem escapar as aspas (`json_encode` sem
  `htmlspecialchars`), o que quebrava o atributo HTML e o clique não fazia nada.
  Corrigido em `Back-end/admin/projetos.php` (agora usa
  `htmlspecialchars(json_encode(...), ENT_QUOTES)`). Testado: cria e apaga de verdade.

## Pontas soltas / melhorias pendentes (verificação de 09/09/2026)

- **Badges de status do painel não tinham cor.** O CSS só tinha `.active/.pending/.done`,
  mas o código usa `ativo/concluido/pausado/aprovado/pendente/cancelado`. Adicionados
  os aliases em pt-BR + os status novos de pedido em `dashboard.css`. ✅ corrigido.
- **`--card-bg` não existia** no `dashboard.css` (usado em `projetos.php`/`pagamentos.php`),
  deixando os cards transparentes. Adicionada a variável. ✅ corrigido.
- **Tabela `cadastro` ainda com CPF/Telefone/CEP como INT** — a migração 001 continua
  sem ser aplicada. O carrinho contorna isso usando o **e-mail** como chave do pedido
  (não o CPF), mas rode `migrations/001_cadastro_cpf_varchar.sql` assim que possível.
- **"Esqueci a senha"** em `Login.php` é um link morto (`href="#"`). Falta implementar.
- **"Lembrar de mim"** no login não faz nada (sem cookie persistente).
- **Sem proteção CSRF** em nenhum formulário (site e painel). Ok para TCC, mas anote.
- **`mail_config.php`** ainda com senha de app do Gmail em texto puro — revogar/rotacionar.
- **Botões `href="#"`** em `portifolio.php` ("Iniciar Projeto", "Ver Sobre Nós") e
  `sobre_nos.php` ("Fale com a Equipe") — apontar para `index.php#contato` / `sobre_nos.php`.
- **`sobre_nos.php`** — card 05 (Sophia) ainda sem foto.
- **MySQL não sobe** (`C:\xampp\mysql\bin` vazio) — restaurar binários do XAMPP.

## Segurança (do que as sessões antigas já tinham apontado)

- `Back-end/mail_config.php` tem uma **senha de app do Gmail em texto puro**
  (`nexus.suporte.26@gmail.com`). Como já esteve exposta, **revogue** em
  https://myaccount.google.com/apppasswords e gere uma nova.
- A tabela `cadastro` no dump está com **CPF/Telefone/CEP como INT** (schema
  antigo — o banco restaurado voltou atrás). Isso trunca CPF/telefone reais.
  Rode `DataBase/migrations/001_cadastro_cpf_varchar.sql` para corrigir
  (faça backup antes).
- `DataBase/.htaccess` bloqueia o dump `.sql` de ser baixado pela web.

## Tabelas do banco

`admins`, `atividades`, `cadastro`, `contatos`, `payments`, `projetos`, `settings`
