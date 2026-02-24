<?php
require_once 'config/config.php';

$pageTitle = 'Início';

// Buscar ativos mais populares (já com preços atualizados via BrAPI)
$pdo = getConnection();
$stmt = $pdo->query("SELECT a.*, t.nome as tipo FROM ativos a 
    LEFT JOIN tipos_ativo t ON a.tipo_id = t.id 
    ORDER BY a.preco_atual DESC LIMIT 12");
$ativosPrincipais = $stmt->fetchAll();

include 'includes/header.php';
?>

<main class="main">
    <section class="hero">
        <div class="container">
            <h1>Sua Carteira de Investimentos</h1>
            <p class="hero-subtitle">Gerencie seus investimentos com cotações reais atualizadas da B3</p>
            
            <form class="search-form" action="ativos.php" method="GET">
                <input type="text" name="q" placeholder="Pesquise pelo ativo desejado (ex: PETR4, VALE3)..." class="search-input">
                <button type="submit" class="btn btn-primary">Pesquisar</button>
            </form>
            
            <p class="hero-hint">Cotações atualizadas automaticamente via BrAPI • Última atualização: <?php echo date('d/m/Y H:i'); ?></p>
        </div>
    </section>

    <section class="quotes-ticker">
        <div class="container">
            <div class="ticker-carousel-wrapper">
                <div class="ticker-carousel-track" id="tickerCarouselTrack">
                    <?php foreach ($ativosPrincipais as $ativo): ?>
                    <div class="ticker-item">
                        <a href="ativos.php?q=<?php echo urlencode($ativo['codigo']); ?>" class="ticker-code"><?php echo htmlspecialchars($ativo['codigo']); ?></a>
                        <span class="ticker-price">R$ <?php echo number_format($ativo['preco_atual'], 2, ',', '.'); ?></span>
                        <span class="ticker-var <?php echo $ativo['variacao_dia'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo ($ativo['variacao_dia'] >= 0 ? '+' : '') . number_format($ativo['variacao_dia'], 2, ',', '.') . '%'; ?>
                        </span>
                        <?php if (isLoggedIn()): ?>
                            <a href="carteira.php?Comprar=<?php echo $ativo['id']; ?>" class="btn btn-sm btn-primary" style="margin-top:5px; font-size:0.75rem;">Comprar</a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="ranking-section">
        <div class="container">
            <h2>Ativos em Destaque (Cotações Reais)</h2>
            <!-- Carrossel de Ativos -->
            <div class="carousel-wrapper">
                <div class="carousel-container">
                    <button class="carousel-btn carousel-prev" onclick="moveCarousel(-1)">‹</button>
                    <div class="carousel-track" id="carouselTrack">
                        <?php foreach ($ativosPrincipais as $ativo): ?>
                        <div class="carousel-slide">
                            <div class="card card-ativo">
                                <div class="card-header">
                                    <span class="ativo-codigo"><?php echo htmlspecialchars($ativo['codigo']); ?></span>
                                    <span class="ativo-tipo"><?php echo htmlspecialchars($ativo['tipo'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="card-body">
                                    <h3><?php echo htmlspecialchars($ativo['nome']); ?></h3>
                                    <div class="ativo-preco">
                                        <span class="valor">R$ <?php echo number_format($ativo['preco_atual'], 2, ',', '.'); ?></span>
                                        <span class="variacao <?php echo $ativo['variacao_dia'] >= 0 ? 'positive' : 'negative'; ?>">
                                            <?php echo ($ativo['variacao_dia'] >= 0 ? '+' : '') . number_format($ativo['variacao_dia'], 2, ',', '.') . '%'; ?>
                                        </span>
                                    </div>
                                    <?php if (isLoggedIn()): ?>
                                        <a href="carteira.php?Comprar=<?php echo $ativo['id']; ?>" class="btn btn-primary btn-block" style="margin-top:15px;">
                                            Realizar Compra
                                        </a>
                                    <?php else: ?>
                                        <p style="margin-top:15px; text-align:center; color:#718096; font-size:0.9rem;">
                                            Faça login para comprar
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-btn carousel-next" onclick="moveCarousel(1)">›</button>
                </div>
                <div class="carousel-dots" id="carouselDots"></div>
            </div>
        </div>
    </section>

    <!-- Seção de Notícias Financeiras -->
    <section class="news-section">
        <div class="container">
            <h2>Notícias Financeiras</h2>
            <div id="newsContainer" class="news-grid">
                <div class="news-loading">Carregando notícias...</div>
            </div>
        </div>
    </section>

    <section class="features" id="como-funciona">
        <div class="container">
            <h2>Como Funciona</h2>
            <div class="grid grid-3">
                <div class="feature-card">
                    <div class="feature-icon">📋</div>
                    <h3>Cadastre-se</h3>
                    <p>Crie sua conta gratuitamente e tenha acesso à sua carteira digital personalizada.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3>Adicione Investimentos</h3>
                    <p>Registre suas ações, FIIs, criptomoedas e outros ativos com preços reais da B3.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Acompanhe sua Carteira</h3>
                    <p>Visualize o desempenho, valor total e evolução com cotações atualizadas.</p>
                </div>
            </div>
        </div>
    </section>

    <?php if (!isLoggedIn()): ?>
    <section class="cta-section">
        <div class="container">
            <h2>Comece agora gratuitamente</h2>
            <p>Gerencie seus investimentos com cotações reais da B3.</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-primary btn-lg">Criar Conta</a>
                <a href="login.php" class="btn btn-outline btn-lg">Já tenho conta</a>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>