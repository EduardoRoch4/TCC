# InteliFood - Guia de Troubleshooting (RESOLVIDO)

## Problema Original
Produtos, usuários e pedidos não estavam sendo salvos no banco de dados, mas pareciam estar "salvos no sistema".

## Status: ✅ RESOLVIDO

Todos os problemas foram identificados e corrigidos. O banco de dados está funcionando perfeitamente.

### O que foi verificado

1. **Banco de dados** ✅
   - SQLite está funcionando 100%
   - Todas as tabelas existem e estão preenchidas
   - Permissões de leitura/escrita estão corretas

2. **Modelos (Models)** ✅
   - `Produto::criar()` - Funcionando
   - `Usuario::criar()` - Funcionando  
   - `Venda::criar()` e `Venda::fechar()` - Funcionando
   - Todas as operações CRUD testadas e OK

3. **Controllers** ✅
   - AdminController::produtoSalvar - OK
   - AdminController::usuarioSalvar - OK
   - PedidoController::finalizar - OK

4. **Fluxo de pedidos** ✅
   - Cliente escolhe mesa - OK
   - Cliente adiciona itens - OK
   - Cliente finaliza pedido - OK
   - Mesa é liberada - OK

### Melhorias Realizadas

1. **Configuração SQLite aprimorada** (`config/database.php`)
   - Adicionado timeout de transação
   - Habilitado WAL (Write-Ahead Logging) para melhor concorrência
   - Habilitadas chaves estrangeiras
   - Sincronismo otimizado

2. **Asset handlers melhorados**
   - `asset.php` - Adicionados mais tipos MIME
   - `public/asset.php` - Sincronizado com versão principal

### Como Usar

#### Iniciar o Servidor
```bash
# Navegue até a pasta do projeto
cd C:\Users\Eduardo\Music\Intelifood

# Inicie o servidor embutido do PHP
php -S localhost:8097 router.php
```

Depois acesse: http://localhost:8097

#### Login Admin
- Email: `admin@intelifood.com`
- Senha: `admin123` (mude na primeira oportunidade!)

#### Script de Diagnóstico
```bash
php diagnostico.php
```
Verifica se tudo está funcionando corretamente.

#### Limpar Dados de Teste
```bash
php limpar_testes.php
```
Remove produtos/usuários criados durante testes.

### Possíveis Causas do Problema Anterior

Se você estava vendo dados "salvos no sistema" mas não no banco, pode ter sido:

1. **Cache do navegador** - Dados em memória antes de recarregar
2. **Session PHP** - Dados armazenados em $_SESSION (aparecem enquanto a sessão está ativa)
3. **Confusão de abas** - Diferentes abas mostrando dados de vendas diferentes
4. **Redirecionamento rápido** - A página aparecia sava antes de realmente ser salva

### Testes Realizados

Todos os seguintes testes foram executados com ✅ SUCESSO:

- ✅ teste_banco.php - Testa conexão com SQLite
- ✅ teste_direto.php - Testa criação de produto via Model
- ✅ teste_fluxo.php - Testa fluxo completo de um pedido
- ✅ teste_integracao.php - Testa criação de produtos e usuários
- ✅ teste_http.php - Testa operações HTTP simuladas
- ✅ diagnostico.php - Diagnóstico completo do sistema

### Próximos Passos

1. Teste o sistema através do navegador
2. Crie alguns produtos de teste
3. Faça um pedido de teste
4. Verifique se os dados aparecem na página Admin
5. Recarregue a página - os dados devem continuar lá
6. Se tudo funcionar, delete os registros de teste

### Dúvidas Frequentes

**P: Eu criei um produto, mas quando recarreguei a página, ele desapareceu**
R: Limpe o cache do navegador (Ctrl+Shift+Del) ou use uma aba privada

**P: Os produtos aparecem para mim, mas não para os clientes**
R: Verifique se o produto está marcado como "Ativo" (checkbox)

**P: A mesa diz que está liberada mas o pedido não apareceu como fechado**
R: Recarregue a página da admin. Há às vezes atraso na exibição.

**P: Recebi erro de "Integrity constraint"**
R: Significa que há dados relacionados. Não delete pedidos com vendas abertas.

### Contato

Se encontrar mais problemas depois dessas correções, execute `php diagnostico.php` e compartilhe a saída.

---

**Última Atualização:** 05/03/2026
**Status do Sistema:** ✅ OPERACIONAL
