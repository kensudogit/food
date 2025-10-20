// Food Delivery Admin - Interactive JavaScript

class FoodDeliveryAdmin {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeCharts();
        this.setupThemeToggle();
        this.setupNotifications();
        this.setupSidebar();
        this.loadDashboardData();
    }

    setupEventListeners() {
        // Sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            });
        }

        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }

        // Menu item clicks
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remove active class from all items
                menuItems.forEach(menuItem => menuItem.classList.remove('active'));
                
                // Add active class to clicked item
                item.classList.add('active');
                
                // Load content based on menu item
                const link = item.querySelector('.menu-link');
                if (link) {
                    const href = link.getAttribute('href');
                    this.loadContent(href);
                }
            });
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Window resize handler
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
            }
        });
    }

    setupSidebar() {
        const sidebar = document.getElementById('sidebar');
        
        // Add smooth scrolling to sidebar
        sidebar.style.scrollBehavior = 'smooth';
        
        // Add hover effects to menu items
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.addEventListener('mouseenter', () => {
                if (!item.classList.contains('active')) {
                    item.style.transform = 'translateX(4px)';
                }
            });
            
            item.addEventListener('mouseleave', () => {
                item.style.transform = 'translateX(0)';
            });
        });
    }

    setupThemeToggle() {
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        
        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        body.setAttribute('data-theme', savedTheme);
        
        // Update theme toggle icon
        this.updateThemeIcon(savedTheme);
        
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const currentTheme = body.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                body.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                
                this.updateThemeIcon(newTheme);
                
                // Add transition effect
                body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
                setTimeout(() => {
                    body.style.transition = '';
                }, 300);
            });
        }
    }

    updateThemeIcon(theme) {
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            const icon = themeToggle.querySelector('i');
            if (icon) {
                icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        }
    }

    setupNotifications() {
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationPanel = document.getElementById('notificationPanel');
        const closeNotifications = document.getElementById('closeNotifications');

        if (notificationBtn) {
            notificationBtn.addEventListener('click', () => {
                notificationPanel.classList.toggle('active');
                
                // Mark notifications as read
                this.markNotificationsAsRead();
            });
        }

        if (closeNotifications) {
            closeNotifications.addEventListener('click', () => {
                notificationPanel.classList.remove('active');
            });
        }

        // Close notification panel when clicking outside
        document.addEventListener('click', (e) => {
            if (!notificationBtn.contains(e.target) && !notificationPanel.contains(e.target)) {
                notificationPanel.classList.remove('active');
            }
        });

        // Simulate real-time notifications
        this.simulateNotifications();
    }

    simulateNotifications() {
        const notifications = [
            {
                icon: 'fas fa-exclamation-triangle',
                title: '新しい注文',
                message: 'Sushi Masterから新しい注文があります',
                type: 'warning'
            },
            {
                icon: 'fas fa-check-circle',
                title: '配送完了',
                message: '注文 #FD-2024-001 が配送完了しました',
                type: 'success'
            },
            {
                icon: 'fas fa-info-circle',
                title: 'システム更新',
                message: '新しい機能が追加されました',
                type: 'info'
            }
        ];

        // Show random notification every 30 seconds
        setInterval(() => {
            const randomNotification = notifications[Math.floor(Math.random() * notifications.length)];
            this.showToastNotification(randomNotification);
        }, 30000);
    }

    showToastNotification(notification) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="${notification.icon}"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${notification.title}</div>
                <div class="toast-message">${notification.message}</div>
            </div>
            <button class="toast-close">
                <i class="fas fa-times"></i>
            </button>
        `;

        // Add toast styles
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--bg-card);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: var(--spacing-md);
            box-shadow: var(--shadow-lg);
            z-index: 3000;
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            min-width: 300px;
            animation: slideInRight 0.3s ease-out;
        `;

        document.body.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 5000);

        // Close button functionality
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            toast.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        });
    }

    markNotificationsAsRead() {
        const notificationBadge = document.querySelector('.notification-badge');
        if (notificationBadge) {
            notificationBadge.style.display = 'none';
        }
    }

    initializeCharts() {
        this.createRevenueChart();
        this.createOrdersChart();
    }

    createRevenueChart() {
        const ctx = document.getElementById('revenueChart');
        if (!ctx) return;

        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['月', '火', '水', '木', '金', '土', '日'],
                datasets: [{
                    label: '売上',
                    data: [1200000, 1500000, 1800000, 1600000, 2000000, 2200000, 1900000],
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
                elements: {
                    point: {
                        hoverBackgroundColor: '#4F46E5'
                    }
                }
            }
        });
    }

    createOrdersChart() {
        const ctx = document.getElementById('ordersChart');
        if (!ctx) return;

        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['調理中', '配送中', '配送完了', 'キャンセル'],
                datasets: [{
                    data: [25, 15, 55, 5],
                    backgroundColor: [
                        '#F59E0B',
                        '#3B82F6',
                        '#10B981',
                        '#EF4444'
                    ],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });
    }

    loadContent(href) {
        // Simulate content loading
        const mainContent = document.querySelector('.dashboard-content');
        if (mainContent) {
            mainContent.style.opacity = '0.5';
            mainContent.style.transition = 'opacity 0.3s ease';
            
            setTimeout(() => {
                mainContent.style.opacity = '1';
                // Here you would load actual content based on href
                console.log('Loading content for:', href);
            }, 300);
        }
    }

    loadDashboardData() {
        // Simulate loading dashboard data
        this.animateStatCards();
        this.updateRealTimeData();
    }

    animateStatCards() {
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach((card, index) => {
            setTimeout(() => {
                card.style.animation = 'fadeIn 0.6s ease-out';
            }, index * 100);
        });
    }

    updateRealTimeData() {
        // Simulate real-time data updates
        setInterval(() => {
            const statValues = document.querySelectorAll('.stat-value');
            statValues.forEach(stat => {
                // Add subtle animation to indicate data update
                stat.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    stat.style.transform = 'scale(1)';
                }, 200);
            });
        }, 30000);
    }

    // Utility methods
    formatCurrency(amount) {
        return new Intl.NumberFormat('ja-JP', {
            style: 'currency',
            currency: 'JPY'
        }).format(amount);
    }

    formatNumber(number) {
        return new Intl.NumberFormat('ja-JP').format(number);
    }

    showLoading(element) {
        element.classList.add('loading');
    }

    hideLoading(element) {
        element.classList.remove('loading');
    }
}

// Initialize the admin system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new FoodDeliveryAdmin();
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .toast-notification {
        transition: all 0.3s ease;
    }
    
    .toast-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-color);
        color: white;
    }
    
    .toast-content {
        flex: 1;
    }
    
    .toast-title {
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 2px;
    }
    
    .toast-message {
        color: var(--text-secondary);
        font-size: 0.875rem;
    }
    
    .toast-close {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .toast-close:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
    }
`;
document.head.appendChild(style);
