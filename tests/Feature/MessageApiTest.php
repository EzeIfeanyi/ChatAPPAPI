<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class MessageApiTest extends TestCase
{
    public function testSendMessageEndpoint()
    {
        $client = new Client();

        // Send a message in a group
        $response = $client->post('http://localhost/api/messages', [
            'json' => [
                'group_id' => 1,  // Assuming group ID 1 exists
                'user_id' => 1,   // Assuming user ID 1 is sending the message
                'content' => 'Hello, Group!'
            ]
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('Hello, Group!', $responseData['content']);
    }

    public function testRetrieveMessagesEndpoint()
    {
        $client = new Client();

        // Retrieve all messages in a group
        $response = $client->get('http://localhost/api/groups/1/messages');

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getBody(), true);
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        // Check the structure of the first message
        $firstMessage = $responseData[0];
        $this->assertArrayHasKey('id', $firstMessage);
        $this->assertArrayHasKey('content', $firstMessage);
        $this->assertArrayHasKey('user_id', $firstMessage);
    }
}
