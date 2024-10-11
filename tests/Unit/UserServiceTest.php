<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Application\Services\UserService;
use Domain\Repositories\UserRepositoryInterface;
use Application\DTOs\UserDTO;
use Firebase\JWT\JWT;
use Domain\Entities\User;
use Monolog\Logger;

class UserServiceTest extends TestCase
{
    private $userRepository;
    private $logger;
    private $jwtSettings;
    private $userService;

    public function setUp(): void
    {
        // Mock the repository and logger
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->logger = $this->createMock(Logger::class);

        // JWT settings
        $this->jwtSettings = [
            'issuer' => 'chat',
            'secret' => 'this is the secret you need for the app',
            'algorithm' => 'HS256',
            'expiration_time' => time() + 3600,
            'issued_at' => time()
        ];

        // Initialize the service with the mocked dependencies
        $this->userService = new UserService($this->userRepository, $this->jwtSettings, $this->logger);
    }

    public function testRegisterUserSuccessfully()
    {
        $userDTO = new UserDTO();
        $userDTO->username = 'test_user';
        $userDTO->password = 'password';

        // Mock the repository save method
        $this->userRepository->expects($this->once())
            ->method('save')
            ->willReturn(new User(1, 'test_user', 'hashed_password'));

        // Expect logger to be called twice: on registration attempt and on success
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['User registration attempt', ['username' => 'test_user']],
                ['User registered successfully', ['user_id' => 1, 'username' => 'test_user']]
            );

        $user = $this->userService->register($userDTO);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test_user', $user->getUsername());
    }

    public function testLoginUserSuccessfully()
    {
        // Simulate a user entity returned from the repository
        $user = new User(1, 'test_user', password_hash('password', PASSWORD_DEFAULT));

        // Expect repository to find the user by username
        $this->userRepository->expects($this->once())
            ->method('findByUsername')
            ->with('test_user')
            ->willReturn($user);

        // Expect logger to be called on login attempt and success
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['User login attempt', ['username' => 'test_user']],
                ['User logged in successfully', ['user_id' => 1, 'username' => 'test_user']]
            );

        // Perform the login operation
        $loginResult = $this->userService->login('test_user', 'password');

        // Assert token and user data
        $this->assertNotNull($loginResult);
        $this->assertArrayHasKey('token', $loginResult);
        $this->assertEquals($user->getId(), $loginResult['user']['id']);

        // Decode and validate the JWT token structure
        $decodedToken = JWT::decode($loginResult['token'], $this->jwtSettings['secret'], $this->jwtSettings['algorithm']);
        $this->assertEquals($this->jwtSettings['issuer'], $decodedToken->iss);
        $this->assertEquals($user->getId(), $decodedToken->sub);
        $this->assertEquals('test_user', $decodedToken->username);
    }

    public function testLoginUserFailed()
    {
        // Expect repository to find no user
        $this->userRepository->expects($this->once())
            ->method('findByUsername')
            ->with('test_user')
            ->willReturn(null);

        // Expect logger to log the failed attempt
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Failed login attempt', ['username' => 'test_user']);

        // Perform the login operation
        $loginResult = $this->userService->login('test_user', 'wrong_password');

        // Assert that the login failed
        $this->assertNull($loginResult);
    }
}
