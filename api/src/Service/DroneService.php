<?php

declare(strict_types=1);

namespace FoodDelivery\Service;

use PDO;
use FoodDelivery\Config\DatabaseConfig;

class DroneService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConfig::getConnection();
    }

    /**
     * Get all drones
     */
    public function getAllDrones(): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, 
                   COUNT(w.id) as waypoint_count,
                   MAX(w.created_at) as last_waypoint_update
            FROM drones d
            LEFT JOIN waypoints w ON d.id = w.drone_id
            GROUP BY d.id
            ORDER BY d.created_at DESC
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get drone by ID
     */
    public function getDroneById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, 
                   COUNT(w.id) as waypoint_count,
                   MAX(w.created_at) as last_waypoint_update
            FROM drones d
            LEFT JOIN waypoints w ON d.id = w.drone_id
            WHERE d.id = ?
            GROUP BY d.id
        ");
        
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    /**
     * Create new drone
     */
    public function createDrone(array $data): ?array
    {
        $requiredFields = ['name', 'model', 'serial_number'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return null;
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO drones (name, model, serial_number, status, battery_level, 
                              current_latitude, current_longitude, current_altitude, 
                              max_flight_time, max_speed, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $success = $stmt->execute([
            $data['name'],
            $data['model'],
            $data['serial_number'],
            $data['status'] ?? 'idle',
            $data['battery_level'] ?? 100,
            $data['current_latitude'] ?? null,
            $data['current_longitude'] ?? null,
            $data['current_altitude'] ?? null,
            $data['max_flight_time'] ?? 30,
            $data['max_speed'] ?? 15
        ]);

        if (!$success) {
            return null;
        }

        $droneId = $this->db->lastInsertId();
        return $this->getDroneById((int) $droneId);
    }

    /**
     * Update drone
     */
    public function updateDrone(int $id, array $data): ?array
    {
        $allowedFields = [
            'name', 'model', 'serial_number', 'status', 'battery_level',
            'current_latitude', 'current_longitude', 'current_altitude',
            'max_flight_time', 'max_speed'
        ];

        $updateFields = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return $this->getDroneById($id);
        }

        $updateFields[] = "updated_at = NOW()";
        $values[] = $id;

        $stmt = $this->db->prepare("
            UPDATE drones 
            SET " . implode(', ', $updateFields) . "
            WHERE id = ?
        ");

        $success = $stmt->execute($values);

        if (!$success) {
            return null;
        }

        return $this->getDroneById($id);
    }

    /**
     * Delete drone
     */
    public function deleteDrone(int $id): bool
    {
        // First delete associated waypoints
        $stmt = $this->db->prepare("DELETE FROM waypoints WHERE drone_id = ?");
        $stmt->execute([$id]);

        // Then delete the drone
        $stmt = $this->db->prepare("DELETE FROM drones WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get drone status
     */
    public function getDroneStatus(int $id): ?array
    {
        $drone = $this->getDroneById($id);
        
        if (!$drone) {
            return null;
        }

        return [
            'drone_id' => $drone['id'],
            'name' => $drone['name'],
            'status' => $drone['status'],
            'battery_level' => $drone['battery_level'],
            'current_position' => [
                'latitude' => $drone['current_latitude'],
                'longitude' => $drone['current_longitude'],
                'altitude' => $drone['current_altitude']
            ],
            'waypoint_count' => $drone['waypoint_count'],
            'last_update' => $drone['last_waypoint_update'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Update drone position
     */
    public function updateDronePosition(int $id, float $latitude, float $longitude, float $altitude): bool
    {
        $stmt = $this->db->prepare("
            UPDATE drones 
            SET current_latitude = ?, current_longitude = ?, current_altitude = ?, updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$latitude, $longitude, $altitude, $id]);
    }

    /**
     * Update drone battery level
     */
    public function updateDroneBattery(int $id, int $batteryLevel): bool
    {
        $stmt = $this->db->prepare("
            UPDATE drones 
            SET battery_level = ?, updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$batteryLevel, $id]);
    }

    /**
     * Update drone status
     */
    public function updateDroneStatus(int $id, string $status): bool
    {
        $allowedStatuses = ['idle', 'flying', 'landing', 'charging', 'maintenance', 'error'];
        
        if (!in_array($status, $allowedStatuses)) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE drones 
            SET status = ?, updated_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([$status, $id]);
    }
}
