<?php

declare(strict_types=1);

namespace FoodDelivery\Routes;

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FoodDelivery\Service\DroneService;
use FoodDelivery\Service\WaypointService;

class DroneRoutes
{
    public function __construct(App $app)
    {
        $this->registerRoutes($app);
    }

    private function registerRoutes(App $app): void
    {
        // Drone management endpoints
        $app->group('/drones', function ($group) {
            
            // Get all drones
            $group->get('', function (Request $request, Response $response) {
                $droneService = new DroneService();
                $drones = $droneService->getAllDrones();
                
                $response->getBody()->write(json_encode([
                    'success' => true,
                    'data' => $drones,
                    'count' => count($drones)
                ]));
                
                return $response->withHeader('Content-Type', 'application/json');
            });

            // Get specific drone
            $group->get('/{id}', function (Request $request, Response $response, array $args) {
                $droneId = (int) $args['id'];
                $droneService = new DroneService();
                $drone = $droneService->getDroneById($droneId);
                
                if (!$drone) {
                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'error' => 'Drone not found'
                    ]));
                    return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
                }
                
                $response->getBody()->write(json_encode([
                    'success' => true,
                    'data' => $drone
                ]));
                
                return $response->withHeader('Content-Type', 'application/json');
            });

            // Create new drone
            $group->post('', function (Request $request, Response $response) {
                $data = $request->getParsedBody();
                $droneService = new DroneService();
                
                $drone = $droneService->createDrone($data);
                
                if (!$drone) {
                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'error' => 'Failed to create drone'
                    ]));
                    return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                }
                
                $response->getBody()->write(json_encode([
                    'success' => true,
                    'data' => $drone
                ]));
                
                return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
            });

            // Update drone
            $group->put('/{id}', function (Request $request, Response $response, array $args) {
                $droneId = (int) $args['id'];
                $data = $request->getParsedBody();
                $droneService = new DroneService();
                
                $drone = $droneService->updateDrone($droneId, $data);
                
                if (!$drone) {
                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'error' => 'Drone not found or update failed'
                    ]));
                    return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
                }
                
                $response->getBody()->write(json_encode([
                    'success' => true,
                    'data' => $drone
                ]));
                
                return $response->withHeader('Content-Type', 'application/json');
            });

            // Delete drone
            $group->delete('/{id}', function (Request $request, Response $response, array $args) {
                $droneId = (int) $args['id'];
                $droneService = new DroneService();
                
                $success = $droneService->deleteDrone($droneId);
                
                if (!$success) {
                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'error' => 'Drone not found or deletion failed'
                    ]));
                    return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
                }
                
                $response->getBody()->write(json_encode([
                    'success' => true,
                    'message' => 'Drone deleted successfully'
                ]));
                
                return $response->withHeader('Content-Type', 'application/json');
            });

            // Drone status endpoints
            $group->get('/{id}/status', function (Request $request, Response $response, array $args) {
                $droneId = (int) $args['id'];
                $droneService = new DroneService();
                $status = $droneService->getDroneStatus($droneId);
                
                if (!$status) {
                    $response->getBody()->write(json_encode([
                        'success' => false,
                        'error' => 'Drone not found'
                    ]));
                    return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
                }
                
                $response->getBody()->write(json_encode([
                    'success' => true,
                    'data' => $status
                ]));
                
                return $response->withHeader('Content-Type', 'application/json');
            });

            // Waypoint management endpoints
            $group->group('/{id}/waypoints', function ($waypointGroup) {
                
                // Get drone waypoints
                $waypointGroup->get('', function (Request $request, Response $response, array $args) {
                    $droneId = (int) $args['id'];
                    $waypointService = new WaypointService();
                    $waypoints = $waypointService->getDroneWaypoints($droneId);
                    
                    $response->getBody()->write(json_encode([
                        'success' => true,
                        'data' => $waypoints,
                        'count' => count($waypoints)
                    ]));
                    
                    return $response->withHeader('Content-Type', 'application/json');
                });

                // Upload QGC waypoint file
                $waypointGroup->post('/upload', function (Request $request, Response $response, array $args) {
                    $droneId = (int) $args['id'];
                    $uploadedFiles = $request->getUploadedFiles();
                    
                    if (empty($uploadedFiles['waypoint_file'])) {
                        $response->getBody()->write(json_encode([
                            'success' => false,
                            'error' => 'No waypoint file uploaded'
                        ]));
                        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                    }
                    
                    $waypointService = new WaypointService();
                    $result = $waypointService->uploadWaypointFile($droneId, $uploadedFiles['waypoint_file']);
                    
                    if (!$result['success']) {
                        $response->getBody()->write(json_encode([
                            'success' => false,
                            'error' => $result['error']
                        ]));
                        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                    }
                    
                    $response->getBody()->write(json_encode([
                        'success' => true,
                        'data' => $result['data'],
                        'message' => 'Waypoint file uploaded and parsed successfully'
                    ]));
                    
                    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
                });

                // Load existing QGC file
                $waypointGroup->post('/load/{filename}', function (Request $request, Response $response, array $args) {
                    $droneId = (int) $args['id'];
                    $filename = $args['filename'];
                    $waypointService = new WaypointService();
                    
                    $result = $waypointService->loadWaypointFile($droneId, $filename);
                    
                    if (!$result['success']) {
                        $response->getBody()->write(json_encode([
                            'success' => false,
                            'error' => $result['error']
                        ]));
                        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                    }
                    
                    $response->getBody()->write(json_encode([
                        'success' => true,
                        'data' => $result['data'],
                        'message' => 'Waypoint file loaded successfully'
                    ]));
                    
                    return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
                });

                // Clear drone waypoints
                $waypointGroup->delete('', function (Request $request, Response $response, array $args) {
                    $droneId = (int) $args['id'];
                    $waypointService = new WaypointService();
                    
                    $success = $waypointService->clearDroneWaypoints($droneId);
                    
                    if (!$success) {
                        $response->getBody()->write(json_encode([
                            'success' => false,
                            'error' => 'Failed to clear waypoints'
                        ]));
                        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                    }
                    
                    $response->getBody()->write(json_encode([
                        'success' => true,
                        'message' => 'Waypoints cleared successfully'
                    ]));
                    
                    return $response->withHeader('Content-Type', 'application/json');
                });
            });
        });
    }
}
