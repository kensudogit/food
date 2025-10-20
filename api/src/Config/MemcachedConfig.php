<?php

declare(strict_types=1);

namespace FoodDelivery\Config;

use Memcached;

class MemcachedConfig
{
    private static ?Memcached $client = null;

    public static function initialize(): void
    {
        self::$client = new Memcached();
        self::$client->addServer(
            $_ENV['MEMCACHED_HOST'] ?? 'localhost',
            $_ENV['MEMCACHED_PORT'] ?? 11211
        );
    }

    public static function getClient(): Memcached
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
            $stats = $client->getStats();
            if (!empty($stats)) {
                return ['status' => 'connected', 'message' => 'Memcached connection successful'];
            }
            return ['status' => 'error', 'message' => 'No Memcached servers available'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public static function cache(string $key, $value = null, int $ttl = 3600)
    {
        $client = self::getClient();
        
        if ($value === null) {
            return $client->get($key);
        }
        
        return $client->set($key, $value, $ttl);
    }

    public static function delete(string $key): bool
    {
        $client = self::getClient();
        return $client->delete($key);
    }
}
