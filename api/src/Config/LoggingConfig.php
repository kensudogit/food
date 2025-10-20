<?php

declare(strict_types=1);

namespace FoodDelivery\Config;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\DatadogHandler;
use FoodDelivery\Config\NewRelicConfig;

class LoggingConfig
{
    private static ?Logger $logger = null;

    public static function initialize(): Logger
    {
        if (self::$logger === null) {
            self::$logger = new Logger('food-delivery-api');
            
            // File handler
            $fileHandler = new StreamHandler(
                $_ENV['LOG_FILE_PATH'] ?? __DIR__ . '/../../logs/app.log',
                Logger::INFO
            );
            self::$logger->pushHandler($fileHandler);

            // Datadog handler
            if ($_ENV['DATADOG_API_KEY'] ?? false) {
                $datadogHandler = new DatadogHandler($_ENV['DATADOG_API_KEY']);
                self::$logger->pushHandler($datadogHandler);
            }

            // New Relic handler
            $newRelicHandler = NewRelicConfig::getHandler();
            if ($newRelicHandler) {
                self::$logger->pushHandler($newRelicHandler);
                self::$logger->pushProcessor(NewRelicConfig::getProcessor());
            }

            // Add custom processors
            self::$logger->pushProcessor(function ($record) {
                $record['extra']['service'] = 'food-delivery-api';
                $record['extra']['version'] = $_ENV['APP_VERSION'] ?? '1.0.0';
                $record['extra']['environment'] = $_ENV['APP_ENV'] ?? 'production';
                return $record;
            });
        }

        return self::$logger;
    }

    public static function getLogger(): Logger
    {
        if (self::$logger === null) {
            return self::initialize();
        }
        return self::$logger;
    }

    public static function logApiRequest(string $method, string $path, int $statusCode, float $responseTime): void
    {
        $logger = self::getLogger();
        
        $logger->info('API Request', [
            'method' => $method,
            'path' => $path,
            'status_code' => $statusCode,
            'response_time_ms' => round($responseTime * 1000, 2),
            'type' => 'api_request'
        ]);

        // Send to New Relic
        NewRelicConfig::recordCustomEvent('ApiRequest', [
            'method' => $method,
            'path' => $path,
            'status_code' => $statusCode,
            'response_time_ms' => round($responseTime * 1000, 2)
        ]);
    }

    public static function logDatabaseQuery(string $query, float $executionTime): void
    {
        $logger = self::getLogger();
        
        $logger->debug('Database Query', [
            'query' => $query,
            'execution_time_ms' => round($executionTime * 1000, 2),
            'type' => 'database_query'
        ]);
    }

    public static function logError(\Exception $exception, array $context = []): void
    {
        $logger = self::getLogger();
        
        $logger->error('Application Error', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'context' => $context,
            'type' => 'error'
        ]);

        // Send to New Relic
        NewRelicConfig::noticeError($exception->getMessage(), $exception);
    }

    public static function logBusinessEvent(string $event, array $data): void
    {
        $logger = self::getLogger();
        
        $logger->info('Business Event', [
            'event' => $event,
            'data' => $data,
            'type' => 'business_event'
        ]);

        // Send to New Relic
        NewRelicConfig::recordCustomEvent('BusinessEvent', array_merge(['event' => $event], $data));
    }
}
