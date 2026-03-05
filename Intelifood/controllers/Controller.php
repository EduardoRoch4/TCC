<?php
abstract class Controller {

    protected function view(string $view, array $dados = []): void {
        extract($dados);
        $arquivo = __DIR__ . '/../views/' . $view . '.php';
        if (file_exists($arquivo)) {
            require $arquivo;
        } else {
            throw new RuntimeException("View não encontrada: $view");
        }
    }

    protected function redirect(string $url, int $code = 302): void {
        header('Location: ' . $url, true, $code);
        exit;
    }

    protected function json($data): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Retorna o token do cliente atual (por aba) recebido em request ou
     * armazenado na sessão. Pode ser vazio.
     */
    protected function getClient(): string {
        // retorna o token enviado por request ou gera um novo.
        // não guardamos o token default em sessão, dessa forma cada aba
        // que acessa sem fornecer um cliente se torna efetivamente nova.
        $token = trim((string) ($_REQUEST['client'] ?? ''));
        if ($token !== '') {
            return preg_replace('/[^a-zA-Z0-9\-]/', '', $token);
        }
        // gera novo cliente aleatório
        return bin2hex(random_bytes(5));
    }

    /**
     * Gera um token aleatório, utilizado pelo front‑end se não existir.
     * Não é usado pelo servidor diretamente.
     */
    protected function generateClientToken(): string {
        return bin2hex(random_bytes(5));
    }

    /**
     * Recupera os dados salvos para um cliente específico na sessão.
     */
    protected function clientData(string $client): array {
        return $_SESSION['clientes'][$client] ?? [];
    }

    /**
     * Seta um valor para um cliente na sessão.
     */
    protected function setClientValue(string $client, string $key, $value): void {
        if (!isset($_SESSION['clientes'][$client])) {
            $_SESSION['clientes'][$client] = [];
        }
        $_SESSION['clientes'][$client][$key] = $value;
    }

    /**
     * Limpa todos os dados do cliente especificado.
     */
    protected function clearClient(string $client): void {
        unset($_SESSION['clientes'][$client]);
    }


    protected function requerAdmin(): void {
        if (empty($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'admin') {
            $this->redirect(BASE_URL . '?url=admin/login');
        }
    }

    protected function requerLogin(): void {
        if (empty($_SESSION['usuario'])) {
            $this->redirect(BASE_URL . '?url=auth/login');
        }
    }
}
