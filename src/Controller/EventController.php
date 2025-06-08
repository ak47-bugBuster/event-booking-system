<?php
/*
 * Author: Akshaya Bhandare
 * Page: All Events operations added: Add, Update, delete and list
 * Created At: 07-Jun-2025 
*/
namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Service\EventService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/events')]
class EventController extends AbstractController
{
    public function __construct(
        private readonly EventService $eventService,
        private readonly SerializerInterface $serializer
    ) {}

    // Create new event function
    #[Route('', name: 'create_event', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $event = new Event();

        // Use Symfony Form for validation and data mapping
        $form = $this->createForm(EventType::class, $event);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->eventService->saveEvent($event);

        return $this->json(
            ['id' => $event->getId(), 'message' => 'Event created successfully'],
            Response::HTTP_CREATED
        );
    }

    // List all the event function
    #[Route('', name: 'list_events', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        // Pagination parameters (default: page=1, limit=10)
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10))); // max 100 limit

        // Filters from query parameters
        $filters = [
            'location' => $request->query->get('location'),
            'title' => $request->query->get('title'),
            'startDate' => $request->query->get('startDate'),
            'endDate' => $request->query->get('endDate'),
        ];

        // Clean filters by removing empty values
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        // Get paginated & filtered events from service
        $paginatedData = $this->eventService->getFilteredEvents($filters, $page, $limit);

        // Serialize event data
        $json = $this->serializer->serialize($paginatedData['data'], 'json', ['groups' => ['event:list']]);

        // Add pagination metadata in response
        $response = [
            'page' => $page,
            'limit' => $limit,
            'total' => $paginatedData['total'],
            'data' => json_decode($json),
        ];

        return $this->json($response, Response::HTTP_OK);
    }

    // Update the event based on event id function
    #[Route('/{id}', name: 'update_event', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $event = $this->eventService->findEvent($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(EventType::class, $event);
        $form->submit(json_decode($request->getContent(), true), false); // false = partial update

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[$error->getOrigin()->getName()] = $error->getMessage();
            }
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->eventService->saveEvent($event);

        return $this->json(['message' => 'Event updated successfully'], Response::HTTP_OK);
    }

    // Delete Event based on event id function
    #[Route('/{id}', name: 'delete_event', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $event = $this->eventService->findEvent($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }

        $this->eventService->deleteEvent($event);

        return $this->json(['message' => 'Event deleted successfully'], Response::HTTP_OK);
    }
}
