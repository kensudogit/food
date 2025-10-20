<?php

declare(strict_types=1);

namespace FoodDelivery\Service;

use PDO;
use FoodDelivery\Config\DatabaseConfig;

class WaypointService
{
    private PDO $db;
    private string $waypointPath;

    public function __construct()
    {
        $this->db = DatabaseConfig::getConnection();
        $this->waypointPath = __DIR__ . '/../../qgc_waypoints/';
    }

    /**
     * Get waypoints for a specific drone
     */
    public function getDroneWaypoints(int $droneId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM waypoints 
            WHERE drone_id = ? 
            ORDER BY sequence_number ASC
        ");
        
        $stmt->execute([$droneId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Upload and parse QGC waypoint file
     */
    public function uploadWaypointFile(int $droneId, $uploadedFile): array
    {
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload failed'];
        }

        $filename = $uploadedFile->getClientFilename();
        $filePath = $this->waypointPath . $filename;

        // Move uploaded file
        $uploadedFile->moveTo($filePath);

        // Parse the file
        return $this->parseWaypointFile($droneId, $filePath, $filename);
    }

    /**
     * Load existing QGC waypoint file
     */
    public function loadWaypointFile(int $droneId, string $filename): array
    {
        $filePath = $this->waypointPath . $filename;

        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => 'File not found'];
        }

        return $this->parseWaypointFile($droneId, $filePath, $filename);
    }

    /**
     * Parse QGC waypoint file
     */
    private function parseWaypointFile(int $droneId, string $filePath, string $filename): array
    {
        try {
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            if (empty($lines)) {
                return ['success' => false, 'error' => 'Empty file'];
            }

            // Check QGC format
            if (!str_starts_with($lines[0], 'QGC WPL')) {
                return ['success' => false, 'error' => 'Invalid QGC waypoint format'];
            }

            // Clear existing waypoints for this drone
            $this->clearDroneWaypoints($droneId);

            $waypoints = [];
            $sequenceNumber = 0;

            // Parse waypoint lines (skip header)
            for ($i = 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (empty($line)) continue;

                $parts = preg_split('/\s+/', $line);
                if (count($parts) < 12) continue;

                $waypoint = [
                    'drone_id' => $droneId,
                    'sequence_number' => $sequenceNumber++,
                    'command' => (int) $parts[3],
                    'param1' => (float) $parts[4],
                    'param2' => (float) $parts[5],
                    'param3' => (float) $parts[6],
                    'param4' => (float) $parts[7],
                    'latitude' => (float) $parts[8],
                    'longitude' => (float) $parts[9],
                    'altitude' => (float) $parts[10],
                    'auto_continue' => (int) $parts[11],
                    'source_file' => $filename,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $waypoints[] = $waypoint;
            }

            // Insert waypoints into database
            $this->insertWaypoints($waypoints);

            return [
                'success' => true,
                'data' => [
                    'drone_id' => $droneId,
                    'filename' => $filename,
                    'waypoint_count' => count($waypoints),
                    'waypoints' => $waypoints
                ]
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Failed to parse file: ' . $e->getMessage()];
        }
    }

    /**
     * Insert waypoints into database
     */
    private function insertWaypoints(array $waypoints): bool
    {
        if (empty($waypoints)) {
            return true;
        }

        $stmt = $this->db->prepare("
            INSERT INTO waypoints (
                drone_id, sequence_number, command, param1, param2, param3, param4,
                latitude, longitude, altitude, auto_continue, source_file, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($waypoints as $waypoint) {
            $success = $stmt->execute([
                $waypoint['drone_id'],
                $waypoint['sequence_number'],
                $waypoint['command'],
                $waypoint['param1'],
                $waypoint['param2'],
                $waypoint['param3'],
                $waypoint['param4'],
                $waypoint['latitude'],
                $waypoint['longitude'],
                $waypoint['altitude'],
                $waypoint['auto_continue'],
                $waypoint['source_file'],
                $waypoint['created_at']
            ]);

            if (!$success) {
                return false;
            }
        }

        return true;
    }

    /**
     * Clear waypoints for a drone
     */
    public function clearDroneWaypoints(int $droneId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM waypoints WHERE drone_id = ?");
        return $stmt->execute([$droneId]);
    }

    /**
     * Get waypoint statistics
     */
    public function getWaypointStatistics(int $droneId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_waypoints,
                MIN(latitude) as min_latitude,
                MAX(latitude) as max_latitude,
                MIN(longitude) as min_longitude,
                MAX(longitude) as max_longitude,
                MIN(altitude) as min_altitude,
                MAX(altitude) as max_altitude,
                AVG(altitude) as avg_altitude,
                source_file,
                created_at
            FROM waypoints 
            WHERE drone_id = ?
            GROUP BY source_file, created_at
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$droneId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate flight distance
     */
    public function calculateFlightDistance(int $droneId): float
    {
        $waypoints = $this->getDroneWaypoints($droneId);
        
        if (count($waypoints) < 2) {
            return 0.0;
        }

        $totalDistance = 0.0;
        
        for ($i = 0; $i < count($waypoints) - 1; $i++) {
            $current = $waypoints[$i];
            $next = $waypoints[$i + 1];
            
            $distance = $this->calculateDistance(
                $current['latitude'], $current['longitude'],
                $next['latitude'], $next['longitude']
            );
            
            $totalDistance += $distance;
        }

        return round($totalDistance, 2);
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get available QGC files
     */
    public function getAvailableQgcFiles(): array
    {
        $files = [];
        
        if (is_dir($this->waypointPath)) {
            $fileList = scandir($this->waypointPath);
            
            foreach ($fileList as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'waypoints') {
                    $filePath = $this->waypointPath . $file;
                    $files[] = [
                        'filename' => $file,
                        'size' => filesize($filePath),
                        'modified' => date('Y-m-d H:i:s', filemtime($filePath))
                    ];
                }
            }
        }
        
        return $files;
    }
}
