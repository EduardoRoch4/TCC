<?php
/**
 * ATUALIZAR COTAÇÕES - BrAPI.dev (com token obrigatório)
 * Rode manualmente via navegador ou configure cron
 */

require_once 'config/config.php';

// Seu token (já inserido)
$SEU_TOKEN = 'veWxiXHL6L7MWCntLLV75p';

function atualizarCotacoesBrAPI() {
    global $SEU_TOKEN;

    if (empty($SEU_TOKEN)) {
        echo "<p style='color:red; font-weight:bold;'>ERRO: Token não definido no script.</p>";
        return;
    }

    $pdo = getConnection();

    // Busca apenas ações, FIIs, ETFs e BDRs (exclui cripto por enquanto)
    $stmt = $pdo->query("
        SELECT id, codigo 
        FROM ativos 
        WHERE tipo_id IN (1,2,3,4)
        ORDER BY codigo
    ");
    $ativos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($ativos)) {
        echo "<p style='color:orange;'>Nenhum ativo elegível (tipos 1,2,3,4) encontrado no banco.</p>";
        return;
    }

    echo "<p>Buscando cotações para " . count($ativos) . " ativos...</p>";

    $codigos = array_column($ativos, 'codigo');
    $codigosStr = implode(',', $codigos);

    // URL da BrAPI
    $url = "https://brapi.dev/api/quote/{$codigosStr}?modules=summary";

    // Header de autenticação
    $context = stream_context_create([
        'http' => [
            'header' => "Authorization: Bearer {$SEU_TOKEN}\r\n",
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        $error = error_get_last();
        echo "<p style='color:red; font-weight:bold;'>Erro na requisição HTTP: " . 
             ($error['message'] ?? 'Falha desconhecida') . "</p>";
        echo "<p>URL tentada: <code>$url</code></p>";
        return;
    }

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<p style='color:red;'>Erro ao decodificar JSON da API: " . json_last_error_msg() . "</p>";
        return;
    }

    if (!isset($data['results']) || !is_array($data['results'])) {
        echo "<pre style='color:red; background:#ffebee; padding:10px;'>";
        echo "Resposta da API inválida ou erro (possível token expirado/limite atingido):\n";
        print_r($data);
        echo "</pre>";
        return;
    }

    // Mapeia preços por código (case insensitive)
    $precos = [];
    foreach ($data['results'] as $item) {
        $codigo = strtoupper($item['symbol'] ?? '');
        if ($codigo) {
            $precos[$codigo] = [
                'preco_atual'  => $item['regularMarketPrice']   ?? 0,
                'variacao_dia' => $item['regularMarketChangePercent'] ?? 0
            ];
        }
    }

    // Atualiza o banco
    $atualizados = 0;
    $erros = 0;

    foreach ($ativos as $ativo) {
        $codigo = strtoupper($ativo['codigo']);
        if (isset($precos[$codigo]) && $precos[$codigo]['preco_atual'] > 0) {
            $stmt = $pdo->prepare("
                UPDATE ativos 
                SET preco_atual = :preco, 
                    variacao_dia = :var 
                WHERE id = :id
            ");
            $stmt->execute([
                ':preco' => $precos[$codigo]['preco_atual'],
                ':var'   => $precos[$codigo]['variacao_dia'],
                ':id'    => $ativo['id']
            ]);
            $atualizados++;
            echo "<p style='color:green; margin:5px 0;'>✔ {$codigo}: R$ " . 
                 number_format($precos[$codigo]['preco_atual'], 2, ',', '.') . 
                 " ({$precos[$codigo]['variacao_dia']}%) </p>";
        } else {
            $erros++;
            echo "<p style='color:orange; margin:5px 0;'>✗ {$codigo} — não encontrado na API ou preço zero</p>";
        }
    }

    echo "<hr style='margin:20px 0;'>";
    echo "<h3 style='color:#0a2540;'>Resumo da atualização:</h3>";
    echo "<p><strong>Ativos atualizados com sucesso:</strong> $atualizados</p>";
    echo "<p><strong>Falhas:</strong> $erros</p>";
    echo "<p><strong>Total de ativos processados:</strong> " . count($ativos) . "</p>";
}

// Executa
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html lang='pt-BR'><head><meta charset='UTF-8'>";
echo "<title>Atualização BrAPI</title><style>body{font-family:Arial,sans-serif;padding:20px;max-width:900px;margin:auto;}</style></head><body>";
echo "<h2>Atualização de Cotações - BrAPI.dev</h2>";
echo "<p><strong>Data/hora:</strong> " . date('d/m/Y H:i:s') . " (Horário de Brasília)</p>";
atualizarCotacoesBrAPI();
echo "</body></html>";