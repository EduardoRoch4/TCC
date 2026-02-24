-- Consolida linhas duplicadas na carteira (mesmo usuário + mesmo ativo em várias linhas)
-- Execute se o mesmo ativo aparece mais de uma vez. Garante uma linha por ativo.

USE carteira_digital;

-- Opção A: Se ainda NÃO existe a chave única, adicione (evita novas duplicatas):
-- ALTER TABLE carteira_investimentos ADD UNIQUE KEY unique_usuario_ativo (usuario_id, ativo_id);

-- Opção B: Se JÁ EXISTEM duplicatas, consolide assim:

-- 1) Atualizar a linha com menor id de cada grupo com quantidade total e preço médio ponderado
UPDATE carteira_investimentos c
INNER JOIN (
    SELECT usuario_id, ativo_id, MIN(id) AS id_manter,
           SUM(quantidade) AS qtd_total,
           SUM(quantidade * preco_medio) / NULLIF(SUM(quantidade), 0) AS preco_medio_novo
    FROM carteira_investimentos
    GROUP BY usuario_id, ativo_id
) t ON c.usuario_id = t.usuario_id AND c.ativo_id = t.ativo_id AND c.id = t.id_manter
SET c.quantidade = t.qtd_total, c.preco_medio = t.preco_medio_novo;

-- 2) Apagar as linhas duplicadas (mantém só a de menor id em cada grupo)
DELETE c1 FROM carteira_investimentos c1
INNER JOIN carteira_investimentos c2
  ON c1.usuario_id = c2.usuario_id AND c1.ativo_id = c2.ativo_id AND c1.id > c2.id;

-- 3) Garantir que não haverá novas duplicatas.
-- Se der erro "Duplicate key", a chave unique_usuario_ativo já existe (pode ignorar).
ALTER TABLE carteira_investimentos ADD UNIQUE KEY unique_usuario_ativo (usuario_id, ativo_id);
