<?php
/**
 * Configuração do Banco de Dados
 * Projeto QR Code - Sistema de Doação de Sangue
 */

// Carregamento simples de arquivo .env (override de variáveis quando presentes)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (!strpos($line, '=')) continue;
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        if ($name === '') continue;
        // Se a variável ainda não existir em getenv, coloque-a
        if (getenv($name) === false) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

class Database {
    // Valores padrão (servem como fallback)
    private $host = 'localhost';
    private $db_name = 'projeto_qr_code';
    private $username = 'root';
    private $password = '@Ed85962u';
    private $charset = 'utf8mb4';
    private $pdo;

    public function getConnection() {
        $this->pdo = null;
        try {
            // Prioriza variáveis de ambiente: DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_CHARSET
            $host = getenv('DB_HOST') ?: $this->host;
            $dbName = getenv('DB_NAME') ?: $this->db_name;
            $user = getenv('DB_USER') ?: $this->username;
            $pass = getenv('DB_PASS') ?: $this->password;
            $charset = getenv('DB_CHARSET') ?: $this->charset;

            $dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log("Erro de conexão: " . $e->getMessage());
            throw new PDOException("Erro de conexão com o banco de dados");
        }
        
        return $this->pdo;
    }
}

function getConnection() {
    $database = new Database();
    return $database->getConnection();
}

// Função para inicializar o banco de dados (chame manualmente via tools/init_db.php)
function initDatabase() {
    try {
        $pdo = getConnection();
        
        // Criar tabelas se não existirem
        $sql = "
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            data_nascimento DATE NOT NULL,
            cpf VARCHAR(14) NOT NULL UNIQUE,
            telefone VARCHAR(20) NOT NULL,
            email VARCHAR(255),
            senha VARCHAR(255) NOT NULL,
            nivel_acesso ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
            ativo BOOLEAN DEFAULT TRUE,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS doadores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            tipo_sanguineo VARCHAR(3) NOT NULL,
            rh VARCHAR(1) NOT NULL,
            peso DECIMAL(5,2) NOT NULL,
            altura DECIMAL(5,2) NOT NULL,
            apto_para_doacao BOOLEAN DEFAULT TRUE,
            ultima_doacao DATE,
            proxima_doacao DATE,
            observacoes TEXT,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS perguntas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            texto_pergunta TEXT NOT NULL,
            tipo_resposta ENUM('SIM_NAO') NOT NULL DEFAULT 'SIM_NAO',
            resposta_inapta ENUM('SIM', 'NAO', 'NENHUMA') NOT NULL DEFAULT 'SIM',
            ordem INT DEFAULT 0,
            ativo BOOLEAN DEFAULT TRUE,
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS respostas_usuario (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            pergunta_id INT NOT NULL,
            resposta ENUM('SIM', 'NAO'),
            data_resposta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY (pergunta_id) REFERENCES perguntas(id) ON DELETE CASCADE,
            UNIQUE KEY (usuario_id, pergunta_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS agendamentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            data_agendamento DATE NOT NULL,
            hora_agendamento TIME NOT NULL,
            status ENUM('AGENDADO', 'CONFIRMADO', 'REALIZADO', 'CANCELADO') DEFAULT 'AGENDADO',
            observacoes TEXT,
            data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($sql);
        
        // Inserir dados de exemplo se não existirem
        $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
        if ($stmt->fetchColumn() == 0) {
            $sql = "
            INSERT INTO usuarios (nome, data_nascimento, cpf, telefone, email, senha) VALUES
            ('João Silva', '1990-05-15', '123.456.789-00', '(11) 99999-9999', 'joao@email.com', '" . password_hash('senha123', PASSWORD_DEFAULT) . "'),
            ('Maria Santos', '1985-08-22', '987.654.321-00', '(11) 88888-8888', 'maria@email.com', '" . password_hash('senha456', PASSWORD_DEFAULT) . "');
            
            INSERT INTO doadores (usuario_id, tipo_sanguineo, rh, peso, altura, apto_para_doacao) VALUES
            (1, 'A', '+', 70.5, 1.75, TRUE),
            (2, 'B', '+', 65.0, 1.68, TRUE);
            ";
            
            $pdo->exec($sql);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Erro ao inicializar banco: " . $e->getMessage());
        return false;
    }
}

?>
