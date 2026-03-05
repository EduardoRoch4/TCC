-- InteliFood - Schema SQLite (não precisa de MySQL)
-- Usado quando DB_DRIVER = 'sqlite' em config/database.php

CREATE TABLE IF NOT EXISTS Usuario (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo TEXT NOT NULL DEFAULT 'cliente' CHECK(tipo IN ('cliente', 'admin')),
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Mesas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    numero INTEGER NOT NULL UNIQUE,
    capacidade INTEGER NOT NULL DEFAULT 4,
    ocupada INTEGER DEFAULT 0,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Produtos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco REAL NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    ativo INTEGER DEFAULT 1,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Vendas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER,
    mesa_id INTEGER NOT NULL,
    total REAL DEFAULT 0.00,
    status TEXT DEFAULT 'aberto' CHECK(status IN ('aberto', 'fechado', 'cancelado')),
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES Usuario(id),
    FOREIGN KEY (mesa_id) REFERENCES Mesas(id)
);

CREATE TABLE IF NOT EXISTS Venda_Itens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    venda_id INTEGER NOT NULL,
    produto_id INTEGER NOT NULL,
    quantidade INTEGER NOT NULL DEFAULT 1,
    preco_unitario REAL NOT NULL,
    subtotal REAL NOT NULL,
    FOREIGN KEY (venda_id) REFERENCES Vendas(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES Produtos(id)
);

CREATE TABLE IF NOT EXISTS Contas_Pagar (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    descricao VARCHAR(200) NOT NULL,
    valor REAL NOT NULL,
    data_vencimento DATE NOT NULL,
    pago INTEGER DEFAULT 0,
    data_pagamento DATE NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_vendas_status ON Vendas(status);
CREATE INDEX IF NOT EXISTS idx_vendas_criado ON Vendas(criado_em);
CREATE INDEX IF NOT EXISTS idx_produtos_categoria ON Produtos(categoria);
CREATE INDEX IF NOT EXISTS idx_produtos_ativo ON Produtos(ativo);

INSERT OR IGNORE INTO Mesas (numero, capacidade) VALUES
(1, 4), (2, 4), (3, 6), (4, 2), (5, 4), (6, 8), (7, 4), (8, 2);

INSERT OR IGNORE INTO Produtos (nome, descricao, preco, categoria) VALUES
('X-Burger', 'Hambúrguer artesanal com queijo', 25.90, 'Lanches'),
('X-Tudo', 'Hambúrguer com bacon, ovo e queijo', 32.90, 'Lanches'),
('Refrigerante 350ml', 'Coca-Cola, Guaraná ou Fanta', 5.00, 'Bebidas'),
('Suco Natural', 'Laranja, limão ou maracujá', 8.00, 'Bebidas'),
('Batata Frita', 'Porção individual', 12.00, 'Acompanhamentos'),
('Feijoada', 'Feijoada completa com acompanhamentos', 35.00, 'Pratos'),
('Filé à Parmegiana', 'Filé com molho e queijo', 38.00, 'Pratos');
