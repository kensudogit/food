-- Drone Management Database Schema
-- This schema extends the existing food delivery database with drone and waypoint management

-- Drones table
CREATE TABLE IF NOT EXISTS drones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    serial_number VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('idle', 'flying', 'landing', 'charging', 'maintenance', 'error') DEFAULT 'idle',
    battery_level INT DEFAULT 100 CHECK (battery_level >= 0 AND battery_level <= 100),
    current_latitude DECIMAL(10, 8) NULL,
    current_longitude DECIMAL(11, 8) NULL,
    current_altitude DECIMAL(8, 2) NULL,
    max_flight_time INT DEFAULT 30 COMMENT 'Maximum flight time in minutes',
    max_speed DECIMAL(5, 2) DEFAULT 15.0 COMMENT 'Maximum speed in m/s',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_serial_number (serial_number),
    INDEX idx_created_at (created_at)
);

-- Waypoints table for storing QGC waypoint data
CREATE TABLE IF NOT EXISTS waypoints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    drone_id INT NOT NULL,
    sequence_number INT NOT NULL,
    command INT NOT NULL COMMENT 'MAVLink command',
    param1 DECIMAL(10, 6) DEFAULT 0,
    param2 DECIMAL(10, 6) DEFAULT 0,
    param3 DECIMAL(10, 6) DEFAULT 0,
    param4 DECIMAL(10, 6) DEFAULT 0,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    altitude DECIMAL(8, 2) NOT NULL,
    auto_continue TINYINT(1) DEFAULT 1,
    source_file VARCHAR(255) NULL COMMENT 'Original QGC file name',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (drone_id) REFERENCES drones(id) ON DELETE CASCADE,
    INDEX idx_drone_sequence (drone_id, sequence_number),
    INDEX idx_coordinates (latitude, longitude),
    INDEX idx_source_file (source_file)
);

-- Flight logs table for tracking drone flights
CREATE TABLE IF NOT EXISTS flight_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    drone_id INT NOT NULL,
    flight_name VARCHAR(100) NULL,
    start_time TIMESTAMP NULL,
    end_time TIMESTAMP NULL,
    start_latitude DECIMAL(10, 8) NULL,
    start_longitude DECIMAL(11, 8) NULL,
    start_altitude DECIMAL(8, 2) NULL,
    end_latitude DECIMAL(10, 8) NULL,
    end_longitude DECIMAL(11, 8) NULL,
    end_altitude DECIMAL(8, 2) NULL,
    total_distance DECIMAL(10, 2) DEFAULT 0 COMMENT 'Total distance in meters',
    flight_duration INT DEFAULT 0 COMMENT 'Flight duration in seconds',
    battery_start INT NULL,
    battery_end INT NULL,
    status ENUM('planned', 'in_progress', 'completed', 'cancelled', 'error') DEFAULT 'planned',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (drone_id) REFERENCES drones(id) ON DELETE CASCADE,
    INDEX idx_drone_flight (drone_id, start_time),
    INDEX idx_status (status)
);

-- Flight waypoint logs for tracking waypoint execution
CREATE TABLE IF NOT EXISTS flight_waypoint_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_log_id INT NOT NULL,
    waypoint_id INT NOT NULL,
    sequence_number INT NOT NULL,
    reached_at TIMESTAMP NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    altitude DECIMAL(8, 2) NULL,
    battery_level INT NULL,
    status ENUM('pending', 'reached', 'skipped', 'error') DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (flight_log_id) REFERENCES flight_logs(id) ON DELETE CASCADE,
    FOREIGN KEY (waypoint_id) REFERENCES waypoints(id) ON DELETE CASCADE,
    INDEX idx_flight_sequence (flight_log_id, sequence_number),
    INDEX idx_status (status)
);

-- Drone telemetry data for real-time monitoring
CREATE TABLE IF NOT EXISTS drone_telemetry (
    id INT AUTO_INCREMENT PRIMARY KEY,
    drone_id INT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    altitude DECIMAL(8, 2) NOT NULL,
    battery_level INT NOT NULL,
    speed DECIMAL(5, 2) NULL COMMENT 'Speed in m/s',
    heading DECIMAL(5, 2) NULL COMMENT 'Heading in degrees',
    temperature DECIMAL(5, 2) NULL COMMENT 'Temperature in Celsius',
    signal_strength INT NULL COMMENT 'Signal strength percentage',
    gps_satellites INT NULL COMMENT 'Number of GPS satellites',
    flight_mode VARCHAR(50) NULL,
    
    FOREIGN KEY (drone_id) REFERENCES drones(id) ON DELETE CASCADE,
    INDEX idx_drone_timestamp (drone_id, timestamp),
    INDEX idx_timestamp (timestamp)
);

-- Drone maintenance records
CREATE TABLE IF NOT EXISTS drone_maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    drone_id INT NOT NULL,
    maintenance_type ENUM('routine', 'repair', 'inspection', 'upgrade') NOT NULL,
    description TEXT NOT NULL,
    performed_by VARCHAR(100) NULL,
    maintenance_date DATE NOT NULL,
    next_maintenance_date DATE NULL,
    cost DECIMAL(10, 2) NULL,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (drone_id) REFERENCES drones(id) ON DELETE CASCADE,
    INDEX idx_drone_maintenance (drone_id, maintenance_date),
    INDEX idx_status (status)
);

-- Insert sample drones
INSERT INTO drones (name, model, serial_number, status, battery_level, max_flight_time, max_speed) VALUES
('Drone Alpha', 'DJI Phantom 4 Pro', 'DJI-P4P-001', 'idle', 100, 30, 20.0),
('Drone Beta', 'DJI Mavic Air 2', 'DJI-MA2-002', 'idle', 95, 25, 19.0),
('Drone Gamma', 'DJI Mini 3 Pro', 'DJI-M3P-003', 'charging', 45, 20, 16.0),
('Drone Delta', 'Autel EVO II', 'AUTEL-EVO2-004', 'maintenance', 0, 35, 18.0),
('Drone Epsilon', 'Parrot Anafi', 'PARROT-ANAFI-005', 'idle', 88, 22, 15.0);

-- Create views for common queries
CREATE VIEW drone_status_summary AS
SELECT 
    d.id,
    d.name,
    d.model,
    d.status,
    d.battery_level,
    d.current_latitude,
    d.current_longitude,
    d.current_altitude,
    COUNT(w.id) as waypoint_count,
    MAX(w.created_at) as last_waypoint_update,
    COUNT(fl.id) as total_flights,
    MAX(fl.end_time) as last_flight_time
FROM drones d
LEFT JOIN waypoints w ON d.id = w.drone_id
LEFT JOIN flight_logs fl ON d.id = fl.drone_id
GROUP BY d.id;

CREATE VIEW flight_statistics AS
SELECT 
    fl.id,
    fl.drone_id,
    d.name as drone_name,
    fl.flight_name,
    fl.start_time,
    fl.end_time,
    fl.total_distance,
    fl.flight_duration,
    fl.battery_start,
    fl.battery_end,
    fl.status,
    COUNT(fwl.id) as waypoints_reached,
    COUNT(CASE WHEN fwl.status = 'reached' THEN 1 END) as waypoints_completed
FROM flight_logs fl
JOIN drones d ON fl.drone_id = d.id
LEFT JOIN flight_waypoint_logs fwl ON fl.id = fwl.flight_log_id
GROUP BY fl.id;
