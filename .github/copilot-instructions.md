## Instruções rápidas para agentes de código

Este repositório é um sistema PHP simples (sem framework) para gerenciamento de doadores e agendamentos usando MySQL. As instruções abaixo fornecem o contexto mínimo para ser produtivo imediatamente.

- Estrutura principal
  - `config/database.php` — central: cria/retorna a conexão PDO com MySQL e executa a inicialização automática das tabelas (função `initDatabase()` chamada no final do arquivo). Qualquer alteração no esquema pode ser refletida aqui ou através de scripts em `tools/`.
  - `index.php`, `login.php`, `cadastro.php`, `dashboard.php` — páginas públicas/privadas. Use `session_start()`; o nível de acesso do usuário fica em `$_SESSION['nivel_acesso']`.
  - `tools/` — scripts utilitários executáveis via PHP CLI (ex.: `tools/run_migration.php`, `tools/create_test_user.php`). Preferência: executar via CLI com `php tools/run_migration.php` no diretório do projeto.
  - `export_perguntas.php` — rota que gera CSV de perguntas; protege via sessão (somente `nivel_acesso === 'admin'`). Muitos endpoints esperam que a sessão contenha `usuario_id` e `nivel_acesso`.

- Padrões relevantes detectados no código
  - Sessões: todas as páginas que exigem usuário iniciam com `session_start()` e verificam `$_SESSION['usuario_id']`. Se alterar a autenticação, preserve esse contrato.
  - Banco de dados: o helper `getConnection()` retorna um PDO configurado com `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION` e `FETCH_ASSOC` — trate exceções PDO onde necessário.
  - Convenções de coluna/enum: várias colunas usam ENUM (ex.: `nivel_acesso`, `status` em `agendamentos`, `resposta_inapta` em `perguntas`) — migrações/alterações devem manter os valores permitidos.
  - Proteção de rotas: checagens simples via `if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] !== 'admin') { http_response_code(403); die('Acesso negado.'); }` — siga esse padrão ao criar novas rotas administrativas.

- Workflows operacionais (como executar/depurar)
  - Inicializar DB rapidamente: abrir o site em um servidor Apache+PHP para ativar o `initDatabase()` (ou chamar `php -f config/database.php` se quiser apenas executar a inicialização). Note que as credenciais estão em `config/database.php`.
  - Rodar migração pontual: `php tools/run_migration.php` (ver arquivo que verifica `INFORMATION_SCHEMA` e altera a tabela `usuarios`).
  - Criar usuário de teste: `php tools/create_test_user.php` ou `tools/create_test_user_web.php` para criar via browser (existem scripts utilitários no diretório `tools/`).

- Arquivos e locais importantes para modificações
  - `config/database.php` — alterações no esquema e credenciais.
  - `tools/` — scripts de manutenção e migração; siga o padrão de checar existência antes de alterar (usa INFORMATION_SCHEMA em `run_migration.php`).
  - `export_perguntas.php` — bom exemplo de streaming CSV usando `php://output` e verificação de permissão.
  - `login.php` — padrão de autenticação (consulta por CPF sem máscara, `password_verify`, popula `$_SESSION` com `nivel_acesso`). Reaproveitar esse padrão para novos pontos de login/SSO.

- Exemplos específicos úteis
  - Para obter conexão PDO em qualquer arquivo: `require_once __DIR__ . '/config/database.php'; $pdo = getConnection();`
  - Proteção de rota admin: copiar o bloco do começo de `export_perguntas.php`.
  - Executar migração: `php tools/run_migration.php` (executar no diretório do projeto; exibe logs em stdout).

- Observações de segurança & operação descobertas
  - As credenciais do MySQL (`username`/`password`) estão embutidas em `config/database.php`. Evitar commit de alterações com senhas reais; troque por variáveis de ambiente se for modificar o projeto para produção.
  - `initDatabase()` é executado automaticamente ao incluir `config/database.php`. Para testes locais isso é conveniente, mas em ambientes de produção pode ser indesejado.

- O que NÃO inventar (limites do que é detectável)
  - Não altere o formato das ENUMs existentes sem uma migração; o código depende desses valores (`'usuario' | 'admin'`, `'AGENDADO'|'CONFIRMADO'|'REALIZADO'|'CANCELADO'`, `'SIM'|'NAO'|'NENHUMA'`).
  - Não assumir existência de Composer, testes automatizados ou pipelines CI no repositório (nenhum arquivo encontrado durante análise).

Se precisar, posso ajustar este arquivo com instruções mais detalhadas (ex.: comandos Windows específicos para Apache, detalhes sobre criação de usuários de teste, ou transformar `initDatabase()` para não rodar automaticamente). Qual parte você quer que eu torne mais detalhada? 
