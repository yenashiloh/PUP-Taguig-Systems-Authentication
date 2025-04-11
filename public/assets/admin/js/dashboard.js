window.onload = function () {
    // Monthly Registrations Chart
    const registrationCanvas = document.getElementById('registrationChart');
    if (registrationCanvas && window.monthlyChartData) {
        const ctx1 = registrationCanvas.getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: window.monthlyChartData.months,
                datasets: [{
                    label: 'User Registrations',
                    data: window.monthlyChartData.registrations,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.2)',
                    fill: true,
                    tension: 0.1,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#4e73df',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { beginAtZero: true },
                    y: { beginAtZero: true }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart',
                }
            }
        });
    }

    // Active vs Deactivated Status Bar Chart
    const statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        const ctx2 = statusCanvas.getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['Active', 'Deactivated'],
                datasets: [{
                    label: 'User Status',
                    data: [
                        activeCount, // Use the variable passed from controller
                        deactivatedCount // Use the variable passed from controller
                    ],
                    backgroundColor: ['#1cc88a', '#e74a3b'],
                    borderColor: ['#17a673', '#c0392b'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutBounce'
                }
            }
        });
    }
};