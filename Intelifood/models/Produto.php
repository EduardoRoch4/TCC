<?php
class Produto extends Model {

    public function listar(bool $apenasAtivos = false): array {
        $sql = "SELECT * FROM Produtos ORDER BY categoria, nome";
        if ($apenasAtivos) {
            $sql = "SELECT * FROM Produtos WHERE ativo = 1 ORDER BY categoria, nome";
        }
        return $this->pdo->query($sql)->fetchAll();
    }

    public function porId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM Produtos WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function criar(string $nome, string $descricao, float $preco, string $categoria): int {
        $stmt = $this->pdo->prepare("INSERT INTO Produtos (nome, descricao, preco, categoria) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $descricao, $preco, $categoria]);
        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $id, string $nome, string $descricao, float $preco, string $categoria, int $ativo = 1): bool {
        $stmt = $this->pdo->prepare("UPDATE Produtos SET nome = ?, descricao = ?, preco = ?, categoria = ?, ativo = ? WHERE id = ?");
        return $stmt->execute([$nome, $descricao, $preco, $categoria, $ativo, $id]);
    }

    public function excluir(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM Produtos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function categorias(): array {
        $stmt = $this->pdo->query("SELECT DISTINCT categoria FROM Produtos WHERE ativo = 1 ORDER BY categoria");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
