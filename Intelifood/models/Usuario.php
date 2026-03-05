<?php
class Usuario extends Model {

    public function criar(string $nome, string $email, string $senha, string $tipo = 'cliente'): int {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO Usuario (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nome, $email, $hash, $tipo]);
        return (int) $this->pdo->lastInsertId();
    }

    public function porId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT id, nome, email, tipo, criado_em FROM Usuario WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function porEmail(string $email): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM Usuario WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function login(string $email, string $senha): ?array {
        $user = $this->porEmail($email);
        if (!$user || !password_verify($senha, $user['senha'])) {
            return null;
        }
        unset($user['senha']);
        return $user;
    }

    public function listar(): array {
        $stmt = $this->pdo->query("SELECT id, nome, email, tipo, criado_em FROM Usuario ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function atualizar(int $id, string $nome, string $email, string $tipo, ?string $novaSenha = null): bool {
        if ($novaSenha !== null && $novaSenha !== '') {
            $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE Usuario SET nome = ?, email = ?, tipo = ?, senha = ? WHERE id = ?");
            return $stmt->execute([$nome, $email, $tipo, $hash, $id]);
        }
        $stmt = $this->pdo->prepare("UPDATE Usuario SET nome = ?, email = ?, tipo = ? WHERE id = ?");
        return $stmt->execute([$nome, $email, $tipo, $id]);
    }
}
