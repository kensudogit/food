<?php

declare(strict_types=1);

namespace FoodDelivery\Grpc\Services;

use Grpc\Server;
use Grpc\ServerCredentials;
use FoodDelivery\Grpc\Proto\RestaurantServiceInterface;
use FoodDelivery\Grpc\Proto\OrderServiceInterface;
use FoodDelivery\Grpc\Proto\UserServiceInterface;
use FoodDelivery\Grpc\Proto\DeliveryServiceInterface;
use FoodDelivery\Grpc\Proto\PaymentServiceInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class GrpcServer
{
    private Server $server;
    private Logger $logger;
    private array $services;

    public function __construct()
    {
        $this->server = new Server([]);
        $this->logger = new Logger('grpc-server');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../../logs/grpc.log', Logger::INFO));
        
        $this->services = [
            'restaurant' => new RestaurantServiceInterface(),
            'order' => new OrderServiceInterface(),
            'user' => new UserServiceInterface(),
            'delivery' => new DeliveryServiceInterface(),
            'payment' => new PaymentServiceInterface(),
        ];
    }

    public function start(string $host = '0.0.0.0', int $port = 50051): void
    {
        $address = "{$host}:{$port}";
        
        // Add services to server
        foreach ($this->services as $name => $service) {
            $this->server->addService($service);
            $this->logger->info("Added {$name} service to gRPC server");
        }

        // Start server
        $this->server->addHttp2Port($address, ServerCredentials::createInsecure());
        
        $this->logger->info("gRPC server starting on {$address}");
        
        $this->server->start();
    }

    public function stop(): void
    {
        $this->logger->info("Stopping gRPC server");
        $this->server->shutdown();
    }

    public function wait(): void
    {
        $this->server->wait();
    }
}
