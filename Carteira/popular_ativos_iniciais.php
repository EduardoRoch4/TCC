<?php
/**
 * POPULAR ATIVOS INICIAIS - Puxa da BrAPI e insere/atualiza no banco
 * Rode uma vez só (ou quando quiser Comprar mais)
 */

require_once 'config/config.php';

$SEU_TOKEN = 'veWxiXHL6L7MWCntLLV75p';

// Lista de códigos que você quer (pode aumentar)
$codigosDesejados = [
    'PETR4', 'VALE3', 'ITUB4', 'BBAS3', 'WEGE3', 'TAEE11', 'KLBN11',
    'IVVB11', 'HGLG11', 'XPML11',
    'AAPL34', 'MSFT34',
    // Cripto (não vem na quote normal, vamos Comprar manual ou em outro endpoint depois)
];

echo "<h2>Popular tabela de ativos com dados reais da BrAPI</h2>";

$pdo = getConnection();

// Cria tipos se não existirem (caso tenha limpado)
$pdo->exec("INSERT IGNORE INTO tipos_ativo (nome, descricao) VALUES 
    ('Ação', 'Ações B3'), ('FII', 'Fundos Imobiliários'), ('ETF', 'ETFs'), 
    ('BDR', 'BDRs'), ('Criptomoeda', 'Criptoativos'), ('Renda Fixa', 'Outros'), ('Outros', 'Demais')");

$codigosStr = implode(',', $codigosDesejados);
$url = "https://brapi.dev/api/quote/{$codigosStr}?modules=summary";

$context = stream_context_create([
    'http' => ['header' => "Authorization: Bearer {$SEU_TOKEN}\r\n"]
]);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    die("Erro na API: " . print_r(error_get_last(), true));
}

$data = json_decode($response, true);

if (!isset($data['results'])) {
    die("<pre>Erro na resposta: " . print_r($data, true) . "</pre>");
}

$inseridos = 0;
$atualizados = 0;

foreach ($data['results'] as $item) {
    $codigo = strtoupper($item['symbol'] ?? '');
    if (!$codigo) continue;

    $nome = $item['longName'] ?? $item['shortName'] ?? $codigo;
    $preco = $item['regularMarketPrice'] ?? 0;
    $var   = $item['regularMarketChangePercent'] ?? 0;

    // Define tipo aproximado (pode melhorar depois)
    $tipo_id = 1; // Ação por padrão
    if (str_ends_with($codigo, '11')) $tipo_id = 2; // FII
    if (str_ends_with($codigo, '11') && strpos($nome, 'ETF') !== false) $tipo_id = 3;
    if (str_ends_with($codigo, '34')) $tipo_id = 4; // BDR

    // Verifica se já existe
    $stmtCheck = $pdo->prepare("SELECT id FROM ativos WHERE codigo = ?");
    $stmtCheck->execute([$codigo]);
    $existe = $stmtCheck->fetch();

    if ($existe) {
        // Atualiza
        $stmt = $pdo->prepare("UPDATE ativos SET nome = ?, tipo_id = ?, preco_atual = ?, variacao_dia = ? WHERE codigo = ?");
        $stmt->execute([$nome, $tipo_id, $preco, $var, $codigo]);
        $atualizados++;
        echo "<p style='color:blue;'>Atualizado: $codigo → $nome (R$ $preco)</p>";
    } else {
        // Insere novo
        $stmt = $pdo->prepare("INSERT INTO ativos (codigo, nome, tipo_id, preco_atual, variacao_dia) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$codigo, $nome, $tipo_id, $preco, $var]);
        $inseridos++;
        echo "<p style='color:green;'>Inserido: $codigo → $nome (R$ $preco)</p>";
    }
}

echo "<hr><strong>Finalizado:</strong> $inseridos novos ativos inseridos, $atualizados atualizados.";