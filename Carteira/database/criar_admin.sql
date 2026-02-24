-- Script para criar um usuário administrador
-- Execute este script após criar o banco de dados

USE carteira_digital;

-- ============================================
-- CREDENCIAIS DO ADMINISTRADOR
-- ============================================
-- Email: admin@carteira.com
-- Senha: admin123
-- ============================================
-- IMPORTANTE: Altere a senha após o primeiro login!
-- ============================================

-- Verificar e criar colunas se não existirem
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'usuarios' 
    AND COLUMN_NAME = 'senha_hash');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE usuarios ADD COLUMN senha_hash VARCHAR(255) NULL AFTER senha', 
    'SELECT "Coluna senha_hash já existe"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'usuarios' 
    AND COLUMN_NAME = 'is_admin');
SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE usuarios ADD COLUMN is_admin TINYINT(1) DEFAULT 0', 
    'SELECT "Coluna is_admin já existe"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Criar usuário admin (senha: admin123)
-- Hash da senha "admin123": $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO usuarios (nome, email, senha_hash, is_admin) 
VALUES ('Administrador', 'admin@carteira.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1)
ON DUPLICATE KEY UPDATE 
    senha_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    is_admin = 1;

-- Se ainda não funcionar, tente este comando alternativo:
-- UPDATE usuarios SET senha_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', is_admin = 1 WHERE email = 'admin@carteira.com';

-- Para criar um admin manualmente via SQL (substitua 'senha_segura' pela senha desejada):
-- UPDATE usuarios SET is_admin = 1 WHERE email = 'seu_email@exemplo.com';
