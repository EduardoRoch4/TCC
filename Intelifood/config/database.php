<?php
/**
 * Configuração da conexão com o banco de dados
 *
 * Opção 1 - SQLite (não precisa instalar MySQL):
 *   Defina DB_DRIVER como 'sqlite'. Execute uma vez: php instalar_sqlite.php
 *
 * Opção 2 - MySQL/MariaDB:
 *   Defina DB_DRIVER como 'mysql' e ajuste host, porta, usuário e senha abaixo.
 */
define('DB_DRIVER', 'sqlite'); // 'sqlite' ou 'mysql'

// Caminho do arquivo SQLite (só usado quando DB_DRIVER = 'sqlite')
define('DB_SQLITE_PATH', dirname(__DIR__) . '/data/intelifood.sqlite');

// MySQL (só usado quando DB_DRIVER = 'mysql')
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'intelifood');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $pdo = null;

    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            if (DB_DRIVER === 'sqlite') {
                self::$pdo = self::conectarSQLite();
            } else {
                self::$pdo = self::conectarMySQL();
            }
        }
        return self::$pdo;
    }

    private static function conectarSQLite(): PDO {
        $arquivo = DB_SQLITE_PATH;
        if (!file_exists($arquivo)) {
            self::mostrarErroSQLiteInstalar($arquivo);
        }
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 30, // Timeout para operações
        ];
        try {
            $pdo = new PDO('sqlite:' . $arquivo, null, null, $options);
            // Configurações para melhor desempenho e confiabilidade com SQLite
            $pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
            $pdo->exec('PRAGMA journal_mode = WAL'); // Write-Ahead Logging para melhor concorrência
            $pdo->exec('PRAGMA synchronous = NORMAL'); // Normal is safer than OFF
            $pdo->exec('PRAGMA foreign_keys = ON'); // Ativar chaves estrangeiras
            return $pdo;
        } catch (PDOException $e) {
            self::mostrarErroConexao($e, 'SQLite');
        }
    }

    private static function conectarMySQL(): PDO {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            return new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            self::mostrarErroConexao($e, 'MySQL');
        }
    }

    private static function mostrarErroSQLiteInstalar(string $arquivo): never {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>SQLite não instalado</title>';
        echo '<style>body{font-family:sans-serif;max-width:560px;margin:40px auto;padding:20px;background:#fef7ed;}';
        echo 'h1{color:#9a3412;} code{background:#e7e5e4;padding:2px 6px;} pre{background:#e7e5e4;padding:12px;}</style></head><body>';
        echo '<h1>Banco SQLite ainda não foi criado</h1>';
        echo '<p>Execute no terminal (na pasta do projeto):</p>';
        echo '<pre>php instalar_sqlite.php</pre>';
        echo '<p>Depois recarregue esta página.</p>';
        echo '</body></html>';
        exit;
    }

    private static function mostrarErroConexao(PDOException $e, string $driver = 'MySQL'): never {
        $msg = $e->getMessage();
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Erro de conexão</title>';
        echo '<style>body{font-family:sans-serif;max-width:560px;margin:40px auto;padding:20px;background:#fef7ed;}';
        echo 'h1{color:#9a3412;} code{background:#e7e5e4;padding:2px 6px;} ul{line-height:1.8;}</style></head><body>';
        echo '<h1>Não foi possível conectar ao banco de dados</h1>';
        if ($driver === 'MySQL') {
            echo '<p>O MySQL/MariaDB está inacessível. Verifique:</p>';
            echo '<ul>';
            echo '<li><strong>Serviço ligado?</strong> O MySQL precisa estar em execução (XAMPP, WAMP, Laragon ou serviço Windows).</li>';
            echo '<li><strong>Host e porta:</strong> Em <code>config/database.php</code> estão <code>' . htmlspecialchars(DB_HOST) . ':' . DB_PORT . '</code>. Se usar outra porta, altere <code>DB_PORT</code>.</li>';
            echo '<li><strong>Banco criado?</strong> Execute o arquivo <code>sql/schema.sql</code> no MySQL para criar o banco e as tabelas.</li>';
            echo '<li><strong>Usuário e senha:</strong> Confira <code>DB_USER</code> e <code>DB_PASS</code> em <code>config/database.php</code>.</li>';
            echo '<li><strong>Usar sem MySQL?</strong> Altere <code>DB_DRIVER</code> para <code>\'sqlite\'</code> e execute <code>php instalar_sqlite.php</code>.</li>';
            echo '</ul>';
        }
        echo '<p><small>Erro: ' . htmlspecialchars($msg) . '</small></p>';
        echo '</body></html>';
        exit;
    }
}
