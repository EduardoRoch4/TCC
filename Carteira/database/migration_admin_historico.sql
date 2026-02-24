-- Migration: Painel Admin + Histórico para gráficos
-- Execute após o schema.sql (ou em banco já existente)

USE carteira_digital;

-- Coluna de administrador (apenas usuários com is_admin = 1 podem acessar /admin/)
-- Se der erro "Duplicate column", a coluna já existe.
ALTER TABLE usuarios ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0;

-- Tabela para evolução do valor da carteira (gráfico no dashboard)
CREATE TABLE IF NOT EXISTS historico_valor_carteira (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_ref DATE NOT NULL,
    valor_total DECIMAL(18, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_data (usuario_id, data_ref)
);

-- Índice opcional (a UNIQUE KEY já cria índice); se der erro de duplicata, ignore.
-- CREATE INDEX idx_historico_usuario_data ON historico_valor_carteira(usuario_id, data_ref);

-- Garantir coluna data_ultima_atualizacao na carteira (se ainda não existir)
-- Descomente a linha abaixo se sua tabela carteira_investimentos só tem data_compra:
-- ALTER TABLE carteira_investimentos ADD COLUMN data_ultima_atualizacao DATE NULL AFTER preco_medio;
-- UPDATE carteira_investimentos SET data_ultima_atualizacao = data_compra WHERE data_ultima_atualizacao IS NULL;

-- Tornar o primeiro usuário administrador (opcional - altere o email para o seu)
-- UPDATE usuarios SET is_admin = 1 WHERE email = 'seu@email.com' LIMIT 1;
-- Ou o primeiro usuário do sistema:
UPDATE usuarios SET is_admin = 1 ORDER BY id ASC LIMIT 1;
