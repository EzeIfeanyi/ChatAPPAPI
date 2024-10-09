<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class UserApiTest extends TestCase
{
    public function testRegisterEndpoint()
    {
        $client = new Client();
        $response = $client->post('http://localhost/register', [
            'json' => [
                'username' => 'test_user',
                'password' => 'password'
            ]
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('test_user', $responseData['username']);
    }

    public function testLoginEndpoint()
    {
        $client = new Client();
        $response = $client->post('http://localhost/login', [
            'json' => [
                'username' => 'test_user',
                'password' => 'password'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('token', $responseData);
    }
}
