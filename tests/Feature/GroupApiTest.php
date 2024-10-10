<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GroupApiTest extends TestCase
{
    private $client;
    private $token;

    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:8080',
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $response = $this->client->post('/api/v1/login', [
            'json' => [
                'username' => 'test_user',
                'password' => 'password'
            ]
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);
        $this->token = $responseData['data']['token'];
    }

    public function testCreateGroupEndpoint()
    {
        try {
            $response = $this->client->post('/api/v1/groups', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token
                ],
                'json' => [
                    'name' => 'Test Group'
                ]
            ]);

            $this->assertEquals(201, $response->getStatusCode());

            $responseData = json_decode($response->getBody()->getContents(), true);
            $this->assertArrayHasKey('status', $responseData);
            $this->assertEquals('success', $responseData['status']);
            $this->assertArrayHasKey('data', $responseData);
            $this->assertArrayHasKey('id', $responseData['data']);
            $this->assertEquals('Test Group', $responseData['data']['name']);
            $this->assertArrayHasKey('message', $responseData);
            $this->assertEquals('Group created successfully', $responseData['message']);
        } catch (RequestException $e) {
            $this->fail('Group creation failed: ' . $e->getMessage());
        }
    }

    public function testJoinGroupEndpoint()
    {
        try {
            $response = $this->client->post('/api/v1/groups/1/join', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token
                ],
                'json' => [
                    'user_id' => 2
                ]
            ]);

            $this->assertEquals(200, $response->getStatusCode());

            $responseData = json_decode($response->getBody()->getContents(), true);
            $this->assertArrayHasKey('status', $responseData);
            $this->assertEquals('success', $responseData['status']);
            $this->assertArrayHasKey('message', $responseData);
            $this->assertEquals('Joined group successfully', $responseData['message']);
        } catch (RequestException $e) {
            $this->fail('Failed to join group: ' . $e->getMessage());
        }
    }
}
