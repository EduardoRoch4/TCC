    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> CarteiraInvest - Painel Administrativo</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/noticias.js"></script>
    <?php if (isset($chartLabels) && !empty($chartLabels)): ?>
    <script>
    // Gráfico de Ativos Mais Comprados
    const ctxAtivos = document.getElementById('chartAtivosMaisComprados');
    if (ctxAtivos) {
        new Chart(ctxAtivos, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Quantidade de Usuários',
                    data: <?php echo json_encode($chartUsuarios); ?>,
                    backgroundColor: 'rgba(0, 200, 83, 0.8)',
                    borderColor: 'rgba(0, 200, 83, 1)',
                    borderWidth: 1
                }, {
                    label: 'Valor Total (R$ mil)',
                    data: <?php echo json_encode(array_map(function($v) { return $v / 1000; }, $chartValores)); ?>,
                    backgroundColor: 'rgba(26, 54, 93, 0.8)',
                    borderColor: 'rgba(26, 54, 93, 1)',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 1) {
                                    return 'Valor Total: R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2}) + ' mil';
                                }
                                return context.dataset.label + ': ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Usuários'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Valor (R$ mil)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    }
    </script>
    <?php endif; ?>
</body>
</html>
