-- Adiciona valor_aplicado ao histórico para gráfico "Evolução do Patrimônio" (valor aplicado + ganho capital)
USE carteira_digital;

ALTER TABLE historico_valor_carteira ADD COLUMN valor_aplicado DECIMAL(18, 2) NULL;
UPDATE historico_valor_carteira SET valor_aplicado = valor_total WHERE valor_aplicado IS NULL;
