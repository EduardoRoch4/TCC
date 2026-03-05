<?php
class AdminController extends Controller {

    public function login(): void {
        if (!empty($_SESSION['usuario']) && $_SESSION['usuario']['tipo'] === 'admin') {
            $this->redirect(BASE_URL . '?url=admin/index');
        }
        $erro = $_SESSION['erro_admin_login'] ?? null;
        unset($_SESSION['erro_admin_login']);
        $this->view('admin/login', ['erro' => $erro]);
    }

    public function logar(): void {
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        if (!$email || !$senha) {
            $_SESSION['erro_admin_login'] = 'Preencha e-mail e senha.';
            $this->redirect(BASE_URL . '?url=admin/login');
        }
        $usuario = (new Usuario())->login($email, $senha);
        if (!$usuario || $usuario['tipo'] !== 'admin') {
            $_SESSION['erro_admin_login'] = 'Acesso apenas para administradores.';
            $this->redirect(BASE_URL . '?url=admin/login');
        }
        $_SESSION['usuario'] = $usuario;
        $this->redirect(BASE_URL . '?url=admin/index');
    }

    public function logout(): void {
        session_destroy();
        session_start();
        $this->redirect(BASE_URL . '?url=admin/login');
    }

    public function index(): void {
        $this->requerAdmin();
        $vendaModel = new Venda();
        $contasModel = new ContaPagar();
        $pedidosAbertos = $vendaModel->listar('aberto');
        $rendimento = $vendaModel->rendimentoPeriodo(null, null);
        $totalPendente = $contasModel->totalPendente();
        $this->view('admin/index', [
            'pedidosAbertos' => $pedidosAbertos,
            'rendimento' => $rendimento,
            'totalPendente' => $totalPendente,
        ]);
    }

    public function pedidos(): void {
        $this->requerAdmin();
        $vendaModel = new Venda();
        $status = $_GET['status'] ?? null;
        $pedidos = $vendaModel->listar($status);
        $this->view('admin/pedidos', ['pedidos' => $pedidos]);
    }

    public function pedidoDetalhe(): void {
        $this->requerAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $vendaModel = new Venda();
        $venda = $vendaModel->porId($id);
        if (!$venda) {
            $_SESSION['erro'] = 'Pedido não encontrado.';
            $this->redirect(BASE_URL . '?url=admin/pedidos');
        }
        $itens = $vendaModel->itens($id);
        $this->view('admin/pedido-detalhe', ['venda' => $venda, 'itens' => $itens]);
    }

    public function fecharPedido(): void {
        $this->requerAdmin();
        $id = (int) ($_POST['venda_id'] ?? 0);
        $vendaModel = new Venda();
        $venda = $vendaModel->porId($id);
        if (!$venda || $venda['status'] !== 'aberto') {
            $_SESSION['erro'] = 'Pedido inválido ou já fechado.';
            $this->redirect(BASE_URL . '?url=admin/pedidos');
        }
        $vendaModel->fechar($id);
        (new Mesa())->liberar((int) $venda['mesa_id']);
        $_SESSION['sucesso'] = 'Pedido fechado. Mesa liberada.';
        $this->redirect(BASE_URL . '?url=admin/pedidos');
    }

    public function mesas(): void {
        $this->requerAdmin();
        $mesaModel = new Mesa();
        $mesas = $mesaModel->listar();
        $this->view('admin/mesas', ['mesas' => $mesas]);
    }

    public function produtos(): void {
        $this->requerAdmin();
        $produtoModel = new Produto();
        $produtos = $produtoModel->listar();
        $this->view('admin/produtos', ['produtos' => $produtos]);
    }

    public function produtoForm(): void {
        $this->requerAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $produto = null;
        if ($id) {
            $produto = (new Produto())->porId($id);
            if (!$produto) {
                $_SESSION['erro'] = 'Produto não encontrado.';
                $this->redirect(BASE_URL . '?url=admin/produtos');
            }
        }
        $this->view('admin/produto-form', ['produto' => $produto]);
    }

