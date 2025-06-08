<?php
/*
 * Author: Akshaya Bhandare
 * Page: Event Booking operations
 * Created At: 07-Jun-2025 
*/
namespace App\Controller;

use App\DTO\BookingDTO;
use App\Entity\Booking;
use App\Entity\Event;
use App\Entity\Attendee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/bookings')]
class BookingController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer
    ) {}

    // Event booking function added here
    #[Route('', name: 'book_event', methods: ['POST'])]
    public function book(Request $request): JsonResponse
    {
        try {
            $data = $request->toArray();
        } catch (\Exception) {
            return $this->errorResponse('Invalid JSON', Response::HTTP_BAD_REQUEST);
        }

        $dto = new BookingDTO($data);

        // Validate DTO
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return $this->errorResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $event = $this->em->getRepository(Event::class)->find($dto->event_id);
        if (!$event) {
            return $this->errorResponse('Event not found', Response::HTTP_NOT_FOUND);
        }

        $attendee = $this->em->getRepository(Attendee::class)->find($dto->attendee_id);
        if (!$attendee) {
            return $this->errorResponse('Attendee not found', Response::HTTP_NOT_FOUND);
        }

        // Duplicate booking check
        $existingBooking = $this->em->getRepository(Booking::class)
            ->findOneBy(['event' => $event, 'attendee' => $attendee]);
        if ($existingBooking) {
            return $this->errorResponse('Duplicate booking not allowed', Response::HTTP_CONFLICT);
        }

        // Capacity check
        $bookedCount = $this->em->getRepository(Booking::class)->count(['event' => $event]);
        if ($bookedCount >= $event->getCapacity()) {
            return $this->errorResponse('Event is fully booked', Response::HTTP_CONFLICT);
        }

        // Create booking
        $booking = new Booking();
        $booking->setEvent($event);
        $booking->setAttendee($attendee);

        $this->em->persist($booking);
        $this->em->flush();

        $responseData = [
            'message' => 'Booking successful',
            'booking_id' => $booking->getId(),
        ];

        // Use serializer normalization
        $json = $this->serializer->serialize($responseData, 'json');

        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    private function errorResponse(mixed $errors, int $statusCode): JsonResponse
    {
        $data = is_array($errors) ? ['errors' => $errors] : ['error' => $errors];
        return new JsonResponse($data, $statusCode);
    }
}
