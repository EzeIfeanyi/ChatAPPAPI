<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MessageApiTest extends TestCase
{
    private $client;
    private $token;

    protected function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:8080', // Assuming server runs on port 8000
            'headers' => ['Content-Type' => 'application/json']
        ]);

        // Log in and get the token
        $response = $this->client->post('/api/v1/login', [
            'json' => [
                'username' => 'test_user',
                'password' => 'password'
            ]
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);
        $this->token = $responseData['data']['token']; // Assuming token is in the 'data' field
    }

    public function testSendMessageEndpoint()
    {
        try {
            // Send a message in a group
            $response = $this->client->post('/api/v1/messages', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token // Add token for authorization
                ],
                'json' => [
                    'user_id' => 1,
                    'group_id' => 1,   // Assuming group ID 1 exists
                    'content' => 'Hello, Group!' // Send message content
                ]
            ]);

            // Assert response status
            $this->assertEquals(201, $response->getStatusCode());

            // Decode response and assert structure
            $responseData = json_decode($response->getBody()->getContents(), true);
            $this->assertArrayHasKey('status', $responseData);
            $this->assertEquals('success', $responseData['status']);
            $this->assertArrayHasKey('data', $responseData);
            $this->assertArrayHasKey('id', $responseData['data']);
            $this->assertEquals('Hello, Group!', $responseData['data']['content']);
        } catch (RequestException $e) {
            $this->fail('Failed to send message: ' . $e->getMessage());
        }
    }

    public function testRetrieveMessagesEndpoint()
    {
        try {
            // Retrieve all messages in a group (group ID 1)
            $response = $this->client->get('/api/v1/groups/1/messages', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token // Add token for authorization
                ]
            ]);

            // Assert response status
            $this->assertEquals(200, $response->getStatusCode());

            // Decode response and assert structure
            $responseData = json_decode($response->getBody()->getContents(), true);
            $this->assertArrayHasKey('status', $responseData);
            $this->assertEquals('success', $responseData['status']);
            $this->assertArrayHasKey('data', $responseData);
            $this->assertIsArray($responseData['data']);
            $this->assertNotEmpty($responseData['data']);

            // Check structure of the first message
            $firstMessage = $responseData['data'][0];
            $this->assertArrayHasKey('id', $firstMessage);
            $this->assertArrayHasKey('content', $firstMessage);
            $this->assertArrayHasKey('user_id', $firstMessage);
        } catch (RequestException $e) {
            $this->fail('Failed to retrieve messages: ' . $e->getMessage());
        }
    }
}
