<?php
class ContaPagar extends Model {

    public function listar(bool $apenasPendentes = false): array {
        $sql = "SELECT * FROM Contas_Pagar ORDER BY data_vencimento ASC";
        if ($apenasPendentes) {
            $sql = "SELECT * FROM Contas_Pagar WHERE pago = 0 ORDER BY data_vencimento ASC";
        }
        return $this->pdo->query($sql)->fetchAll();
    }

    public function porId(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM Contas_Pagar WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function criar(string $descricao, float $valor, string $dataVencimento): int {
        $stmt = $this->pdo->prepare("INSERT INTO Contas_Pagar (descricao, valor, data_vencimento) VALUES (?, ?, ?)");
        $stmt->execute([$descricao, $valor, $dataVencimento]);
        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $id, string $descricao, float $valor, string $dataVencimento): bool {
        $stmt = $this->pdo->prepare("UPDATE Contas_Pagar SET descricao = ?, valor = ?, data_vencimento = ? WHERE id = ?");
        return $stmt->execute([$descricao, $valor, $dataVencimento, $id]);
    }

    public function marcarPago(int $id, ?string $dataPagamento = null): bool {
        $data = $dataPagamento ?? date('Y-m-d');
        $stmt = $this->pdo->prepare("UPDATE Contas_Pagar SET pago = 1, data_pagamento = ? WHERE id = ?");
        return $stmt->execute([$data, $id]);
    }

    public function excluir(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM Contas_Pagar WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function totalPendente(): float {
        $stmt = $this->pdo->query("SELECT COALESCE(SUM(valor), 0) FROM Contas_Pagar WHERE pago = 0");
        return (float) $stmt->fetchColumn();
    }
}
