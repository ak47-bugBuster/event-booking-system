<?php
/*
 * Author: Akshaya Bhandare
 * Page: Test Controller with all possible test cases for events
 * Created At: 08-Jun-2025 
*/
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    // test to check if event is successfully created
    public function testCreateEventSuccess(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/events',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Test Event',
                'description' => 'A test event description',
                'location' => 'India',
                'capacity' => 100,
                'startsAt' => '2025-06-10T10:00:00+00:00',
                'endsAt' => '2025-06-10T12:00:00+00:00',
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($client->getResponse()->getContent());
        $this->assertStringContainsString('Event created successfully', $client->getResponse()->getContent());
    }

    // test if validations are successfull
    public function testCreateEventValidationFail(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/events',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                // Missing title testing
                'description' => 'No title provided',
                'location' => 'India',
                'capacity' => 50,
                'startsAt' => '2025-06-10T10:00:00+00:00',
                'endsAt' => '2025-06-10T12:00:00+00:00',
            ])
        );

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('title', $response['errors']);
        $this->assertEquals('This value should not be blank.', $response['errors']['title']);
    }
}
