# Documentação de Configuração do Projeto E-Triagem

Este arquivo descreve todas as configurações e dados necessários para o funcionamento do sistema E-Triagem.

## Estrutura da Pasta `config`


## Configuração do Banco de Dados

O sistema utiliza MySQL como banco de dados. Os dados de conexão estão definidos em `database.php`.

### Exemplo de configuração (`database.php`):
```php
$host = 'localhost';
$user = 'usuario';
$password = 'senha';
$dbname = 'e_triagem';
$conn = new mysqli($host, $user, $password, $dbname);
```


 `agendamentos.php`

- O banco de dados deve conter as tabelas necessárias para cadastro, login, dashboard e demais funcionalidades do sistema.
- Recomenda-se criar um usuário específico para o sistema com permissões restritas.

## Configuração do Apache

- O arquivo `apache-config.conf` contém exemplos de configuração para o Apache.
- Certifique-se de que o Apache está instalado e configurado para servir arquivos PHP.
- O diretório raiz do projeto deve ser configurado como `DocumentRoot` no Apache.

## Configuração do PHP

- O PHP deve estar instalado e configurado no servidor.
- Recomenda-se PHP 7.4 ou superior.
- Extensão `mysqli` deve estar habilitada.

## Segurança

- Nunca compartilhe dados sensíveis (usuário/senha) em ambientes públicos.
- Utilize variáveis de ambiente ou arquivos `.env` para armazenar credenciais em produção.
- Mantenha o arquivo `database.php` fora do diretório público sempre que possível.

## Dados Sensíveis

- Os dados de acesso ao banco de dados são definidos em `database.php`.
- Altere as credenciais padrão após a instalação.

## Observações

---

# Documentação de Funcionalidades e Regras do Sistema E-Triagem

## Perfis de Usuário

O sistema possui dois perfis principais:

- **Administrador (admin):**
  - Gerencia usuários, doadores, agendamentos e questionários.
  - Pode editar, excluir e trocar senha de qualquer usuário (exceto a si mesmo).
  - Só pode excluir outro admin se houver pelo menos dois admins cadastrados.
  - Visualiza e gerencia todas as doações e agendamentos.
  - Desbloqueia doadores bloqueados.
  - Edita perguntas do questionário.

- **Usuário Comum (comum):**
  - Pode ativar o perfil de doador.
  - Responde ao questionário de aptidão.
  - Agenda doações se estiver apto.
  - Visualiza seu próprio histórico e status.

## Funcionalidades por Perfil

### Administrador

1. **Gerenciar Usuários**
   - Listar todos os usuários.
   - Editar dados, trocar senha e excluir usuários.
   - Alterar perfil (admin/comum) de outros usuários.
   - Não pode excluir o último admin do sistema.

2. **Gerenciar Doadores**
   - Listar todos os doadores.
   - Editar status, desbloquear doadores bloqueados.
   - Visualizar faltas, bloqueios e aptidão.

3. **Gerenciar Agendamentos e Doações**
   - Visualizar todos os agendamentos futuros e passados.
   - Registrar doação realizada ou falta.
   - Bloquear doador automaticamente após 2 faltas.
   - Desbloquear doador manualmente.

4. **Gerenciar Questionário**
   - Editar perguntas e respostas do questionário de aptidão.
   - Visualizar respostas dos usuários.

### Usuário Comum

1. **Cadastro e Login**
   - Realizar cadastro com dados pessoais.
   - Login seguro com senha criptografada.

2. **Ativar Perfil de Doador**
   - Ativar perfil de doador para acessar funcionalidades de doação.
   - Só pode ativar uma vez.

3. **Responder Questionário**
   - Responder questionário de aptidão.
   - Só pode agendar doação se for considerado apto.
   - Se for considerado inapto, fica bloqueado até liberação do admin.

4. **Agendar Doação**
   - Agendar doação se estiver apto e não bloqueado.
   - Não pode agendar se estiver inapto ou bloqueado.

5. **Visualizar Histórico**
   - Ver histórico de agendamentos, doações e status.

## Regras de Uso e Bloqueios

- **Ativação de Doador:**
  - Usuário comum pode ativar o perfil de doador uma única vez.
  - Após ativação, pode responder o questionário e agendar doações.

- **Questionário de Aptidão:**
  - Deve ser respondido antes do primeiro agendamento.
  - Se todas as respostas estiverem corretas, o usuário é considerado apto.
  - Se houver respostas incorretas, o usuário é considerado inapto e bloqueado para agendamento.
  - Apenas o admin pode desbloquear um usuário inapto.

- **Agendamento de Doação:**
  - Só é permitido para doadores aptos e não bloqueados.
  - Após 2 faltas em agendamentos, o doador é automaticamente bloqueado.
  - O admin pode desbloquear manualmente.

- **Exclusão de Usuários:**
  - Apenas admin pode excluir usuários.
  - Não é possível excluir o próprio usuário logado.
  - Não é possível excluir o último admin do sistema.

- **Troca de Senha:**
  - Admin pode trocar a senha de qualquer usuário.
  - Usuário pode trocar sua própria senha (caso implementado).

## Limite de Idade para Doação

- O administrador pode definir uma idade máxima (idade de corte) para doação de sangue.
- Usuários com idade superior ao limite cadastrado não poderão ser considerados aptos para doação, independentemente do questionário ou desbloqueio manual.
- O cadastro do usuário NÃO é apagado, apenas o status de doador permanece permanentemente inapto.
- O admin pode alterar o limite de idade a qualquer momento.
- O sistema calcula a idade do usuário automaticamente com base na data de nascimento.
- Usuários acima do limite:
  - Não podem ser desbloqueados nem ativados como doadores.
  - Permanecem com status "Inapto por idade".

### Fluxo

1. Admin acessa a tela de gerenciamento e define a idade máxima permitida para doação.
2. Ao ativar perfil de doador ou ao tentar desbloquear, o sistema verifica a idade do usuário.
3. Se a idade for maior que o limite, o usuário é marcado como inapto permanente.
4. O admin pode visualizar quem está inapto por idade na listagem de doadores/usuários.

## Estados dos Usuários

- **Ativo:** Usuário cadastrado e com acesso ao sistema.
- **Doador Ativo:** Usuário ativou o perfil de doador e está apto para doar.
- **Doador Inapto:** Usuário respondeu o questionário com respostas incorretas e está bloqueado para agendamento.
- **Bloqueado por Faltas:** Usuário faltou a 2 agendamentos e está bloqueado até liberação do admin.
- **Desbloqueado:** Admin liberou o usuário para voltar a responder o questionário. Só após ser aprovado no questionário o usuário poderá agendar/doar novamente.

## Fluxo Passo a Passo

1. **Cadastro:** Usuário preenche formulário e cria conta.
2. **Login:** Usuário acessa o sistema com email/CPF e senha.
3. **Ativação de Doador:** Usuário comum ativa o perfil de doador.
4. **Questionário:** Doador responde o questionário de aptidão.
5. **Apto:** Se aprovado, pode agendar doação.
6. **Inapto:** Se reprovado, fica bloqueado até liberação do admin.
7. **Agendamento:** Doador apto agenda doação.
8. **Faltas:** Após 2 faltas, doador é bloqueado automaticamente.
9. **Admin:** Pode desbloquear doador, editar usuários, excluir, trocar senha, editar perguntas, etc.

---

Consulte também o início deste arquivo para requisitos técnicos e instruções de instalação.

---

**Última atualização:** 14 de outubro de 2025
