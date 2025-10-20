<?php

declare(strict_types=1);

namespace FoodDelivery\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use FoodDelivery\Config\RedisConfig;

class RateLimitMiddleware
{
    private int $maxRequests;
    private int $windowSeconds;

    public function __construct(int $maxRequests = 100, int $windowSeconds = 3600)
    {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit:{$ip}";
        
        try {
            $redis = RedisConfig::getClient();
            $current = $redis->get($key);
            
            if ($current === null) {
                $redis->setex($key, $this->windowSeconds, 1);
            } elseif ((int)$current >= $this->maxRequests) {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode([
                    'error' => 'Rate limit exceeded',
                    'message' => 'Too many requests. Please try again later.',
                    'retry_after' => $this->windowSeconds
                ]));
                
                return $response
                    ->withStatus(429)
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('Retry-After', (string)$this->windowSeconds);
            } else {
                $redis->incr($key);
            }
            
        } catch (\Exception $e) {
            // If Redis is down, allow the request to proceed
            // Log the error but don't block the user
        }

        return $handler->handle($request);
    }
}
