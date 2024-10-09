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
    // Declare service and dependencies
    private $userRepository;
    private $logger;
    private $jwtSettings;
    private $userService;

    public function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->logger = $this->createMock(Logger::class);
        $this->jwtSettings = [
            'issuer' => 'localhost',
            'secret' => 'secret',
            'algorithm' => 'HS256',
            'expiration_time' => time() + 3600,
            'issued_at' => time()
        ];

        $this->userService = new UserService($this->userRepository, $this->jwtSettings, $this->logger);
    }

    public function testRegisterUserSuccessfully()
    {
        $userDTO = new UserDTO();
        $userDTO->username = 'test_user';
        $userDTO->password = 'password';

        $this->userRepository->expects($this->once())
            ->method('save')
            ->willReturn(new User(1, 'test_user', 'hashed_password'));

        $user = $this->userService->register($userDTO);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test_user', $user->getUsername());
    }

    public function testLoginUserSuccessfully()
    {
        $user = new User(1, 'test_user', password_hash('password', PASSWORD_DEFAULT));

        $this->userRepository->expects($this->once())
            ->method('findByUsername')
            ->with('test_user')
            ->willReturn($user);

        $loginResult = $this->userService->login('test_user', 'password');

        $this->assertNotNull($loginResult);
        $this->assertArrayHasKey('token', $loginResult);
        $this->assertEquals($user->getId(), $loginResult['user']['id']);
    }
}
