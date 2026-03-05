<?php
class PedidoController extends Controller {

    // o método existia para impedir que logins de administrador usassem a
    // mesma interface de cliente, mas isso acabou bloqueando testes e
    // forçando o usuário a abrir outra conta. como cada aba já é isolada
    // agora, não faz sentido negar o acesso — o admin pode criar pedidos
    // normalmente ou ser redirecionado para a área de administração.
    private function bloqueiaAdmin(): void {
        // deixamos vazio para não retornar erro JSON; se for necessário
        // algum comportamento futuro, altere aqui.
    }

    public function iniciar(): void {
        $this->bloqueiaAdmin();
        $client = $this->getClient();
        if (!$client) {
            $this->json(['ok' => false, 'msg' => 'Cliente inválido']);
        }
        // já existe pedido para este cliente?
        if (!empty($_SESSION['clientes'][$client]['mesa_id'])) {
            $this->json(['ok' => false, 'msg' => 'Você já iniciou um pedido.']);
        }
        $mesaId = (int) ($_POST['mesa_id'] ?? 0);
        if (!$mesaId) {
            $this->json(['ok' => false, 'msg' => 'Mesa inválida']);
        }
        $mesaModel = new Mesa();
        $mesa = $mesaModel->porId($mesaId);
        if (!$mesa || $mesa['ocupada']) {
            $this->json(['ok' => false, 'msg' => 'Mesa indisponível']);
        }
        $usuarioId = $_SESSION['usuario']['id'] ?? null;
        $vendaModel = new Venda();
        $vendaId = $vendaModel->criar($usuarioId, $mesaId);
        $mesaModel->ocupar($mesaId);
        // gravar especificamente para o cliente/token
        $_SESSION['clientes'][$client] = [
            'venda_id' => $vendaId,
            'mesa_id'  => $mesaId,
            'mesa_numero' => $mesa['numero'],
        ];
        $this->json(['ok' => true, 'venda_id' => $vendaId, 'redirect' => BASE_URL . '?url=menu/pedido&mesa_id=' . $mesaId . '&client=' . $client]);
    }

    public function adicionar(): void {
        $this->bloqueiaAdmin();
        $client = $this->getClient();
        $vendaId = (int) ($_SESSION['clientes'][$client]['venda_id'] ?? 0);
        if (!$vendaId) {
            $this->json(['ok' => false, 'msg' => 'Inicie um pedido escolhendo uma mesa.']);
        }
        $produtoId = (int) ($_POST['produto_id'] ?? 0);
        $quantidade = max(1, (int) ($_POST['quantidade'] ?? 1));
        $produtoModel = new Produto();
        $produto = $produtoModel->porId($produtoId);
        if (!$produto || !$produto['ativo']) {
            $this->json(['ok' => false, 'msg' => 'Produto inválido.']);
        }
        $vendaModel = new Venda();
        $vendaModel->adicionarItem($vendaId, $produtoId, $quantidade, (float) $produto['preco']);
        $venda = $vendaModel->porId($vendaId);
        $this->json(['ok' => true, 'total' => $venda['total'], 'msg' => 'Item adicionado!']);
    }

    public function removerItem(): void {
        $this->bloqueiaAdmin();
        $client = $this->getClient();
        $vendaId = (int) ($_SESSION['clientes'][$client]['venda_id'] ?? 0);
        $itemId = (int) ($_POST['item_id'] ?? 0);
        if (!$vendaId || !$itemId) {
            $this->json(['ok' => false, 'msg' => 'Dados inválidos.']);
        }
        $vendaModel = new Venda();
        $vendaModel->removerItem($itemId, $vendaId);
        $venda = $vendaModel->porId($vendaId);
        $this->json(['ok' => true, 'total' => $venda['total']]);
    }

    public function finalizar(): void {
        // removido redirecionamento automático para área admin; o administrador
        // deve poder usar o cardápio como qualquer cliente e, ao enviar o
        // pedido, retornar ao menu.
        $client = $this->getClient();
        $vendaId = (int) ($_SESSION['clientes'][$client]['venda_id'] ?? 0);
        if (!$vendaId) {
            $this->redirect(BASE_URL . '?url=menu/mesa' . ($client ? '&client=' . $client : ''));
        }
        $vendaModel = new Venda();
        $venda = $vendaModel->porId($vendaId);
        if (!$venda || $venda['status'] !== 'aberto') {
            // limpa dados do cliente
            $this->clearClient($client);
            $this->redirect(BASE_URL . '?url=menu/mesa' . ($client ? '&client=' . $client : ''));
        }
        $vendaModel->fechar($vendaId);
        $mesaModel = new Mesa();
        $mesaModel->liberar((int) $venda['mesa_id']);
        $this->clearClient($client);
        $_SESSION['sucesso'] = 'Pedido enviado! Obrigado.';
        $this->redirect(BASE_URL . '?url=menu/index' . ($client ? '&client=' . $client : ''));
    }
}
