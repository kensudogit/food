-- Sales Management System Database Schema
-- 売上管理システム用のデータベーススキーマ

USE food_delivery;

-- Sales Analytics Tables
-- 売上分析テーブル

-- Daily Sales Summary Table
-- 日次売上サマリーテーブル
CREATE TABLE daily_sales_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    restaurant_id INT NOT NULL,
    total_orders INT NOT NULL DEFAULT 0,
    total_revenue DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    total_delivery_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_service_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_tax DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_discount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    net_revenue DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    average_order_value DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    peak_hour_start TIME,
    peak_hour_end TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_restaurant_date (restaurant_id, date),
    INDEX idx_date (date),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_revenue (total_revenue)
);

-- Monthly Sales Summary Table
-- 月次売上サマリーテーブル
CREATE TABLE monthly_sales_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL,
    month INT NOT NULL,
    restaurant_id INT NOT NULL,
    total_orders INT NOT NULL DEFAULT 0,
    total_revenue DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    total_delivery_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_service_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_tax DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    total_discount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    net_revenue DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    average_order_value DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    growth_rate DECIMAL(5, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_restaurant_year_month (restaurant_id, year, month),
    INDEX idx_year_month (year, month),
    INDEX idx_restaurant_id (restaurant_id)
);

-- Sales Analytics Table
-- 売上分析テーブル
CREATE TABLE sales_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    analysis_date DATE NOT NULL,
    analysis_type ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_orders INT NOT NULL DEFAULT 0,
    total_revenue DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    total_customers INT NOT NULL DEFAULT 0,
    repeat_customers INT NOT NULL DEFAULT 0,
    new_customers INT NOT NULL DEFAULT 0,
    average_order_value DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    customer_lifetime_value DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    conversion_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    retention_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    peak_hours JSON,
    popular_items JSON,
    revenue_by_hour JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_analysis_date (analysis_date),
    INDEX idx_analysis_type (analysis_type),
    INDEX idx_period (period_start, period_end)
);

-- Restaurant Performance Metrics
-- レストランパフォーマンス指標テーブル
CREATE TABLE restaurant_performance_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    metric_date DATE NOT NULL,
    order_completion_time_avg INT NOT NULL DEFAULT 0,
    delivery_time_avg INT NOT NULL DEFAULT 0,
    customer_satisfaction_score DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
    order_accuracy_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    cancellation_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    refund_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    driver_rating_avg DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
    food_rating_avg DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
    service_rating_avg DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_restaurant_date (restaurant_id, metric_date),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_metric_date (metric_date)
);

-- Commission and Fees Table
-- 手数料・料金テーブル
CREATE TABLE commission_fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    order_id INT NOT NULL,
    commission_type ENUM('platform', 'delivery', 'payment', 'service') NOT NULL,
    commission_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    commission_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    base_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    fee_date DATE NOT NULL,
    status ENUM('pending', 'collected', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_order_id (order_id),
    INDEX idx_fee_date (fee_date),
    INDEX idx_status (status)
);

-- Revenue Tracking Table
-- 収益追跡テーブル
CREATE TABLE revenue_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    order_id INT NOT NULL,
    revenue_type ENUM('food_sales', 'delivery_fee', 'service_fee', 'commission', 'tip') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'JPY',
    payment_method VARCHAR(50),
    transaction_date TIMESTAMP NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_order_id (order_id),
    INDEX idx_revenue_type (revenue_type),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_status (status)
);

-- Admin Users Table
-- 管理者ユーザーテーブル
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'manager', 'analyst') NOT NULL DEFAULT 'analyst',
    permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
);

-- Admin Activity Log Table
-- 管理者活動ログテーブル
CREATE TABLE admin_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50) NOT NULL,
    resource_id VARCHAR(50),
    details JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE CASCADE,
    INDEX idx_admin_user_id (admin_user_id),
    INDEX idx_action (action),
    INDEX idx_resource_type (resource_type),
    INDEX idx_created_at (created_at)
);

-- Restaurant Management Settings
-- レストラン管理設定テーブル
CREATE TABLE restaurant_management_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_restaurant_setting (restaurant_id, setting_key),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_setting_key (setting_key)
);

-- Delivery Performance Tracking
-- 配送パフォーマンス追跡テーブル
CREATE TABLE delivery_performance_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    driver_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    pickup_time TIMESTAMP NULL,
    delivery_time TIMESTAMP NULL,
    estimated_delivery_time TIMESTAMP NOT NULL,
    actual_delivery_time TIMESTAMP NULL,
    delivery_duration_minutes INT NULL,
    distance_km DECIMAL(8, 2) NULL,
    delivery_fee DECIMAL(8, 2) NOT NULL DEFAULT 0.00,
    tip_amount DECIMAL(8, 2) NOT NULL DEFAULT 0.00,
    customer_rating INT CHECK (customer_rating >= 1 AND customer_rating <= 5),
    delivery_status ENUM('assigned', 'picked_up', 'in_transit', 'delivered', 'failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES delivery_drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_driver_id (driver_id),
    INDEX idx_restaurant_id (restaurant_id),
    INDEX idx_delivery_status (delivery_status),
    INDEX idx_delivery_time (delivery_time)
);

-- Insert sample admin user
INSERT INTO admin_users (uuid, username, email, password_hash, first_name, last_name, role) VALUES
('550e8400-e29b-41d4-a716-446655440100', 'admin', 'admin@fooddelivery.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'super_admin'),
('550e8400-e29b-41d4-a716-446655440101', 'manager', 'manager@fooddelivery.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager', 'User', 'manager'),
('550e8400-e29b-41d4-a716-446655440102', 'analyst', 'analyst@fooddelivery.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Analyst', 'User', 'analyst');

-- Insert sample restaurant management settings
INSERT INTO restaurant_management_settings (restaurant_id, setting_key, setting_value, setting_type) VALUES
(1, 'commission_rate', '15.00', 'number'),
(1, 'delivery_fee_rate', '200.00', 'number'),
(1, 'service_fee_rate', '5.00', 'number'),
(1, 'auto_accept_orders', 'true', 'boolean'),
(1, 'max_delivery_distance', '10.00', 'number'),
(2, 'commission_rate', '12.00', 'number'),
(2, 'delivery_fee_rate', '300.00', 'number'),
(2, 'service_fee_rate', '5.00', 'number'),
(2, 'auto_accept_orders', 'false', 'boolean'),
(2, 'max_delivery_distance', '15.00', 'number');
