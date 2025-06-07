<?php

namespace App\Controller;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/events')]
class EventController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator
    ) {}

    #[Route('', name: 'create_event', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $event = new Event();
        $event->setTitle($data['title'] ?? null);
        $event->setDescription($data['description'] ?? null);
        $event->setLocation($data['location'] ?? null);
        $event->setCapacity($data['capacity'] ?? null);
        $event->setStartsAt(isset($data['startsAt']) ? new \DateTime($data['startsAt']) : null);
        $event->setEndsAt(isset($data['endsAt']) ? new \DateTime($data['endsAt']) : null);

        $errors = $this->validator->validate($event);
        if (count($errors) > 0) {
            $errs = [];
            foreach ($errors as $error) {
                $errs[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errs], 400);
        }

        $this->em->persist($event);
        $this->em->flush();

        return $this->json([
            'id' => $event->getId(),
            'message' => 'Event created successfully'
        ], 201);
    }

    #[Route('', name: 'list_events', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $events = $this->em->getRepository(Event::class)->findAll();
        $data = [];

        foreach ($events as $event) {
            $data[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'location' => $event->getLocation(),
                'startsAt' => $event->getStartsAt()?->format(\DateTime::ATOM),
                'endsAt' => $event->getEndsAt()?->format(\DateTime::ATOM),
                'capacity' => $event->getCapacity(),
                'booked' => $event->getBookedCount(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'update_event', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $event = $this->em->getRepository(Event::class)->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['title'])) $event->setTitle($data['title']);
        if (isset($data['description'])) $event->setDescription($data['description']);
        if (isset($data['location'])) $event->setLocation($data['location']);
        if (isset($data['capacity'])) $event->setCapacity($data['capacity']);
        if (isset($data['startsAt'])) $event->setStartsAt(new \DateTime($data['startsAt']));
        if (isset($data['endsAt'])) $event->setEndsAt(new \DateTime($data['endsAt']));

        $errors = $this->validator->validate($event);
        if (count($errors) > 0) {
            $errs = [];
            foreach ($errors as $error) {
                $errs[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errs], 400);
        }

        $this->em->flush();

        return $this->json(['message' => 'Event updated successfully']);
    }

    #[Route('/{id}', name: 'delete_event', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $event = $this->em->getRepository(Event::class)->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], 404);
        }

        $this->em->remove($event);
        $this->em->flush();

        return $this->json(['message' => 'Event deleted successfully']);
    }
}
