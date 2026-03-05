# InteliFood — Sistema de Restaurantes

Sistema completo para restaurantes em PHP (MVC), com área do cliente (cardápio e pedidos) e painel administrativo.

## Requisitos

- PHP 7.4+ (com PDO MySQL)
- MySQL ou MariaDB

## Instalação

1. **Clone ou copie o projeto** para uma pasta acessível pelo servidor (ex: `htdocs/Intelifood`).

2. **Crie o banco de dados**  
   No MySQL, execute o arquivo `sql/schema.sql`:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
   Ou importe `sql/schema.sql` pelo phpMyAdmin.

3. **Configure a conexão**  
   Edite `config/database.php` e ajuste host, nome do banco, usuário e senha:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'intelifood');
   define('DB_USER', 'root');
   define('DB_PASS', 'sua_senha');
   ```

4. **Crie o usuário administrador**  
   Após importar o schema, execute:
   ```bash
   php criar_admin.php
   ```
   Será criado o login: **admin@intelifood.com** / **admin123**

5. **Configure a URL base**  
   Em `config/config.php`, ajuste `BASE_URL` conforme sua instalação:
   - Se acessar por `http://localhost/Intelifood/public/`, use: `define('BASE_URL', '/Intelifood/public/');`
   - Se usar virtual host na raiz do projeto: `define('BASE_URL', '/');`

6. **Acesse a aplicação**  
   - Cardápio (público): `http://seu-dominio/public/?url=menu/index`  
   - Login admin: `http://seu-dominio/public/?url=auth/login`

## Estrutura (MVC)

- **Models**: `models/` — Usuario, Mesa, Produto, Venda, ContaPagar
- **Controllers**: `controllers/` — AuthController, MenuController, PedidoController, AdminController
- **Views**: `views/` — layout, auth, menu, admin
- **Front controller**: `public/index.php` — roteamento por `?url=controller/action`

## Funcionalidades

### Cliente (usuário normal)
- Ver cardápio
- Cadastrar e fazer login
- Escolher mesa disponível
- Montar pedido (adicionar itens por categoria)
- Ver carrinho, remover itens e enviar pedido

### Administrador
- **Painel**: resumo de pedidos abertos, rendimento e contas a pagar
- **Pedidos**: listar (abertos/fechados/cancelados), ver detalhe, fechar pedido e liberar mesa
- **Mesas**: listar, cadastrar, editar e excluir
- **Produtos**: cadastrar, editar (nome, descrição, preço, categoria, ativo) e excluir
- **Rendimento**: total de vendas fechadas por período
- **Contas a pagar**: cadastrar, editar, marcar como pago e excluir

## Tabelas do banco

- **Usuario** — clientes e admin (nome, email, senha, tipo)
- **Mesas** — número, capacidade, ocupada
- **Produtos** — nome, descrição, preço, categoria, ativo
- **Vendas** — pedidos (usuario_id, mesa_id, total, status)
- **Venda_Itens** — itens de cada venda (produto, quantidade, preço)
- **Contas_Pagar** — descrição, valor, vencimento, pago

## Segurança

- Senhas armazenadas com `password_hash` (bcrypt)
- Área admin restrita por sessão (`tipo = admin`)
- Rotas de pedido e mesa exigem login
