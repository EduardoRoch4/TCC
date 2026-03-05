<?php
class MenuController extends Controller {

    public function index(): void {
        $produtoModel = new Produto();
        $produtos = $produtoModel->listar(true);
        $categorias = [];
        foreach ($produtos as $p) {
            $categorias[$p['categoria']][] = $p;
        }
        $this->view('menu/index', ['categorias' => $categorias]);
    }

    public function mesa(): void {
        $client = $this->getClient();
        // impede que o mesmo cliente (aba) escolha outra mesa enquanto pedido ativo
        $mesaId = (int) ($_SESSION['clientes'][$client]['mesa_id'] ?? 0);
        if ($mesaId) {
            $mesaModel = new Mesa();
            $mesaData = $mesaModel->porId($mesaId);
            if ($mesaData) {
                $_SESSION['clientes'][$client]['mesa_numero'] = $mesaData['numero'];
            }
            $_SESSION['erro'] = 'Você já está usando a mesa ' . ($_SESSION['clientes'][$client]['mesa_numero'] ?? $mesaId) . ". Abra outra aba ou janela para iniciar um novo pedido.";
            $this->redirect(BASE_URL . '?url=menu/pedido&mesa_id=' . $mesaId . '&client=' . $client);
        }

        $mesaModel = new Mesa();
        $mesas = $mesaModel->listar(true);
        $this->view('menu/mesa', ['mesas' => $mesas]);
    }

    public function pedido(): void {
        $client = $this->getClient();
        $mesaId = (int) ($_GET['mesa_id'] ?? 0);
        if (!$mesaId) {
            $this->redirect(BASE_URL . '?url=menu/mesa' . ($client ? '&client=' . $client : ''));
        }
        $mesaModel = new Mesa();
        $mesa = $mesaModel->porId($mesaId);
        $mesaSess = (int) ($_SESSION['clientes'][$client]['mesa_id'] ?? 0);
        if ($mesaSess && $mesaSess !== $mesaId) {
            $_SESSION['erro'] = 'Você já possui a mesa ' . ($_SESSION['clientes'][$client]['mesa_numero'] ?? $mesaSess) . '.';
            $this->redirect(BASE_URL . '?url=menu/pedido&mesa_id=' . $mesaSess . '&client=' . $client);
        }
        // permitir que o próprio cliente acesse sua mesa mesmo que ela esteja marcada como ocupada
        if (!$mesa || ($mesa['ocupada'] && $mesaSess !== $mesaId)) {
            $_SESSION['erro'] = 'Mesa indisponível.';
            $this->redirect(BASE_URL . '?url=menu/mesa' . ($client ? '&client=' . $client : ''));
        }
        $produtoModel = new Produto();
        $produtos = $produtoModel->listar(true);
        $categorias = [];
        foreach ($produtos as $p) {
            $categorias[$p['categoria']][] = $p;
        }
        $this->view('menu/pedido', ['mesa' => $mesa, 'categorias' => $categorias]);
    }

    public function carrinho(): void {
        $client = $this->getClient();
        $vendaId = (int) ($_SESSION['clientes'][$client]['venda_id'] ?? 0);
        $mesaId = (int) ($_SESSION['clientes'][$client]['mesa_id'] ?? 0);
        if (!$vendaId || !$mesaId) {
            $this->redirect(BASE_URL . '?url=menu/mesa' . ($client ? '&client=' . $client : ''));
        }
        $vendaModel = new Venda();
        $venda = $vendaModel->porId($vendaId);
        if (!$venda || $venda['status'] !== 'aberto') {
            $this->clearClient($client);
            $this->redirect(BASE_URL . '?url=menu/mesa' . ($client ? '&client=' . $client : ''));
        }
        $itens = $vendaModel->itens($vendaId);
        $this->view('menu/carrinho', ['venda' => $venda, 'itens' => $itens]);
    }
}
