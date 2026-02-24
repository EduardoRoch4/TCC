# CarteiraInvest - Carteira Digital de Investimentos

Plataforma completa de carteira digital inspirada no Investidor10, desenvolvida com PHP, HTML, CSS e JavaScript.

## Funcionalidades

- **Autenticação de usuários**: Login e cadastro com senha criptografada
- **Carteira digital**: Gerencie seus investimentos em um só lugar
- **Catálogo de ativos**: Ações, FIIs, ETFs, BDRs e criptomoedas
- **Dashboard**: Visão geral com **gráficos** (evolução do valor da carteira, distribuição por ativo e por tipo), valor total e resultado
- **Editar ativos comprados**: altere quantidade, preço médio, data e notas na sua carteira
- **Painel administrativo** (apenas administradores): total de usuários, ativos mais comprados, últimos cadastros
- **Comprar/Editar/Remover** investimentos da carteira
- **Interface responsiva** inspirada no Investidor10

## Requisitos

- PHP 7.4+ (recomendado 8.0+)
- MySQL 5.7+ ou MariaDB
- Apache com mod_rewrite (ou servidor compatível)
- Extensão PDO do PHP habilitada

## Instalação

1. **Clone ou copie** o projeto para a pasta do servidor web (ex: `htdocs/Carteira`)

2. **Configure o banco de dados**:
   - Edite `config/database.php` com suas credenciais MySQL
   - Execute o script SQL:

   ```bash
   mysql -u root -p < database/schema.sql
   ```

   Ou importe o arquivo `database/schema.sql` pelo phpMyAdmin.

   Para **gráficos no dashboard** e **painel admin**, execute também:
   ```bash
   mysql -u root -p carteira_digital < database/migration_admin_historico.sql
   ```
   Isso cria a tabela de histórico da carteira e a coluna `is_admin` nos usuários. O primeiro usuário cadastrado vira administrador.

3. **Ajuste as configurações** em `config/config.php`:
   - Altere `SITE_URL` para a URL do seu projeto (ex: `http://localhost/Carteira`)

4. **Acesse** o site pelo navegador.

## Estrutura do Projeto

```
Carteira/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/
│   ├── config.php
│   └── database.php
├── database/
│   └── schema.sql
├── includes/
│   ├── auth.php
│   ├── header.php
│   └── footer.php
├── index.php          # Página inicial
├── login.php
├── register.php
├── logout.php
├── ativos.php         # Catálogo de ativos
├── carteira.php       # Minha carteira (requer login)
├── dashboard.php      # Dashboard com gráficos (requer login)
├── admin/
│   ├── index.php     # Painel administrativo (apenas is_admin)
│   └── includes/
├── database/
│   ├── schema.sql
│   └── migration_admin_historico.sql
└── README.md
```

## Primeiro Acesso

1. Acesse a página inicial
2. Clique em **Cadastrar** e crie sua conta
3. Faça login
4. Navegue até **Ativos** para ver o catálogo
5. Adicione investimentos à sua carteira
6. Acesse **Dashboard** para visão geral

## Segurança

- Senhas armazenadas com `password_hash()` (bcrypt)
- Sessões para autenticação
- Prepared statements para queries SQL (proteção contra SQL injection)
- Validação de entrada nos formulários

## Duplicatas na carteira

Se o mesmo ativo aparecer mais de uma vez (duas linhas em vez de uma com quantidade somada), execute o script de correção:

```bash
mysql -u root -p carteira_digital < database/fix_duplicados_carteira.sql
```

Isso consolida as linhas duplicadas em uma por ativo e garante a chave única `(usuario_id, ativo_id)`.

## Observação

Os preços dos ativos são ilustrativos e vêm de dados estáticos no banco. Para dados em tempo real, seria necessário integrar com uma API de cotações (ex: B3, Alpha Vantage).
