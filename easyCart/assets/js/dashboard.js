document.addEventListener('DOMContentLoaded', function () {
    const statOrders = document.getElementById('stat-orders');
    const statSpent = document.getElementById('stat-spent');
    const chartLoading = document.getElementById('chartLoading');
    const chartCanvas = document.getElementById('orderChart');
    const emptyChart = document.getElementById('emptyChart');

    // Fetch dashboard stats via AJAX
    fetchStats();

    function fetchStats() {
        const formData = new FormData();
        formData.append('action', 'get_stats');

        fetch('src/handlers/dashboard.handler', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    updateDashboard(res.data);
                } else {
                    console.error('Failed to fetch stats:', res.message);
                    statOrders.textContent = 'Error';
                    statSpent.textContent = 'Error';
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
            });
    }

    function updateDashboard(data) {
        // Update metric cards
        statOrders.textContent = data.total_orders;
        statSpent.textContent = '₹' + data.total_spent;

        // Update Chart
        if (data.chart_labels && data.chart_labels.length > 0) {
            chartLoading.style.display = 'none';
            chartCanvas.style.display = 'block';
            renderChart(data.chart_labels, data.chart_values);
        } else {
            chartLoading.style.display = 'none';
            emptyChart.style.display = 'flex';
        }
    }

    function renderChart(labels, values) {
        const ctx = chartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Order Amount (₹)',
                    data: values,
                    fill: true,
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderColor: 'rgb(37, 99, 235)',
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgb(37, 99, 235)',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function (value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});
