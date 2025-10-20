<?php

declare(strict_types=1);

namespace FoodDelivery\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use FoodDelivery\Config\MonitoringConfig;

class LoggingMiddleware
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('api');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/api.log', Logger::INFO));
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $startTime = microtime(true);
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        
        // Log request
        $this->logger->info('Request received', [
            'method' => $method,
            'path' => $path,
            'ip' => $ip,
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        // Process request
        $response = $handler->handle($request);
        
        // Calculate response time
        $responseTime = microtime(true) - $startTime;
        $statusCode = $response->getStatusCode();
        
        // Log response
        $this->logger->info('Response sent', [
            'method' => $method,
            'path' => $path,
            'status_code' => $statusCode,
            'response_time' => round($responseTime * 1000, 2) . 'ms',
            'ip' => $ip
        ]);

        // Send metrics to monitoring
        MonitoringConfig::timing('api.response_time', $responseTime * 1000, [
            'method' => $method,
            'path' => $path,
            'status_code' => (string)$statusCode
        ]);

        MonitoringConfig::increment('api.requests', 1, [
            'method' => $method,
            'path' => $path,
            'status_code' => (string)$statusCode
        ]);

        return $response;
    }
}
