<?php
/*
 * Author: Akshaya Bhandare
 * Page: Test Controller with all possible test cases for bookings
 * Created At: 08-Jun-2025 
*/
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Event;
use App\Entity\Attendee;
use Doctrine\ORM\EntityManagerInterface;

class BookingControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;

    // creating new attendee and event and then booking is tested
    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        // Clear bookings, events and attendees if needed
        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement($platform->getTruncateTableSQL('booking', true));
        $connection->executeStatement($platform->getTruncateTableSQL('event', true));
        $connection->executeStatement($platform->getTruncateTableSQL('attendee', true));
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        // Create and persist a sample event and attendee
        $event = new Event();
        $event->setTitle('Sample Event');
        $event->setCapacity(2);
        $event->setDescription('Sample description');
        $event->setLocation('India');
        $event->setStartsAt(new \DateTime('2025-06-09 10:00:00'));
        $event->setEndsAt(new \DateTime('2025-06-09 10:00:00'));
        $this->em->persist($event);

        $attendee = new Attendee();
        $attendee->setName('Test User');
        $attendee->setEmail('test_' . uniqid() . '@example.com');
        $this->em->persist($attendee);

        $this->em->flush();

        $this->eventId = $event->getId();
        $this->attendeeId = $attendee->getId();
    }

    // check if booking is created successfully
    public function testBookEventSuccessfully(): void
    {
        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $this->eventId,
            'attendee_id' => $this->attendeeId,
        ]));

        $this->assertResponseStatusCodeSame(201);

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('booking_id', $response);
        $this->assertSame('Booking successful', $response['message']);
    }

    // test error condition where event and attendee both are missing
    public function testBookEventValidationError(): void
    {
        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));
        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('event_id', $response['errors']);
        $this->assertArrayHasKey('attendee_id', $response['errors']);
    }

    // test only to check if booking event is valid or not
    public function testBookEventInvalidJson(): void
    {
        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], '{invalid_json}');
        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertSame('Invalid JSON', $response['error']);
    }

    // non existing event and attendee id test case
    public function testBookEventNotFound(): void
    {
        // Non-existing event_id
        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => 999999,
            'attendee_id' => $this->attendeeId,
        ]));
        $this->assertResponseStatusCodeSame(404);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Event not found', $response['error']);

        // Non-existing attendee_id
        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $this->eventId,
            'attendee_id' => 999999,
        ]));
        $this->assertResponseStatusCodeSame(404);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Attendee not found', $response['error']);
    }

    // check for duplicate here
    public function testBookDuplicateBooking(): void
    {
        // First booking success
        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $this->eventId,
            'attendee_id' => $this->attendeeId,
        ]));
        $this->assertResponseStatusCodeSame(201);

        // Attempt duplicate booking
        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $this->eventId,
            'attendee_id' => $this->attendeeId,
        ]));
        $this->assertResponseStatusCodeSame(409);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Duplicate booking not allowed', $response['error']);
    }

    // test to check if booking an event is successfully done
    public function testBookEventFullyBooked(): void
    {
        // Add another attendee to fill event capacity
        $attendee2 = new Attendee();
        $attendee2->setName('Another User');
        $attendee2->setEmail('test_' . uniqid() . '@example.com');
        $this->em->persist($attendee2);
        $this->em->flush();

        // Book first attendee - already booked in previous test
        $booking1 = new \App\Entity\Booking();
        $booking1->setEvent($this->em->getReference(Event::class, $this->eventId));
        $booking1->setAttendee($this->em->getReference(Attendee::class, $this->attendeeId));
        $this->em->persist($booking1);

        // Book second attendee
        $booking2 = new \App\Entity\Booking();
        $booking2->setEvent($this->em->getReference(Event::class, $this->eventId));
        $booking2->setAttendee($attendee2);
        $this->em->persist($booking2);

        $this->em->flush();

        // Event capacity is 2, so next booking should fail
        $attendee3 = new Attendee();
        $attendee3->setName('Third User');
        $attendee3->setEmail('test_' . uniqid() . '@example.com');
        $this->em->persist($attendee3);
        $this->em->flush();

        $this->client->request('POST', '/api/bookings', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'event_id' => $this->eventId,
            'attendee_id' => $attendee3->getId(),
        ]));

        $this->assertResponseStatusCodeSame(409);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Event is fully booked', $response['error']);
    }
}
