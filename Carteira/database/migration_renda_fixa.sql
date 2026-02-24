-- Renda Fixa: tabela e tipos de título no Brasil
USE carteira_digital;

CREATE TABLE IF NOT EXISTS carteira_renda_fixa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    emissor VARCHAR(150) NOT NULL,
    tipo_titulo VARCHAR(50) NOT NULL,
    indexador VARCHAR(30) NOT NULL,
    taxa DECIMAL(8, 4) DEFAULT NULL,
    forma ENUM('POS_FIXADO', 'PRE_FIXADO') NOT NULL DEFAULT 'POS_FIXADO',
    valor_investido DECIMAL(18, 2) NOT NULL,
    data_compra DATE NOT NULL,
    data_vencimento DATE DEFAULT NULL,
    liquidez_diaria TINYINT(1) NOT NULL DEFAULT 0,
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE INDEX idx_renda_fixa_usuario ON carteira_renda_fixa(usuario_id);

-- Tipos de título de renda fixa no Brasil (referência no PHP)
-- CDB, LCI, LCA, LC, LF, Debênture, CRI, CRA, LIG, Tesouro Direto, etc.
