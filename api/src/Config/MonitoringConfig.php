<?php

declare(strict_types=1);

namespace FoodDelivery\Config;

use DataDog\DogStatsd;

class MonitoringConfig
{
    private static ?DogStatsd $datadog = null;

    public static function initialize(): void
    {
        if ($_ENV['DATADOG_API_KEY'] ?? false) {
            self::$datadog = new DogStatsd([
                'host' => $_ENV['DATADOG_HOST'] ?? 'localhost',
                'port' => $_ENV['DATADOG_PORT'] ?? 8125,
                'global_tags' => [
                    'service' => 'food-delivery-api',
                    'environment' => $_ENV['APP_ENV'] ?? 'production'
                ]
            ]);
        }
    }

    public static function getDatadog(): ?DogStatsd
    {
        if (self::$datadog === null) {
            self::initialize();
        }
        return self::$datadog;
    }

    public static function increment(string $metric, int $value = 1, array $tags = []): void
    {
        $datadog = self::getDatadog();
        if ($datadog) {
            $datadog->increment($metric, $value, $tags);
        }
    }

    public static function timing(string $metric, float $time, array $tags = []): void
    {
        $datadog = self::getDatadog();
        if ($datadog) {
            $datadog->timing($metric, $time, $tags);
        }
    }

    public static function gauge(string $metric, float $value, array $tags = []): void
    {
        $datadog = self::getDatadog();
        if ($datadog) {
            $datadog->gauge($metric, $value, $tags);
        }
    }

    public static function histogram(string $metric, float $value, array $tags = []): void
    {
        $datadog = self::getDatadog();
        if ($datadog) {
            $datadog->histogram($metric, $value, $tags);
        }
    }
}
