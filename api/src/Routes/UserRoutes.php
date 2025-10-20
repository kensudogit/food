<?php

declare(strict_types=1);

namespace FoodDelivery\Routes;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use FoodDelivery\Controllers\UserController;

class UserRoutes
{
    public static function register(App $app): void
    {
        $app->group('/users', function (RouteCollectorProxy $group) {
            $group->get('/profile', UserController::class . ':getProfile')->add(AuthMiddleware::class);
            $group->put('/profile', UserController::class . ':updateProfile')->add(AuthMiddleware::class);
            $group->get('/orders', UserController::class . ':getUserOrders')->add(AuthMiddleware::class);
            $group->get('/addresses', UserController::class . ':getAddresses')->add(AuthMiddleware::class);
            $group->post('/addresses', UserController::class . ':addAddress')->add(AuthMiddleware::class);
            $group->put('/addresses/{id}', UserController::class . ':updateAddress')->add(AuthMiddleware::class);
            $group->delete('/addresses/{id}', UserController::class . ':deleteAddress')->add(AuthMiddleware::class);
        });
    }
}