    public function produtoSalvar(): void {
        $this->requerAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $preco = (float) str_replace(',', '.', $_POST['preco'] ?? 0);
        $categoria = trim($_POST['categoria'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        if (!$nome || $preco <= 0 || !$categoria) {
            $_SESSION['erro'] = 'Preencha nome, categoria e preço.';
            $this->redirect(BASE_URL . '?url=admin/produto-form' . ($id ? '&id=' . $id : ''));
        }
        $model = new Produto();
        if ($id) {
            $model->atualizar($id, $nome, $descricao, $preco, $categoria, $ativo);
            $_SESSION['sucesso'] = 'Produto atualizado.';
        } else {
            $model->criar($nome, $descricao, $preco, $categoria);
            $_SESSION['sucesso'] = 'Produto cadastrado.';
        }
        $this->redirect(BASE_URL . '?url=admin/produtos');
    }

    public function produtoExcluir(): void {
        $this->requerAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            (new Produto())->excluir($id);
            $_SESSION['sucesso'] = 'Produto removido.';
        }
        $this->redirect(BASE_URL . '?url=admin/produtos');
    }

    public function rendimento(): void {
        $this->requerAdmin();
        $inicio = $_GET['inicio'] ?? date('Y-m-01');
        $fim = $_GET['fim'] ?? date('Y-m-d');
        $vendaModel = new Venda();
        $rendimento = $vendaModel->rendimentoPeriodo($inicio, $fim);
        $this->view('admin/rendimento', ['rendimento' => $rendimento, 'inicio' => $inicio, 'fim' => $fim]);
    }

    public function contas(): void {
        $this->requerAdmin();
        $contasModel = new ContaPagar();
        $contas = $contasModel->listar();
        $totalPendente = $contasModel->totalPendente();
        $this->view('admin/contas', ['contas' => $contas, 'totalPendente' => $totalPendente]);
    }

    public function contaForm(): void {
        $this->requerAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $conta = null;
        if ($id) {
            $conta = (new ContaPagar())->porId($id);
            if (!$conta) {
                $_SESSION['erro'] = 'Conta não encontrada.';
                $this->redirect(BASE_URL . '?url=admin/contas');
            }
        }
        $this->view('admin/conta-form', ['conta' => $conta]);
    }

    public function contaSalvar(): void {
        $this->requerAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $descricao = trim($_POST['descricao'] ?? '');
        $valor = (float) str_replace(',', '.', $_POST['valor'] ?? 0);
        $dataVencimento = $_POST['data_vencimento'] ?? '';
        if (!$descricao || $valor <= 0 || !$dataVencimento) {
            $_SESSION['erro'] = 'Preencha todos os campos.';
            $this->redirect(BASE_URL . '?url=admin/conta-form' . ($id ? '&id=' . $id : ''));
        }
        $model = new ContaPagar();
        if ($id) {
            $model->atualizar($id, $descricao, $valor, $dataVencimento);
            $_SESSION['sucesso'] = 'Conta atualizada.';
        } else {
            $model->criar($descricao, $valor, $dataVencimento);
            $_SESSION['sucesso'] = 'Conta cadastrada.';
        }
        $this->redirect(BASE_URL . '?url=admin/contas');
    }

    public function contaPagar(): void {
        $this->requerAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            (new ContaPagar())->marcarPago($id);
            $_SESSION['sucesso'] = 'Conta marcada como paga.';
        }
        $this->redirect(BASE_URL . '?url=admin/contas');
    }

