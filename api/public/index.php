<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ContentLengthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use FoodDelivery\Middleware\CorsMiddleware;
use FoodDelivery\Middleware\AuthMiddleware;
use FoodDelivery\Middleware\LoggingMiddleware;
use FoodDelivery\Middleware\RateLimitMiddleware;
use FoodDelivery\Routes\RestaurantRoutes;
use FoodDelivery\Routes\OrderRoutes;
use FoodDelivery\Routes\UserRoutes;
use FoodDelivery\Routes\DeliveryRoutes;
use FoodDelivery\Routes\PaymentRoutes;
use FoodDelivery\Routes\DroneRoutes;
use FoodDelivery\Config\DatabaseConfig;
use FoodDelivery\Config\RedisConfig;
use FoodDelivery\Config\MemcachedConfig;
use FoodDelivery\Config\MonitoringConfig;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\DatadogHandler;
use Monolog\Handler\NewRelicHandler;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Create Slim app
$app = AppFactory::create();

// Set base path
$app->setBasePath('/api/v1');

// Add middleware
$app->add(new ContentLengthMiddleware());
$app->add(new BodyParsingMiddleware());
$app->add(new CorsMiddleware());
$app->add(new LoggingMiddleware());
$app->add(new RateLimitMiddleware());

// Error handling
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Logger setup
$logger = new Logger('food-delivery-api');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::INFO));

// Add monitoring handlers if configured
if ($_ENV['DATADOG_API_KEY'] ?? false) {
    $logger->pushHandler(new DatadogHandler($_ENV['DATADOG_API_KEY']));
}

if ($_ENV['NEWRELIC_LICENSE_KEY'] ?? false) {
    $logger->pushHandler(new NewRelicHandler());
}

// Database configuration
DatabaseConfig::initialize();
RedisConfig::initialize();
MemcachedConfig::initialize();
MonitoringConfig::initialize();

// Register routes
$app->group('/restaurants', RestaurantRoutes::class);
$app->group('/orders', OrderRoutes::class);
$app->group('/users', UserRoutes::class);
$app->group('/delivery', DeliveryRoutes::class);
$app->group('/payments', PaymentRoutes::class);
$app->group('/drones', DroneRoutes::class);

// Health check endpoint
$app->get('/health', function (Request $request, Response $response) {
    $data = [
        'status' => 'healthy',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'services' => [
            'database' => DatabaseConfig::checkConnection(),
            'redis' => RedisConfig::checkConnection(),
            'memcached' => MemcachedConfig::checkConnection()
        ]
    ];
    
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// Run the application
$app->run();
