
# Título do Projeto

Uma breve descrição sobre o que esse projeto faz e para quem ele é

# 🚀 GUIA APACHE + PHP - PROJETO QR CODE
## Sistema de Doação de Sangue em PHP

---

## 📋 **CONVERSÃO COMPLETA REALIZADA**

Seu projeto Java foi convertido para **PHP** e está pronto para rodar no **Apache24**!

---

## 🎯 **ARQUIVOS CRIADOS**

### **Estrutura do Projeto PHP:**
```
projeto-qr-code-php/
├── index.php                 # Página inicial
├── login.php                 # Sistema de login
├── cadastro.php              # Cadastro de usuários
├── dashboard.php             # Painel do usuário
├── logout.php                # Logout
├── config/
│   └── database.php          # Configuração do banco
├── assets/
│   ├── css/
│   │   └── style.css         # Estilos CSS
│   └── js/
│       └── script.js         # JavaScript
├── apache-config.conf        # Configuração do Apache
├── instalar-apache.bat       # Script de instalação
└── GUIA-APACHE-PHP.md        # Este guia
```

---

## 🚀 **INSTALAÇÃO AUTOMÁTICA**

### **1. Execute o Script de Instalação**
```cmd
# Navegue para a pasta do projeto
cd "H:\Meu Drive\98-WORKSPACES\02-HEMOFLOW\hemoflow-core\projetos\ProjetoQrCode\projeto-qr-code-php"

# Execute o script de instalação
instalar-apache.bat
```

**O script faz tudo automaticamente:**
- ✅ Verifica Apache, PHP e MySQL
- ✅ Para o Apache
- ✅ Copia projeto para htdocs
- ✅ Configura banco de dados
- ✅ Configura Apache
- ✅ Inicia Apache
- ✅ Testa aplicação
- ✅ Abre navegador

---

## 🔧 **INSTALAÇÃO MANUAL**

### **1. Copiar Projeto**
```cmd
# Copiar para htdocs do Apache
xcopy "projeto-qr-code-php" "C:\Apache24\htdocs\projeto-qr-code-php\" /E /I /Y
```

### **2. Configurar Banco de Dados**
```cmd
# Criar banco
mysql -u root -p"@Ed85962u" -e "CREATE DATABASE IF NOT EXISTS projeto_qr_code CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### **3. Configurar Apache**
Adicione ao arquivo `C:\Apache24\conf\httpd.conf`:
```apache
# Projeto QR Code
Include "C:/Apache24/htdocs/projeto-qr-code-php/apache-config.conf"
```

### **4. Iniciar Apache**
```cmd
net start Apache2.4
```

---

## 🌐 **ACESSAR APLICAÇÃO**

### **URLs de Acesso:**
- **Página Inicial:** http://localhost/
- **Login:** http://localhost/login.php
- **Cadastro:** http://localhost/cadastro.php
- **Dashboard:** http://localhost/dashboard.php

### **Dados de Teste:**
- **CPF:** 123.456.789-00
- **Senha:** senha123

---

## ✨ **FUNCIONALIDADES IMPLEMENTADAS**

### **1. Sistema de Autenticação**
- ✅ Login com CPF e senha
- ✅ Cadastro completo de usuários
- ✅ Validação de CPF
- ✅ Senhas criptografadas
- ✅ Sessões seguras

### **2. Interface Moderna**
- ✅ Design responsivo
- ✅ Ícones Font Awesome
- ✅ Animações CSS
- ✅ Validação JavaScript
- ✅ Máscaras de entrada

### **3. Banco de Dados**
- ✅ Conexão MySQL
- ✅ Tabelas criadas automaticamente
- ✅ Dados de exemplo inseridos
- ✅ Relacionamentos configurados

### **4. Dashboard do Usuário**
- ✅ Informações pessoais
- ✅ Status de doador
- ✅ Questionário de aptidão
- ✅ Agendamentos

---

## 🔧 **COMANDOS ÚTEIS**

### **Gerenciar Apache**
```cmd
# Iniciar Apache
net start Apache2.4

# Parar Apache
net stop Apache2.4

# Reiniciar Apache
net stop Apache2.4 && net start Apache2.4
```

### **Verificar Status**
```cmd
# Verificar se Apache está rodando
netstat -an | findstr :80

# Verificar logs
type C:\Apache24\logs\error.log
```

### **Testar Aplicação**
```cmd
# Testar via curl
curl http://localhost/

# Testar via PowerShell
Invoke-WebRequest -Uri "http://localhost/" -Method Get
```

---

## 📊 **CONFIGURAÇÕES DO APACHE**

### **Arquivo de Configuração:**
```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot "C:/Apache24/htdocs/projeto-qr-code-php"
    
    # Configurações de PHP
    <FilesMatch "\.php$">
        SetHandler application/x-httpd-php
    </FilesMatch>
    
    # Configurações de segurança
    <Directory "C:/Apache24/htdocs/projeto-qr-code-php">
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## 🗄️ **BANCO DE DADOS**

### **Tabelas Criadas:**
- `usuarios` - Dados dos usuários
- `doadores` - Informações dos doadores
- `questionarios` - Questionário de aptidão
- `agendamentos` - Agendamentos de doações

