<?php
/**
 * Configuração do Banco de Dados
 * Projeto QR Code - Sistema de Doação de Sangue
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'projeto_qr_code';
    private $username = 'unifaccamp';
    private $password = 'academico';
    private $charset = 'utf8mb4';
    private $pdo;

    public function getConnection() {
        $this->pdo = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
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

// Função para inicializar o banco de dados
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

        CREATE TABLE IF NOT EXISTS questionarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            pergunta_1 BOOLEAN NOT NULL,
            pergunta_2 BOOLEAN NOT NULL,
            pergunta_3 BOOLEAN NOT NULL,
            pergunta_4 BOOLEAN NOT NULL,
            pergunta_5 BOOLEAN NOT NULL,
            pergunta_6 BOOLEAN NOT NULL,
            pergunta_7 BOOLEAN NOT NULL,
            pergunta_8 BOOLEAN NOT NULL,
            pergunta_9 BOOLEAN NOT NULL,
            pergunta_10 BOOLEAN NOT NULL,
            observacoes TEXT,
            aprovado BOOLEAN NOT NULL,
            data_preenchimento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
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

// Inicializar banco automaticamente
initDatabase();
?>

