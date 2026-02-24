-- Carteira Digital - Schema do Banco de Dados
-- Execute este arquivo no MySQL para criar as tabelas

CREATE DATABASE IF NOT EXISTS carteira_digital;
USE carteira_digital;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    telefone VARCHAR(20),
    data_nascimento DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de tipos de ativos
CREATE TABLE IF NOT EXISTS tipos_ativo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descricao VARCHAR(255)
);

-- Inserir tipos de ativos padrão
INSERT INTO tipos_ativo (nome, descricao) VALUES
('Ação', 'Ações brasileiras B3'),
('FII', 'Fundos Imobiliários'),
('ETF', 'Exchange Traded Funds'),
('BDR', 'Brazilian Depositary Receipts'),
('Criptomoeda', 'Bitcoin, Ethereum, etc.'),
('Renda Fixa', 'CDB, LCI, LCA, Tesouro'),
('Outros', 'Outros investimentos');

-- Tabela de ativos disponíveis (catálogo)
CREATE TABLE IF NOT EXISTS ativos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    nome VARCHAR(150) NOT NULL,
    tipo_id INT,
    preco_atual DECIMAL(18, 4) DEFAULT 0,
    variacao_dia DECIMAL(8, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_id) REFERENCES tipos_ativo(id),
    UNIQUE KEY unique_codigo (codigo)
);

-- Inserir ativos de exemplo
INSERT INTO ativos (codigo, nome, tipo_id, preco_atual, variacao_dia) VALUES
('PETR4', 'Petrobras', 1, 37.81, 1.67),
('VALE3', 'Vale', 1, 84.09, 0.20),
('ITUB4', 'Banco Itaú', 1, 48.55, 1.17),
('BBAS3', 'Banco do Brasil', 1, 26.46, 2.48),
('WEGE3', 'WEG', 1, 53.37, -3.78),
('IVVB11', 'iShares S&P 500', 3, 402.70, -0.63),
('HGLG11', 'Pátria Logística', 2, 182.50, 0.15),
('XPML11', 'XP Malls', 2, 9.45, 0.32),
('TAEE11', 'Taesa', 1, 44.56, 2.01),
('KLBN11', 'Klabin', 1, 20.17, -0.20),
('AAPL34', 'Apple BDR', 4, 67.89, -1.61),
('MSFT34', 'Microsoft BDR', 4, 86.61, -1.13),
('BTC', 'Bitcoin', 5, 350000.00, 1.06),
('ETH', 'Ethereum', 5, 18500.00, 0.85);

-- Tabela de investimentos na carteira do usuário
CREATE TABLE IF NOT EXISTS carteira_investimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    ativo_id INT NOT NULL,
    quantidade DECIMAL(18, 6) NOT NULL,
    preco_medio DECIMAL(18, 4) NOT NULL,
    data_compra DATE NOT NULL,
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (ativo_id) REFERENCES ativos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario_ativo (usuario_id, ativo_id)
);

-- Tabela de operações (histórico de compras/vendas)
CREATE TABLE IF NOT EXISTS operacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    ativo_id INT NOT NULL,
    tipo ENUM('COMPRA', 'VENDA') NOT NULL,
    quantidade DECIMAL(18, 6) NOT NULL,
    preco_unitario DECIMAL(18, 4) NOT NULL,
    valor_total DECIMAL(18, 2) NOT NULL,
    data_operacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (ativo_id) REFERENCES ativos(id) ON DELETE CASCADE
);

-- Índices para melhor performance
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_carteira_usuario ON carteira_investimentos(usuario_id);
CREATE INDEX idx_operacoes_usuario ON operacoes(usuario_id);
CREATE INDEX idx_ativos_codigo ON ativos(codigo);
