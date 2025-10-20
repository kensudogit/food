<?php

declare(strict_types=1);

namespace FoodDelivery\Config;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Configuration;
use Doctrine\DBAL\Logging\DebugStack;

class DatabaseConfig
{
    private static ?Connection $connection = null;
    private static ?EntityManager $entityManager = null;

    public static function initialize(): void
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [__DIR__ . '/../src/Entity'],
            isDevMode: $_ENV['APP_ENV'] === 'development'
        );

        $connectionParams = [
            'dbname' => $_ENV['DB_NAME'] ?? 'food_delivery',
            'user' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'driver' => 'pdo_mysql',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];

        self::$connection = DriverManager::getConnection($connectionParams);
        self::$entityManager = new EntityManager(self::$connection, $config);

        // Enable SQL logging in development
        if ($_ENV['APP_ENV'] === 'development') {
            $config->setSQLLogger(new DebugStack());
        }
    }

    public static function getConnection(): Connection
    {
        if (self::$connection === null) {
            self::initialize();
        }
        return self::$connection;
    }

    public static function getEntityManager(): EntityManager
    {
        if (self::$entityManager === null) {
            self::initialize();
        }
        return self::$entityManager;
    }

    public static function checkConnection(): array
    {
        try {
            $connection = self::getConnection();
            $connection->executeQuery('SELECT 1');
            return ['status' => 'connected', 'message' => 'Database connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
