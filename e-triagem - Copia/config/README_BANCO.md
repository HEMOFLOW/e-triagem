# Scripts de Criação do Banco de Dados E-Triagem

Este arquivo contém todos os scripts SQL necessários para criar o banco de dados e as tabelas do sistema E-Triagem, incluindo inserção do usuário admin inicial (Eduardo).

---

## 1. Criação do Banco de Dados

```sql
CREATE DATABASE IF NOT EXISTS e_triagem DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE e_triagem;
```

## 2. Tabela de Usuários

```sql
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cpf VARCHAR(20) NOT NULL UNIQUE,
    data_nascimento DATE NOT NULL,
    perfil ENUM('admin','comum') NOT NULL DEFAULT 'comum',
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## 3. Tabela de Doadores

```sql
CREATE TABLE IF NOT EXISTS doadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    apto_para_doacao TINYINT(1) NOT NULL DEFAULT 1,
    faltas INT NOT NULL DEFAULT 0,
    bloqueado TINYINT(1) NOT NULL DEFAULT 0,
    ultima_doacao DATE DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

### Script para adicionar a coluna em bancos já existentes:

```sql
ALTER TABLE doadores ADD COLUMN ultima_doacao DATE DEFAULT NULL;
```

> A coluna `ultima_doacao` será atualizada automaticamente pelo sistema sempre que uma doação for realizada.

## 4. Tabela de Questionários Respondidos

```sql
CREATE TABLE IF NOT EXISTS questionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    pergunta_1 TINYINT(1),
    pergunta_2 TINYINT(1),
    pergunta_3 TINYINT(1),
    pergunta_4 TINYINT(1),
    pergunta_5 TINYINT(1),
    pergunta_6 TINYINT(1),
    pergunta_7 TINYINT(1),
    pergunta_8 TINYINT(1),
    pergunta_9 TINYINT(1),
    pergunta_10 TINYINT(1),
    observacoes VARCHAR(255),
    aprovado TINYINT(1) NOT NULL,
    data_preenchimento DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

## 5. Tabela de Configuração do Questionário

```sql
CREATE TABLE IF NOT EXISTS questionario_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta VARCHAR(255) NOT NULL,
    resposta_correta TINYINT(1) NOT NULL
);
```

## 6. Tabela de Agendamentos

```sql
CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_agendamento DATE NOT NULL,
    hora_agendamento TIME NOT NULL,
    status ENUM('AGENDADO','REALIZADO','FALTA') NOT NULL DEFAULT 'AGENDADO',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

## 7. Tabela de Configurações Globais

```sql
CREATE TABLE IF NOT EXISTS configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) NOT NULL UNIQUE,
    valor VARCHAR(100) NOT NULL
);

-- Valor padrão para idade máxima do doador
INSERT INTO configuracoes (chave, valor) VALUES ('idade_maxima_doador', '69')
    ON DUPLICATE KEY UPDATE valor = valor;
```

## 8. Usuário Admin Inicial

> Substitua a senha abaixo por um hash gerado pelo PHP (exemplo: password_hash('SENHA', PASSWORD_DEFAULT))

```sql
INSERT INTO usuarios (nome, email, senha, cpf, data_nascimento, perfil)
VALUES ('Eduardo', 'eduardo@email.com', '$2y$10$HASHDAQUI', '000.000.000-00', '1990-01-01', 'admin')
ON DUPLICATE KEY UPDATE perfil = 'admin';
```

- **Importante:** Gere a senha hashada no PHP e substitua `$2y$10$HASHDAQUI` pelo valor real.

---

## Observações
- Execute os scripts na ordem apresentada.
- O sistema utiliza as tabelas acima para todas as funcionalidades descritas na documentação.
- O usuário "Eduardo" será criado como admin já no banco.
- Para outros admins, cadastre normalmente pelo sistema e altere o perfil para 'admin'.
