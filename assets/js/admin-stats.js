jQuery(document).ready(function($) {
    function initCharts(data) {
        const ctx = document.getElementById('macrocdp-sales-chart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Ventas',
                    data: data.values,
                    borderColor: '#0073aa',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    $('#macrocdp-stats-period').on('change', function() {
        const period = $(this).val();
        $.ajax({
            url: ajaxurl,
            data: {
                action: 'macrocdp_get_stats',
                period: period,
                nonce: macrocdpStats.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar las estadísticas
                    $('.stat-total-sales').text(response.data.totalSales);
                    $('.stat-total-orders').text(response.data.totalOrders);
                    $('.stat-average').text(response.data.average);
                    
                    // Actualizar el gráfico
                    initCharts(response.data.chart);
                }
            }
        });
    });
});
