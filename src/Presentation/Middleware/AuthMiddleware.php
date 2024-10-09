<?php

namespace Presentation\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class AuthMiddleware implements MiddlewareInterface {
    private $secret;
    private $algorithm;

    public function __construct(string $secret, string $algorithm) {
        $this->secret = $secret;
        $this->algorithm = $algorithm;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface {
        $token = $request->getHeaderLine('Authorization');

        if (!$token) {
            return $this->unauthorizedResponse('Authorization token not provided');
        }

        // Remove "Bearer " from the token
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        try {
            // Decode the JWT token
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));

            // Add the user information to the request attributes if needed
            $request = $request->withAttribute('user', $decoded);
        } catch (ExpiredException $e) {
            return $this->unauthorizedResponse('Token has expired');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Invalid token: ' . $e->getMessage());
        }

        return $handler->handle($request);
    }

    private function unauthorizedResponse(string $message): PsrResponseInterface {
        $response = (new \Slim\Psr7\Response())
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    
        $response->getBody()->write(json_encode(['error' => $message]));
    
        return $response;
    }    
}
