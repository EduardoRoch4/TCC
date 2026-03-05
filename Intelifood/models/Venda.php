<?php
class Venda extends Model {

    public function criar(?int $usuarioId, int $mesaId): int {
        $stmt = $this->pdo->prepare("INSERT INTO Vendas (usuario_id, mesa_id, status) VALUES (?, ?, 'aberto')");
        $stmt->execute([$usuarioId, $mesaId]);
        return (int) $this->pdo->lastInsertId();
    }

    public function porId(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT v.*, m.numero as mesa_numero 
            FROM Vendas v 
            JOIN Mesas m ON m.id = v.mesa_id 
            WHERE v.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function adicionarItem(int $vendaId, int $produtoId, int $quantidade, float $precoUnitario): bool {
        $subtotal = $quantidade * $precoUnitario;
        $stmt = $this->pdo->prepare("INSERT INTO Venda_Itens (venda_id, produto_id, quantidade, preco_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$vendaId, $produtoId, $quantidade, $precoUnitario, $subtotal]);
        $this->atualizarTotal($vendaId);
        return true;
    }

    public function atualizarTotal(int $vendaId): void {
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(subtotal), 0) FROM Venda_Itens WHERE venda_id = ?");
        $stmt->execute([$vendaId]);
        $total = (float) $stmt->fetchColumn();
        $up = $this->pdo->prepare("UPDATE Vendas SET total = ? WHERE id = ?");
        $up->execute([$total, $vendaId]);
    }

    public function itens(int $vendaId): array {
        $stmt = $this->pdo->prepare("
            SELECT vi.*, p.nome as produto_nome 
            FROM Venda_Itens vi 
            JOIN Produtos p ON p.id = vi.produto_id 
            WHERE vi.venda_id = ?
        ");
        $stmt->execute([$vendaId]);
        return $stmt->fetchAll();
    }

    public function removerItem(int $itemId, int $vendaId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM Venda_Itens WHERE id = ? AND venda_id = ?");
        $ok = $stmt->execute([$itemId, $vendaId]);
        if ($ok) $this->atualizarTotal($vendaId);
        return $ok;
    }

    public function listar(?string $status = null): array {
        $sql = "
            SELECT v.*, m.numero as mesa_numero 
            FROM Vendas v 
            JOIN Mesas m ON m.id = v.mesa_id 
        ";
        $params = [];
        if ($status) {
            $sql .= " WHERE v.status = ? ";
            $params[] = $status;
        }
        $sql .= " ORDER BY v.criado_em DESC ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function fechar(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE Vendas SET status = 'fechado' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function cancelar(int $id): bool {
        $v = $this->porId($id);
        if ($v && $v['mesa_id']) {
            (new Mesa())->liberar((int)$v['mesa_id']);
        }
        $stmt = $this->pdo->prepare("UPDATE Vendas SET status = 'cancelado' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function rendimentoPeriodo(?string $inicio, ?string $fim): array {
        $sql = "SELECT COALESCE(SUM(total), 0) as total, COUNT(*) as quantidade FROM Vendas WHERE status = 'fechado'";
        $params = [];
        if ($inicio) { $sql .= " AND DATE(criado_em) >= ?"; $params[] = $inicio; }
        if ($fim)   { $sql .= " AND DATE(criado_em) <= ?"; $params[] = $fim; }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}
