<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class GroupApiTest extends TestCase
{
    public function testCreateGroupEndpoint()
    {
        $client = new Client();
        
        // Create a new group
        $response = $client->post('http://localhost/api/groups', [
            'json' => [
                'name' => 'Test Group',
                'user_id' => 1  // Assuming user ID 1 is the creator
            ]
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('Test Group', $responseData['name']);
    }

    public function testJoinGroupEndpoint()
    {
        $client = new Client();

        // User joins an existing group
        $response = $client->post('http://localhost/api/groups/1/join', [
            'json' => [
                'user_id' => 2  // Assuming user ID 2 is joining group 1
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
    }
}
