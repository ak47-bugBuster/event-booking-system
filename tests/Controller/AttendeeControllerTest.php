<?php
/*
 * Author: Akshaya Bhandare
 * Page: Test Controller with all possible test cases for attendees
 * Created At: 08-Jun-2025 
*/
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AttendeeControllerTest extends WebTestCase
{
    // test for adding attendees
    public function testRegisterSuccess(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Test User',
            'email' => 'test_' . uniqid() . '@example.com',
        ]));

        $this->assertResponseStatusCodeSame(201);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);
        $this->assertSame('Attendee registered successfully', $response['message']);
    }

    // test for validation fail while adding
    public function testRegisterValidationFail(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => '',
            'email' => 'invalid-email'
        ]));

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
    }

    // test for validation for duplicate email while registering
    public function testRegisterDuplicateEmail(): void
    {
        $client = static::createClient();

        $data = [
            'name' => 'Duplicate User',
            'email' => 'test_' . uniqid() . '@example.com',
        ];

        // Register first
        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertResponseStatusCodeSame(201);

        // Try duplicate
        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $this->assertResponseStatusCodeSame(409);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Email already registered', $response['error']);
    }

    // test for updating attendees
    public function testUpdateAttendee(): void
    {
        $client = static::createClient();

        // Step 1: Register an attendee
        $email = 'test_' . uniqid() . '@example.com';
        $client->request('POST', '/api/attendees', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Original Name',
            'email' => $email,
        ]));

        $this->assertResponseStatusCodeSame(201);
        $response = json_decode($client->getResponse()->getContent(), true);
        $attendeeId = $response['id'];
        $this->assertNotNull($attendeeId);

        // Step 2: Update the attendee
        $client->request('PUT', "/api/attendees/{$attendeeId}", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Updated Name',
            'email' => $email, 
        ]));

        $this->assertResponseStatusCodeSame(200);
        $updateResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Attendee updated successfully', $updateResponse['message']);
        $this->assertSame($attendeeId, $updateResponse['id']);
    }
}