    public function contaExcluir(): void {
        $this->requerAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            (new ContaPagar())->excluir($id);
            $_SESSION['sucesso'] = 'Conta removida.';
        }
        $this->redirect(BASE_URL . '?url=admin/contas');
    }

    public function mesaForm(): void {
        $this->requerAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $mesa = null;
        if ($id) {
            $mesa = (new Mesa())->porId($id);
            if (!$mesa) {
                $_SESSION['erro'] = 'Mesa não encontrada.';
                $this->redirect(BASE_URL . '?url=admin/mesas');
            }
        }
        $this->view('admin/mesa-form', ['mesa' => $mesa]);
    }

    public function mesaSalvar(): void {
        $this->requerAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $numero = (int) ($_POST['numero'] ?? 0);
        $capacidade = max(1, (int) ($_POST['capacidade'] ?? 4));
        if ($numero <= 0) {
            $_SESSION['erro'] = 'Número da mesa inválido.';
            $this->redirect(BASE_URL . '?url=admin/mesa-form' . ($id ? '&id=' . $id : ''));
        }
        $model = new Mesa();
        if ($id) {
            $model->atualizar($id, $numero, $capacidade);
            $_SESSION['sucesso'] = 'Mesa atualizada.';
        } else {
            $model->criar($numero, $capacidade);
            $_SESSION['sucesso'] = 'Mesa cadastrada.';
        }
        $this->redirect(BASE_URL . '?url=admin/mesas');
    }

    public function mesaExcluir(): void {
        $this->requerAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            (new Mesa())->excluir($id);
            $_SESSION['sucesso'] = 'Mesa removida.';
        }
        $this->redirect(BASE_URL . '?url=admin/mesas');
    }

    public function usuarios(): void {
        $this->requerAdmin();
        $usuarios = (new Usuario())->listar();
        $this->view('admin/usuarios', ['usuarios' => $usuarios]);
    }

    public function usuarioForm(): void {
        $this->requerAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $usuario = null;
        if ($id) {
            $usuario = (new Usuario())->porId($id);
            if (!$usuario) {
                $_SESSION['erro'] = 'Usuário não encontrado.';
                $this->redirect(BASE_URL . '?url=admin/usuarios');
            }
        }
        $this->view('admin/usuario-form', ['usuario' => $usuario]);
    }

    public function usuarioSalvar(): void {
        $this->requerAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tipo = $_POST['tipo'] === 'admin' ? 'admin' : 'cliente';
        $userModel = new Usuario();
        if ($id) {
            $existente = $userModel->porId($id);
            if (!$existente) {
                $_SESSION['erro'] = 'Usuário não encontrado.';
                $this->redirect(BASE_URL . '?url=admin/usuarios');
            }
            if ($email !== $existente['email'] && $userModel->porEmail($email)) {
                $_SESSION['erro'] = 'E-mail já cadastrado.';
                $this->redirect(BASE_URL . '?url=admin/usuarioForm&id=' . $id);
            }
            $novaSenha = trim($_POST['senha'] ?? '');
            if ($novaSenha !== '' && strlen($novaSenha) < 6) {
                $_SESSION['erro'] = 'Nova senha deve ter no mínimo 6 caracteres.';
                $this->redirect(BASE_URL . '?url=admin/usuarioForm&id=' . $id);
            }
            $userModel->atualizar($id, $nome, $email, $tipo, $novaSenha !== '' ? $novaSenha : null);
            $_SESSION['sucesso'] = 'Usuário atualizado.';
        } else {
            $senha = $_POST['senha'] ?? '';
            if (strlen($senha) < 6) {
                $_SESSION['erro'] = 'Senha deve ter no mínimo 6 caracteres.';
                $this->redirect(BASE_URL . '?url=admin/usuarioForm');
            }
            if ($userModel->porEmail($email)) {
                $_SESSION['erro'] = 'E-mail já cadastrado.';
                $this->redirect(BASE_URL . '?url=admin/usuarioForm');
            }
            $userModel->criar($nome, $email, $senha, $tipo);
            $_SESSION['sucesso'] = 'Usuário cadastrado.';
        }
        $this->redirect(BASE_URL . '?url=admin/usuarios');
    }
}
