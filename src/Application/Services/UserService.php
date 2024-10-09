<?php

namespace Application\Services;

use Domain\Entities\User;
use Domain\Repositories\UserRepositoryInterface;
use Application\DTOs\UserDTO;
use Application\Validators\UserValidator;
use Firebase\JWT\JWT;
use Monolog\Logger;

class UserService implements UserServiceInterface {
    private $userRepository;
    private $jwtSettings;
    private $logger;

    public function __construct(
        UserRepositoryInterface $userRepository,
        array $jwtSettings,
        Logger $logger) {
        $this->userRepository = $userRepository;
        $this->jwtSettings = $jwtSettings;
        $this->logger = $logger;
    }

    public function register(UserDTO $userDTO): User {
        UserValidator::validate($userDTO);
        
        // Log the registration attempt
        $this->logger->info('User registration attempt', [
            'username' => $userDTO->username,
        ]);

        $user = new User(null, $userDTO->username, password_hash($userDTO->password, PASSWORD_DEFAULT));
        $this->userRepository->save($user);
        
        // Log successful registration
        $this->logger->info('User registered successfully', [
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
        ]);

        return $user;
    }

    public function login($username, $password): ?array {
        $this->logger->info('User login attempt', [
            'username' => $username,
        ]);

        $user = $this->userRepository->findByUsername($username);

        if ($user && password_verify($password, $user->getPassword())) {
            // Log successful login
            $this->logger->info('User logged in successfully', [
                'user_id' => $user->getId(),
                'username' => $user->getUsername(),
            ]);

            // Generate a JWT token for the user
            $token = $this->generateToken($user);

            // Return user data and token
            return [
                'user' => [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                ],
                'token' => $token
            ];
        }

        // Log failed login attempt
        $this->logger->warning('Failed login attempt', [
            'username' => $username,
        ]);

        return null;
    }

    private function generateToken(User $user): string {
        $payload = [
            'iss' => $this->jwtSettings['issuer'],
            'iat' => $this->jwtSettings['issued_at'],
            'exp' => $this->jwtSettings['expiration_time'],
            'sub' => $user->getId(),
            'username' => $user->getUsername()
        ];

        // Generate the JWT using the secret key and algorithm from settings
        return JWT::encode($payload, $this->jwtSettings['secret'], $this->jwtSettings['algorithm']);
    }
}
