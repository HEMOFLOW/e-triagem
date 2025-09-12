Projeto QR Code — Sistema PHP para gerenciamento de doadores e agendamentos

Passo-a-passo rápido para publicar este projeto no GitHub e como package (Composer/Packagist ou GitHub Packages):

1) Inicializar repositório Git localmente (no Windows `cmd.exe`) e commitar:

	```cmd
	cd C:\Apache24\htdocs\qr_code
	git init
	git add .
	git commit -m "Inicial commit: projeto QR Code"
	```

2) Criar repositório remoto no GitHub (pela web ou `gh` CLI). Com `gh`:

	```cmd
	gh repo create seuusuario/projeto-qr-code --public --source=. --remote=origin
	git push -u origin main
	```

3) Preparar `composer.json` (já incluído) e criar uma tag de versão:

	```cmd
	git tag -a v1.0.0 -m "Versão inicial"
	git push origin v1.0.0
	```

4) Registrar no Packagist (recomendado para PHP):
	- Faça login em https://packagist.org/ e clique em "Submit" > informe a URL do repositório GitHub.
	- Packagist irá sincronizar automaticamente via GitHub (use GitHub OAuth para integração automática).

5) (Opcional) Usar GitHub Packages com Composer:
	- Criar um Personal Access Token (PAT) com `repo` e `write:packages` scopes.
	- Configurar `auth.json` localmente para composer com o token para instalar o pacote privado.

6) Segurança: remova credenciais hard-coded antes de tornar público (especialmente `config/database.php`). Em vez disso, mova para variáveis de ambiente ou `.env` e adicione `.env` ao `.gitignore`.

Se quiser, eu posso:
- Extrair as credenciais de `config/database.php` para variáveis de ambiente e aplicar um `.env` loader mínimo.
- Criar um script `tools/init_db.php` para controlar a criação do schema em dev (em vez de executar `initDatabase()` automaticamente).
