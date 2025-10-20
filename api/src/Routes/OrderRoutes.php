<?php

declare(strict_types=1);

namespace FoodDelivery\Routes;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use FoodDelivery\Controllers\OrderController;

class OrderRoutes
{
    public static function register(App $app): void
    {
        $app->group('/orders', function (RouteCollectorProxy $group) {
            $group->get('', OrderController::class . ':getOrders')->add(AuthMiddleware::class);
            $group->get('/{id}', OrderController::class . ':getOrder')->add(AuthMiddleware::class);
            $group->post('', OrderController::class . ':createOrder')->add(AuthMiddleware::class);
            $group->put('/{id}/status', OrderController::class . ':updateOrderStatus')->add(AuthMiddleware::class);
            $group->post('/{id}/cancel', OrderController::class . ':cancelOrder')->add(AuthMiddleware::class);
            $group->get('/{id}/tracking', OrderController::class . ':trackOrder')->add(AuthMiddleware::class);
        });
    }
}