### **Configuração:**
```php
$host = 'localhost';
$db_name = 'projeto_qr_code';
$username = 'root';
$password = '@Ed85962u';
```

---

## 🚨 **TROUBLESHOOTING**

### **Erro: "Apache não inicia"**
```cmd
# Verificar configuração
C:\Apache24\bin\httpd.exe -t

# Verificar logs
type C:\Apache24\logs\error.log
```

### **Erro: "PHP não funciona"**
```cmd
# Verificar se PHP está habilitado
# Editar httpd.conf e adicionar:
LoadModule php_module "C:/Apache24/php/php8apache2_4.dll"
AddType application/x-httpd-php .php
```

### **Erro: "Banco não conecta"**
```cmd
# Verificar MySQL
mysql -u root -p"@Ed85962u" -e "SHOW DATABASES;"

# Verificar configuração em config/database.php
```

### **Erro: "Página não carrega"**
```cmd
# Verificar se Apache está rodando
netstat -an | findstr :80

# Verificar se arquivos estão em htdocs
dir C:\Apache24\htdocs\projeto-qr-code-php\
```

---

## 📝 **LOGS IMPORTANTES**

### **Apache Logs:**
- `C:\Apache24\logs\error.log` - Erros do Apache
- `C:\Apache24\logs\access.log` - Acessos
- `C:\Apache24\logs\projeto-qr-code_error.log` - Erros específicos

### **PHP Logs:**
- `C:\Apache24\logs\php_errors.log` - Erros do PHP

---

## 🎯 **TESTE RÁPIDO**

### **1. Executar Instalação**
```cmd
instalar-apache.bat
```

### **2. Aguardar 10 segundos**

### **3. Acessar**
- http://localhost/

### **4. Testar Login**
- CPF: `123.456.789-00`
- Senha: `senha123`

---

## ✅ **VERIFICAÇÃO FINAL**

### **Checklist de Funcionamento**
- [ ] Apache inicia sem erros
- [ ] PHP funciona corretamente
- [ ] Banco de dados conecta
- [ ] Página inicial carrega
- [ ] Login funciona
- [ ] Cadastro funciona
- [ ] Dashboard carrega

### **Se tudo funcionar:**
```
✅ APLICAÇÃO PHP FUNCIONANDO PERFEITAMENTE!
🌐 URL: http://localhost/
📱 Dados: CPF 123.456.789-00 / Senha senha123
```

---

## 🎉 **PRONTO!**

Sua aplicação **Projeto QR Code** agora está rodando em **PHP no Apache24**!

**Vantagens da versão PHP:**
- ✅ Mais simples de configurar
- ✅ Melhor compatibilidade com Apache
- ✅ Mais fácil de manter
- ✅ Performance adequada
- ✅ Interface moderna

**Comandos para usar:**
- `instalar-apache.bat` - Instalar aplicação
- `net start Apache2.4` - Iniciar Apache
- `net stop Apache2.4` - Parar Apache

**URLs importantes:**
- Aplicação: http://localhost/
- Login: http://localhost/login.php
- Cadastro: http://localhost/cadastro.php

- # Documentação de Configuração do Projeto e-Triagem

Este arquivo descreve todas as configurações e dados necessários para o funcionamento do sistema e-Triagem.

## Estrutura da Pasta `config`

- `database.php`: Script responsável pela conexão com o banco de dados MySQL.
- `README.md`: Documentação das configurações e dados utilizados.

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

- **host**: Endereço do servidor MySQL (geralmente `localhost`)
- **user**: Usuário do banco de dados
- **password**: Senha do usuário
- **dbname**: Nome do banco de dados

### Requisitos do Banco de Dados

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

## Funcionalidades do Aplicativo e-Triagem

O sistema e-Triagem é capaz de:

- 👤 Realizar cadastro de usuários (pacientes, profissionais, administradores)
- 🔑 Gerenciar login e autenticação de usuários
- 📝 Registrar triagem de pacientes
- 📊 Exibir painel/dashboard com estatísticas e informações relevantes
- 📋 Listar, editar e excluir registros de pacientes
- 🛡️ Gerenciar permissões de acesso por tipo de usuário
- 🕓 Registrar histórico de atendimentos
- 📄 Gerar relatórios de triagem e atendimentos
- 🔍 Buscar pacientes por nome, CPF ou outros filtros
- 🚪 Realizar logout seguro
- 💻 Interface web responsiva e intuitiva
- 🗄️ Integração com banco de dados MySQL
- 🔒 Segurança básica de dados e sessões
- 👥 Suporte a múltiplos usuários simultâneos
- 🎨 Customização de estilos via CSS
- ⚡ Scripts interativos via JavaScript
- 🛠️ Fácil instalação e configuração em ambiente Apache/PHP

---

Consulte o arquivo `GUIA-APACHE-PHP.md` para instruções detalhadas de instalação do Apache e PHP.
Para dúvidas ou problemas, consulte a documentação oficial do Apache e PHP.

---

**Última atualização:** 23 de setembro de 2025
