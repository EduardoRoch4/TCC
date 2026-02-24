    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <span class="logo-icon">💼</span>
                    <span>CarteiraInvest</span>
                </div>
                <div class="footer-links">
                    <a href="index.php">Início</a>
                    <a href="ativos.php">Ativos</a>
                    <a href="index.php#como-funciona">Como Funciona</a>
                </div>
                <p class="footer-disclaimer">
                    CarteiraInvest é uma plataforma de simulação. Os dados de cotação são ilustrativos. 
                    Não constitui recomendação de investimento.
                </p>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> CarteiraInvest. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/ticker-carousel.js"></script>
    <script src="assets/js/carousel.js"></script>
    <script src="assets/js/noticias.js"></script>
    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
