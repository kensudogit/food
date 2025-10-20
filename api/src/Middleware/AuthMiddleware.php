<?php

declare(strict_types=1);

namespace FoodDelivery\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class AuthMiddleware
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('auth');
        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/auth.log', Logger::INFO));
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $path = $request->getUri()->getPath();
        
        // Skip auth for public endpoints
        if ($this->isPublicEndpoint($path)) {
            return $handler->handle($request);
        }

        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            $this->logger->warning('Missing or invalid authorization header', [
                'path' => $path,
                'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => 'Unauthorized',
                'message' => 'Missing or invalid authorization token'
            ]));
            
            return $response
                ->withStatus(401)
                ->withHeader('Content-Type', 'application/json');
        }

        try {
            $token = substr($authHeader, 7);
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            
            // Add user info to request attributes
            $request = $request->withAttribute('user', $decoded);
            
            $this->logger->info('User authenticated successfully', [
                'user_id' => $decoded->user_id ?? 'unknown',
                'path' => $path
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('JWT validation failed', [
                'error' => $e->getMessage(),
                'path' => $path,
                'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => 'Unauthorized',
                'message' => 'Invalid token'
            ]));
            
            return $response
                ->withStatus(401)
                ->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }

    private function isPublicEndpoint(string $path): bool
    {
        $publicPaths = [
            '/api/v1/health',
            '/api/v1/auth/login',
            '/api/v1/auth/register',
            '/api/v1/restaurants',
            '/api/v1/restaurants/{id}/menu'
        ];

        foreach ($publicPaths as $publicPath) {
            if (fnmatch($publicPath, $path)) {
                return true;
            }
        }

        return false;
    }
}
