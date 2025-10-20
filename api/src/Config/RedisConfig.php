<?php

declare(strict_types=1);

namespace FoodDelivery\Config;

use Predis\Client;
use Predis\ClientException;

class RedisConfig
{
    private static ?Client $client = null;

    public static function initialize(): void
    {
        $config = [
            'scheme' => $_ENV['REDIS_SCHEME'] ?? 'tcp',
            'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
            'port' => $_ENV['REDIS_PORT'] ?? 6379,
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
            'database' => $_ENV['REDIS_DATABASE'] ?? 0,
        ];

        self::$client = new Client($config);
    }

    public static function getClient(): Client
    {
        if (self::$client === null) {
            self::initialize();
        }
        return self::$client;
    }

    public static function checkConnection(): array
    {
        try {
            $client = self::getClient();
            $client->ping();
            return ['status' => 'connected', 'message' => 'Redis connection successful'];
        } catch (ClientException $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public static function cache(string $key, $value = null, int $ttl = 3600)
    {
        $client = self::getClient();
        
        if ($value === null) {
            return $client->get($key);
        }
        
        return $client->setex($key, $ttl, serialize($value));
    }

    public static function delete(string $key): bool
    {
        $client = self::getClient();
        return (bool) $client->del($key);
    }
}
