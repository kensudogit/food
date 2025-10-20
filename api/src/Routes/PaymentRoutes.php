<?php

declare(strict_types=1);

namespace FoodDelivery\Routes;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use FoodDelivery\Controllers\PaymentController;

class PaymentRoutes
{
    public static function register(App $app): void
    {
        $app->group('/payments', function (RouteCollectorProxy $group) {
            $group->post('/process', PaymentController::class . ':processPayment')->add(AuthMiddleware::class);
            $group->post('/refund', PaymentController::class . ':processRefund')->add(AuthMiddleware::class);
            $group->get('/methods', PaymentController::class . ':getPaymentMethods')->add(AuthMiddleware::class);
            $group->post('/methods', PaymentController::class . ':addPaymentMethod')->add(AuthMiddleware::class);
            $group->delete('/methods/{id}', PaymentController::class . ':removePaymentMethod')->add(AuthMiddleware::class);
            $group->get('/history', PaymentController::class . ':getPaymentHistory')->add(AuthMiddleware::class);
        });
    }
}
