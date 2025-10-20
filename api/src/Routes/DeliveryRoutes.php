<?php

declare(strict_types=1);

namespace FoodDelivery\Routes;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use FoodDelivery\Controllers\DeliveryController;

class DeliveryRoutes
{
    public static function register(App $app): void
    {
        $app->group('/delivery', function (RouteCollectorProxy $group) {
            $group->get('/drivers', DeliveryController::class . ':getDrivers')->add(AuthMiddleware::class);
            $group->get('/drivers/{id}', DeliveryController::class . ':getDriver')->add(AuthMiddleware::class);
            $group->post('/drivers/{id}/location', DeliveryController::class . ':updateDriverLocation')->add(AuthMiddleware::class);
            $group->get('/estimates', DeliveryController::class . ':getDeliveryEstimates');
            $group->post('/assign', DeliveryController::class . ':assignDriver')->add(AuthMiddleware::class);
            $group->get('/tracking/{orderId}', DeliveryController::class . ':trackDelivery');
        });
    }
}
