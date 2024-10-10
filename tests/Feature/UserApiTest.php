<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class UserApiTest extends TestCase
{
    private $client;
    
    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:8080',
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }

    public function testRegisterEndpoint()
    {
        $response = $this->client->post('/api/v1/register', [
            'json' => [
                'username' => 'test_user',
                'password' => 'password'
            ]
        ]);

        // Assert correct response status
        $this->assertEquals(201, $response->getStatusCode());

        // Decode response and assert structure
        $responseData = json_decode($response->getBody()->getContents(), true);

        // Check if response follows the generic response structure
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('id', $responseData['data']);
        $this->assertEquals('test_user', $responseData['data']['username']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User registered successfully.', $responseData['message']);
    }

    public function testLoginEndpoint()
    {
        $response = $this->client->post('/api/v1/login', [
            'json' => [
                'username' => 'test_user',
                'password' => 'password'
            ]
        ]);

        // Assert correct response status
        $this->assertEquals(200, $response->getStatusCode());

        // Decode response and assert structure
        $responseData = json_decode($response->getBody()->getContents(), true);

        // Check if response follows the generic response structure
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('token', $responseData['data']); // Assuming 'token' is returned in login response
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Login successful.', $responseData['message']);
    }
}
