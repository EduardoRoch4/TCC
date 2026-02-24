<?php
/**
 * API para buscar notícias financeiras de sites brasileiros
 * Usa NewsAPI ou RSS feeds como fallback
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Configuração da NewsAPI (você precisa de uma chave gratuita em https://newsapi.org/)
$newsApiKey = 'YOUR_NEWSAPI_KEY_HERE'; // Substitua pela sua chave
// Buscar apenas notícias financeiras
$newsApiUrl = 'https://newsapi.org/v2/everything?q=(finanças+OR+investimentos+OR+bolsa+de+valores+OR+ações+OR+FII+OR+renda+fixa+OR+CDI+OR+Selic+OR+Ibovespa+OR+dólar+OR+mercado+financeiro+OR+economia+financeira)&language=pt&sortBy=publishedAt&pageSize=20';

// Função para buscar via NewsAPI
function buscarNoticiasNewsAPI($url, $apiKey) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '&apiKey=' . $apiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    return null;
}

// Função para buscar via RSS feeds brasileiros (fallback)
function buscarNoticiasRSS() {
    $feeds = [
        'https://www.infomoney.com.br/feed/',
        'https://www.valor.com.br/financas/rss',
        'https://g1.globo.com/economia/feed/',
        'https://www.estadao.com.br/rss/economia.xml'
    ];
    
    $noticias = [];
    
    foreach ($feeds as $feedUrl) {
        try {
            $xml = @simplexml_load_file($feedUrl);
            if ($xml && isset($xml->channel->item)) {
                foreach ($xml->channel->item as $item) {
                    $titulo = (string)$item->title;
                    $link = (string)$item->link;
                    $descricao = strip_tags((string)$item->description);
                    $data = isset($item->pubDate) ? date('Y-m-d H:i:s', strtotime((string)$item->pubDate)) : date('Y-m-d H:i:s');
                    $imagem = '';
                    
                    // Tentar pegar imagem do conteúdo
                    if (isset($item->enclosure)) {
                        $imagem = (string)$item->enclosure['url'];
                    }
                    
                    // Tentar pegar imagem do conteúdo HTML
                    if (empty($imagem) && isset($item->description)) {
                        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', (string)$item->description, $matches);
                        if (!empty($matches[1])) {
                            $imagem = $matches[1];
                        }
                    }
                    
                    // Tentar pegar imagem do media:content
                    if (empty($imagem) && isset($item->children('media', true)->content)) {
                        $mediaContent = $item->children('media', true)->content;
                        if (isset($mediaContent->attributes()->url)) {
                            $imagem = (string)$mediaContent->attributes()->url;
                        }
                    }
                    
                    // Filtrar apenas notícias relacionadas a finanças
                    if (stripos($titulo, 'finança') !== false || 
                        stripos($titulo, 'investimento') !== false || 
                        stripos($titulo, 'bolsa') !== false ||
                        stripos($titulo, 'ação') !== false ||
                        stripos($titulo, 'dólar') !== false ||
                        stripos($titulo, 'selic') !== false ||
                        stripos($titulo, 'ibovespa') !== false) {
                        $noticias[] = [
                            'titulo' => $titulo,
                            'descricao' => substr($descricao, 0, 150) . '...',
                            'link' => $link,
                            'data' => $data,
                            'imagem' => $imagem,
                            'fonte' => parse_url($feedUrl, PHP_URL_HOST)
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            // Ignora erros de feed individual
            continue;
        }
    }
    
    // Limitar a 12 notícias e ordenar por data
    usort($noticias, function($a, $b) {
        return strtotime($b['data']) - strtotime($a['data']);
    });
    
    return array_slice($noticias, 0, 12);
}

// Tentar NewsAPI primeiro
$noticias = null;
if ($newsApiKey !== 'YOUR_NEWSAPI_KEY_HERE') {
    $resultado = buscarNoticiasNewsAPI($newsApiUrl, $newsApiKey);
    if ($resultado && isset($resultado['articles'])) {
        // Filtrar apenas notícias financeiras
        $palavrasChave = [
            'finança', 'investimento', 'bolsa', 'ação', 'ações', 'dólar', 'selic', 'ibovespa',
            'fii', 'renda fixa', 'cdi', 'cdb', 'lci', 'lca', 'tesouro', 'mercado financeiro',
            'economia financeira', 'carteira', 'patrimônio', 'dividendos', 'cotação', 'ativo',
            'b3', 'bovespa', 'fundo', 'fundos', 'investidor', 'investidores'
        ];
        
        $artigosFinanceiros = array_filter($resultado['articles'], function($article) use ($palavrasChave) {
            $titulo = mb_strtolower($article['title'] ?? '', 'UTF-8');
            $descricao = mb_strtolower($article['description'] ?? '', 'UTF-8');
            
            foreach ($palavrasChave as $palavra) {
                if (stripos($titulo, $palavra) !== false || stripos($descricao, $palavra) !== false) {
                    return true;
                }
            }
            return false;
        });
        
        $noticias = array_map(function($article) {
            return [
                'titulo' => $article['title'] ?? '',
                'descricao' => substr($article['description'] ?? '', 0, 150) . '...',
                'link' => $article['url'] ?? '',
                'data' => isset($article['publishedAt']) ? date('Y-m-d H:i:s', strtotime($article['publishedAt'])) : date('Y-m-d H:i:s'),
                'imagem' => $article['urlToImage'] ?? '',
                'fonte' => $article['source']['name'] ?? 'Notícias'
            ];
        }, array_slice($artigosFinanceiros, 0, 12));
    }
}

// Se NewsAPI falhar, usar RSS
if (!$noticias) {
    $noticias = buscarNoticiasRSS();
}

// Se ainda não tiver notícias, retornar notícias de exemplo
if (empty($noticias)) {
    $noticias = [
        [
            'titulo' => 'Mercado financeiro brasileiro em alta',
            'descricao' => 'O mercado financeiro brasileiro registra alta nesta semana...',
            'link' => '#',
            'data' => date('Y-m-d H:i:s'),
            'imagem' => '',
            'fonte' => 'CarteiraInvest'
        ]
    ];
}

echo json_encode(['noticias' => $noticias], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
