<?php

declare(strict_types=1);

namespace FoodDelivery\Admin;

use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\BodyParsingMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FoodDelivery\Admin\Controllers\DashboardController;
use FoodDelivery\Admin\Controllers\SalesController;
use FoodDelivery\Admin\Controllers\RestaurantController;
use FoodDelivery\Admin\Controllers\OrderController;
use FoodDelivery\Admin\Middleware\AuthMiddleware;
use FoodDelivery\Admin\Middleware\AdminLoggingMiddleware;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;

require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Create Slim app
$app = AppFactory::create();

// Set base path
$app->setBasePath('/admin');

// Add middleware
$app->add(new BodyParsingMiddleware());
$app->add(new AdminLoggingMiddleware());
$app->add(new AuthMiddleware());

// Error handling
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Logger setup
$logger = new Logger('food-delivery-admin');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/admin.log', Logger::INFO));

// Register routes
$app->get('/', DashboardController::class . ':index');
$app->get('/dashboard', DashboardController::class . ':index');
$app->get('/sales', SalesController::class . ':index');
$app->get('/sales/data', SalesController::class . ':getSalesData');
$app->get('/sales/export', SalesController::class . ':exportData');
$app->get('/restaurants', RestaurantController::class . ':index');
$app->get('/restaurants/{id}', RestaurantController::class . ':show');
$app->get('/orders', OrderController::class . ':index');
$app->get('/orders/{id}', OrderController::class . ':show');

// API endpoints for AJAX requests
$app->group('/api', function ($group) {
    $group->get('/dashboard/stats', DashboardController::class . ':getStats');
    $group->get('/sales/analytics', SalesController::class . ':getAnalytics');
    $group->get('/restaurants/list', RestaurantController::class . ':getList');
    $group->get('/orders/list', OrderController::class . ':getList');
});

// Health check endpoint
$app->get('/health', function (Request $request, Response $response) {
    $data = [
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'service' => 'admin-panel'
    ];
    
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// Run the application
$app->run();
