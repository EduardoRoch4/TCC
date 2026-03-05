<?php
class Mesa extends Model {

    public function listar(bool $apenasDisponiveis = false): array {
        $sql = "SELECT * FROM Mesas ORDER BY numero";
        if ($apenasDisponiveis) {
            $sql = "SELECT * FROM Mesas WHERE ocupada = 0 ORDER BY numero";
        }
        return $this->pdo->query($sql)->fetchAll();
    }

    public function porId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM Mesas WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function ocupar(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE Mesas SET ocupada = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function liberar(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE Mesas SET ocupada = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function criar(int $numero, int $capacidade = 4): int {
        $stmt = $this->pdo->prepare("INSERT INTO Mesas (numero, capacidade) VALUES (?, ?)");
        $stmt->execute([$numero, $capacidade]);
        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $id, int $numero, int $capacidade): bool {
        $stmt = $this->pdo->prepare("UPDATE Mesas SET numero = ?, capacidade = ? WHERE id = ?");
        return $stmt->execute([$numero, $capacidade, $id]);
    }

    public function excluir(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM Mesas WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
