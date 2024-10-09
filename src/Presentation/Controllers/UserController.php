<?php

namespace Presentation\Controllers;

use Application\Commands\LoginUserCommand;
use Application\Commands\RegisterUserCommand;
use Application\DTOs\UserDTO;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Presentation\Responses\ApiResponse;

class UserController {
    private $registerUserCommand;
    private $loginCommand;
    private $logger;

    public function __construct(
        RegisterUserCommand $registerUserCommand,
        LoginUserCommand $loginCommand,
        Logger $logger) {
        $this->registerUserCommand = $registerUserCommand;
        $this->loginCommand = $loginCommand;
        $this->logger = $logger;
    }

    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $data = json_decode($request->getBody()->getContents(), true);
        $userDTO = new UserDTO();
        $userDTO->username = $data['username'];
        $userDTO->password = $data['password'];

        // Log the registration attempt
        $this->logger->info('User registration attempt', [
            'username' => $userDTO->username
        ]);

        try {
            $user = $this->registerUserCommand->execute($userDTO);

            // Log successful registration
            $this->logger->info('User registered successfully', [
                'user_id' => $user->getId(),
                'username' => $user->getUsername()
            ]);

            $apiResponse = new ApiResponse('success', [
                'id' => $user->getId(),
                'username' => $user->getUsername()
            ], 'User registered successfully.');

            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            // Log registration error
            $this->logger->error('User registration failed', [
                'username' => $userDTO->username,
                'error' => $e->getMessage()
            ]);

            $apiResponse = new ApiResponse('error', null, $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
        $data = json_decode($request->getBody(), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        // Log the login attempt
        $this->logger->info('User login attempt', [
            'username' => $username
        ]);

        try {
            $loginResult = $this->loginCommand->execute($username, $password);

            if ($loginResult) {
                // Log successful login
                $this->logger->info('User logged in successfully', [
                    'username' => $username
                ]);

                $apiResponse = new ApiResponse('success', $loginResult, 'Login successful.');
                $response->getBody()->write($apiResponse->toJson());
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
            }

            throw new \Exception('Invalid username or password');
        } catch (\Exception $e) {
            // Log login failure
            $this->logger->error('User login failed', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);

            $apiResponse = new ApiResponse('error', null, $e->getMessage());
            $response->getBody()->write($apiResponse->toJson());
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }
}
