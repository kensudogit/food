<?php

declare(strict_types=1);

namespace FoodDelivery\Routes;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use FoodDelivery\Controllers\RestaurantController;

class RestaurantRoutes
{
    public static function register(App $app): void
    {
        $app->group('/restaurants', function (RouteCollectorProxy $group) {
            $group->get('', RestaurantController::class . ':getRestaurants');
            $group->get('/{id}', RestaurantController::class . ':getRestaurant');
            $group->get('/{id}/menu', RestaurantController::class . ':getMenu');
            $group->get('/{id}/reviews', RestaurantController::class . ':getReviews');
            $group->post('', RestaurantController::class . ':createRestaurant')->add(AuthMiddleware::class);
            $group->put('/{id}', RestaurantController::class . ':updateRestaurant')->add(AuthMiddleware::class);
            $group->delete('/{id}', RestaurantController::class . ':deleteRestaurant')->add(AuthMiddleware::class);
        });
    }
}
