<?php

declare(strict_types=1);

namespace FoodDelivery\Config;

use NewRelic\Monolog\Enricher\{Handler, Processor};

class NewRelicConfig
{
    private static ?Handler $handler = null;
    private static ?Processor $processor = null;

    public static function initialize(): void
    {
        if ($_ENV['NEWRELIC_LICENSE_KEY'] ?? false) {
            // Initialize New Relic
            if (extension_loaded('newrelic')) {
                newrelic_set_appname($_ENV['NEWRELIC_APP_NAME'] ?? 'Food Delivery API');
            }

            // Create New Relic handler for Monolog
            self::$handler = new Handler();
            self::$processor = new Processor();
        }
    }

    public static function getHandler(): ?Handler
    {
        if (self::$handler === null) {
            self::initialize();
        }
        return self::$handler;
    }

    public static function getProcessor(): ?Processor
    {
        if (self::$processor === null) {
            self::initialize();
        }
        return self::$processor;
    }

    public static function addCustomAttribute(string $key, $value): void
    {
        if (extension_loaded('newrelic')) {
            newrelic_add_custom_parameter($key, $value);
        }
    }

    public static function recordCustomEvent(string $eventType, array $attributes): void
    {
        if (extension_loaded('newrelic')) {
            newrelic_record_custom_event($eventType, $attributes);
        }
    }

    public static function startTransaction(string $name): void
    {
        if (extension_loaded('newrelic')) {
            newrelic_start_transaction($name);
        }
    }

    public static function endTransaction(): void
    {
        if (extension_loaded('newrelic')) {
            newrelic_end_transaction();
        }
    }

    public static function noticeError(string $message, \Exception $exception = null): void
    {
        if (extension_loaded('newrelic')) {
            if ($exception) {
                newrelic_notice_error($message, $exception);
            } else {
                newrelic_notice_error($message);
            }
        }
    }
}
