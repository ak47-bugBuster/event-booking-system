<?php
/*
 * Author: Akshaya Bhandare
 * Page: All attendees operations added: Add, Update and list
 * Created At: 07-Jun-2025 
*/
namespace App\Controller;

use App\DTO\AttendeeDTO;
use App\Service\AttendeeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/attendees')]
class AttendeeController extends AbstractController
{
    public function __construct(
        private readonly AttendeeService $attendeeService,
        private readonly ValidatorInterface $validator
    ) {}

    // Adding Attendees to database
    #[Route('', name: 'register_attendee', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new AttendeeDTO($data);

        // Validate DTO
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], 400);
        }

        try {
            $attendee = $this->attendeeService->registerAttendee($dto);
            return $this->json([
                'id' => $attendee->getId(),
                'message' => 'Attendee registered successfully'
            ], 201);
        } catch (HttpException $e) {
            return new JsonResponse(json_decode($e->getMessage(), true), $e->getStatusCode());
        }
    }

    // Updating the attendees to database
    #[Route('/{id}', name: 'update_attendee', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = new AttendeeDTO($data);

        // Validate
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], 400);
        }

        try {
            $attendee = $this->attendeeService->updateAttendee($id, $dto);
            return $this->json([
                'id' => $attendee->getId(),
                'message' => 'Attendee updated successfully'
            ]);
        } catch (HttpException $e) {
            return new JsonResponse(json_decode($e->getMessage(), true), $e->getStatusCode());
        }
    }

    // List all the attendees record
    #[Route('', name: 'list_attendees', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $attendees = $this->attendeeService->getAllAttendees();
        $data = array_map(fn($a) => [
            'id' => $a->getId(),
            'name' => $a->getName(),
            'email' => $a->getEmail()
        ], $attendees);

        return $this->json($data);
    }

}

