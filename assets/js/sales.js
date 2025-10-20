// Sales Management System - JavaScript

class SalesManagement {
    constructor() {
        this.currentDateRange = 'today';
        this.salesData = {};
        this.init();
    }

    init() {
        this.setupDateRangePicker();
        this.initializeSalesCharts();
        this.setupTableControls();
        this.loadSalesData();
        this.setupRealTimeUpdates();
    }

    setupDateRangePicker() {
        const dateButtons = document.querySelectorAll('.date-btn');
        
        dateButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                dateButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                button.classList.add('active');
                
                // Update current date range
                this.currentDateRange = button.dataset.range;
                
                // Load data for selected range
                this.loadSalesData();
                
                // Update charts
                this.updateCharts();
            });
        });
    }

    initializeSalesCharts() {
        this.createSalesTrendChart();
        this.createRestaurantSalesChart();
    }

    createSalesTrendChart() {
        const ctx = document.getElementById('salesTrendChart');
        if (!ctx) return;

        this.salesTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.getDateLabels(),
                datasets: [{
                    label: '売上',
                    data: this.getSalesTrendData(),
                    borderColor: '#4F46E5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4F46E5',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }, {
                    label: '手数料収入',
                    data: this.getCommissionData(),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#10B981',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ¥' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '¥' + (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }

    createRestaurantSalesChart() {
        const ctx = document.getElementById('restaurantSalesChart');
        if (!ctx) return;

        this.restaurantSalesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: this.getTopRestaurants(),
                datasets: [{
                    label: '売上',
                    data: this.getRestaurantSalesData(),
                    backgroundColor: [
                        '#4F46E5',
                        '#10B981',
                        '#F59E0B',
                        '#EF4444',
                        '#8B5CF6',
                        '#06B6D4',
                        '#84CC16',
                        '#F97316',
                        '#EC4899',
                        '#6366F1'
                    ],
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '売上: ¥' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '¥' + (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    }

    setupTableControls() {
        const searchInput = document.querySelector('.sales-table-container .search-box input');
        const filterSelect = document.querySelector('.filter-select');
        
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterTable(e.target.value);
            });
        }
        
        if (filterSelect) {
            filterSelect.addEventListener('change', (e) => {
                this.filterTableByType(e.target.value);
            });
        }
    }

    filterTable(searchTerm) {
        const tableRows = document.querySelectorAll('.sales-table tbody tr');
        
        tableRows.forEach(row => {
            const restaurantName = row.querySelector('.restaurant-name').textContent.toLowerCase();
            const location = row.querySelector('.restaurant-location').textContent.toLowerCase();
            
            if (restaurantName.includes(searchTerm.toLowerCase()) || 
                location.includes(searchTerm.toLowerCase())) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    filterTableByType(filterType) {
        const tableRows = document.querySelectorAll('.sales-table tbody tr');
        
        tableRows.forEach(row => {
            if (filterType === 'all') {
                row.style.display = '';
            } else {
                // Add logic for other filter types
                row.style.display = '';
            }
        });
    }

    loadSalesData() {
        // Simulate loading sales data
        this.salesData = {
            totalRevenue: 12456789,
            totalOrders: 2456,
            commissionIncome: 1234567,
            averageOrderValue: 5067,
            growthRate: 15.3
        };
        
        this.updateOverviewCards();
        this.updateHourlySales();
        this.updatePaymentMethods();
    }

    updateOverviewCards() {
        const cards = document.querySelectorAll('.overview-card .card-value');
        
        if (cards[0]) {
            cards[0].textContent = '¥' + this.salesData.totalRevenue.toLocaleString();
        }
        if (cards[1]) {
            cards[1].textContent = this.salesData.totalOrders.toLocaleString();
        }
        if (cards[2]) {
            cards[2].textContent = '¥' + this.salesData.commissionIncome.toLocaleString();
        }
        if (cards[3]) {
            cards[3].textContent = '¥' + this.salesData.averageOrderValue.toLocaleString();
        }
    }

    updateHourlySales() {
        const hourlyData = this.getHourlySalesData();
        const hourItems = document.querySelectorAll('.hour-item');
        
        hourItems.forEach((item, index) => {
            if (hourlyData[index]) {
                const fill = item.querySelector('.hour-fill');
                const amount = item.querySelector('.hour-amount');
                
                if (fill) {
                    fill.style.width = hourlyData[index].percentage + '%';
                }
                if (amount) {
                    amount.textContent = '¥' + hourlyData[index].amount.toLocaleString();
                }
            }
        });
    }

    updatePaymentMethods() {
        const paymentData = this.getPaymentMethodsData();
        const paymentItems = document.querySelectorAll('.payment-item');
        
        paymentItems.forEach((item, index) => {
            if (paymentData[index]) {
                const percentage = item.querySelector('.payment-percentage');
                const amount = item.querySelector('.payment-amount');
                
                if (percentage) {
                    percentage.textContent = paymentData[index].percentage + '%';
                }
                if (amount) {
                    amount.textContent = '¥' + paymentData[index].amount.toLocaleString();
                }
            }
        });
    }

    updateCharts() {
        if (this.salesTrendChart) {
            this.salesTrendChart.data.labels = this.getDateLabels();
            this.salesTrendChart.data.datasets[0].data = this.getSalesTrendData();
            this.salesTrendChart.data.datasets[1].data = this.getCommissionData();
            this.salesTrendChart.update();
        }
        
        if (this.restaurantSalesChart) {
            this.restaurantSalesChart.data.labels = this.getTopRestaurants();
            this.restaurantSalesChart.data.datasets[0].data = this.getRestaurantSalesData();
            this.restaurantSalesChart.update();
        }
    }

    setupRealTimeUpdates() {
        // Simulate real-time data updates
        setInterval(() => {
            this.updateRealTimeData();
        }, 30000); // Update every 30 seconds
    }

    updateRealTimeData() {
        // Add subtle animation to indicate data update
        const cards = document.querySelectorAll('.overview-card');
        cards.forEach(card => {
            card.style.transform = 'scale(1.02)';
            setTimeout(() => {
                card.style.transform = 'scale(1)';
            }, 200);
        });
        
        // Update some random data
        this.salesData.totalOrders += Math.floor(Math.random() * 5);
        this.salesData.totalRevenue += Math.floor(Math.random() * 50000);
        
        this.updateOverviewCards();
    }

    // Data generation methods
    getDateLabels() {
        const labels = [];
        const today = new Date();
        
        switch (this.currentDateRange) {
            case 'today':
                for (let i = 0; i < 24; i++) {
                    labels.push(i + ':00');
                }
                break;
            case 'week':
                for (let i = 6; i >= 0; i--) {
                    const date = new Date(today);
                    date.setDate(date.getDate() - i);
                    labels.push(date.toLocaleDateString('ja-JP', { month: 'short', day: 'numeric' }));
                }
                break;
            case 'month':
                for (let i = 29; i >= 0; i--) {
                    const date = new Date(today);
                    date.setDate(date.getDate() - i);
                    labels.push(date.toLocaleDateString('ja-JP', { month: 'short', day: 'numeric' }));
                }
                break;
        }
        
        return labels;
    }

    getSalesTrendData() {
        const data = [];
        const baseAmount = 1000000;
        
        for (let i = 0; i < this.getDateLabels().length; i++) {
            data.push(baseAmount + Math.random() * 500000);
        }
        
        return data;
    }

    getCommissionData() {
        const salesData = this.getSalesTrendData();
        return salesData.map(amount => amount * 0.1); // 10% commission
    }

    getTopRestaurants() {
        return [
            'Sushi Master',
            'Pizza Corner',
            'Ramen House',
            'Burger King',
            'KFC',
            'McDonald\'s',
            'Subway',
            'Starbucks',
            'Dunkin\'',
            'Taco Bell'
        ];
    }

    getRestaurantSalesData() {
        const data = [];
        const baseAmount = 500000;
        
        for (let i = 0; i < 10; i++) {
            data.push(baseAmount + Math.random() * 1000000);
        }
        
        return data.sort((a, b) => b - a);
    }

    getHourlySalesData() {
        const hours = ['06:00', '07:00', '08:00', '12:00', '18:00', '19:00'];
        const data = [];
        
        hours.forEach(hour => {
            const amount = Math.floor(Math.random() * 500000) + 50000;
            const percentage = (amount / 600000) * 100;
            
            data.push({
                amount: amount,
                percentage: Math.min(percentage, 100)
            });
        });
        
        return data;
    }

    getPaymentMethodsData() {
        return [
            { name: 'クレジットカード', percentage: 65, amount: 8096913 },
            { name: 'モバイル決済', percentage: 25, amount: 3114197 },
            { name: '現金', percentage: 10, amount: 1245679 }
        ];
    }

    // Export functionality
    exportData() {
        const exportBtn = document.querySelector('.export-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => {
                this.downloadCSV();
            });
        }
    }

    downloadCSV() {
        const csvContent = this.generateCSV();
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `sales_data_${this.currentDateRange}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    generateCSV() {
        const headers = ['レストラン', '注文数', '売上', '手数料', '純利益', '成長率'];
        const rows = [
            ['Sushi Master', '456', '¥2,456,789', '¥368,518', '¥2,088,271', '+15.3%'],
            ['Pizza Corner', '234', '¥1,234,567', '¥185,185', '¥1,049,382', '+8.7%'],
            ['Ramen House', '189', '¥987,654', '¥148,148', '¥839,506', '-2.1%']
        ];
        
        let csv = headers.join(',') + '\n';
        rows.forEach(row => {
            csv += row.join(',') + '\n';
        });
        
        return csv;
    }
}

// Initialize sales management when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SalesManagement();
});

// Add additional utility functions
const SalesUtils = {
    formatCurrency: (amount) => {
        return new Intl.NumberFormat('ja-JP', {
            style: 'currency',
            currency: 'JPY'
        }).format(amount);
    },

    formatNumber: (number) => {
        return new Intl.NumberFormat('ja-JP').format(number);
    },

    formatPercentage: (value) => {
        return new Intl.NumberFormat('ja-JP', {
            style: 'percent',
            minimumFractionDigits: 1,
            maximumFractionDigits: 1
        }).format(value / 100);
    },

    calculateGrowthRate: (current, previous) => {
        if (previous === 0) return 0;
        return ((current - previous) / previous) * 100;
    },

    generateRandomData: (min, max, count) => {
        const data = [];
        for (let i = 0; i < count; i++) {
            data.push(Math.floor(Math.random() * (max - min + 1)) + min);
        }
        return data;
    }
};
