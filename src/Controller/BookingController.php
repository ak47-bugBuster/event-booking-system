<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Event;
use App\Entity\Attendee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/bookings')]
class BookingController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {}

    #[Route('', name: 'book_event', methods: ['POST'])]
    public function book(Request $request): JsonResponse
    {
        // Decode JSON request content safely
        try {
            $data = $request->toArray();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $eventId = $data['event_id'] ?? null;
        $attendeeId = $data['attendee_id'] ?? null;

        if (!$eventId || !$attendeeId) {
            return $this->json(['error' => 'event_id and attendee_id are required'], 400);
        }

        $event = $this->em->getRepository(Event::class)->find($eventId);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        $attendee = $this->em->getRepository(Attendee::class)->find($attendeeId);
        if (!$attendee) {
            return $this->json(['error' => 'Attendee not found'], 404);
        }

        // Check for duplicate booking
        $existingBooking = $this->em->getRepository(Booking::class)
            ->findOneBy(['event' => $event, 'attendee' => $attendee]);
        if ($existingBooking) {
            return $this->json(['error' => 'Duplicate booking not allowed'], 409);
        }

        // Check event capacity to prevent overbooking
        $bookedCount = count($event->getBookings());
        if ($bookedCount >= $event->getCapacity()) {
            return $this->json(['error' => 'Event is fully booked'], 409);
        }

        // Create and persist new booking
        $booking = new Booking();
        $booking->setEvent($event);
        $booking->setAttendee($attendee);

        $this->em->persist($booking);
        $this->em->flush();

        return $this->json(
            ['message' => 'Booking successful', 'booking_id' => $booking->getId()],
            201
        );
    }
}
