<?php
namespace App\Controller;

use App\Entity\Attendee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/attendees')]
class AttendeeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator
    ) {}

    #[Route('', name: 'register_attendee', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $attendee = new Attendee();
        $attendee->setName($data['name'] ?? null);
        $attendee->setEmail($data['email'] ?? null);

        $errors = $this->validator->validate($attendee);
        if (count($errors) > 0) {
            $errs = [];
            foreach ($errors as $error) {
                $errs[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errs], 400);
        }

        // Check for duplicate email
        $existing = $this->em->getRepository(Attendee::class)->findOneBy(['email' => $attendee->getEmail()]);
        if ($existing) {
            return $this->json(['error' => 'Email already registered'], 409);
        }

        $this->em->persist($attendee);
        $this->em->flush();

        return $this->json([
            'id' => $attendee->getId(),
            'message' => 'Attendee registered successfully'
        ], 201);
    }
}
