<?php
/*
 * Author: Akshaya Bhandare
 * Page: Service for attendee used for optimization
 * Created At: 08-Jun-2025 
*/
namespace App\Service;

use App\DTO\AttendeeDTO;
use App\Entity\Attendee;
use App\Repository\AttendeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AttendeeService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
        private readonly AttendeeRepository $attendeeRepository
    ) {}

    // Add new attendee service
    public function registerAttendee(AttendeeDTO $dto): Attendee
    {
         // Check for duplicate email
        $existingAttendee = $this->attendeeRepository->findOneBy(['email' => $dto->email]);
        if ($existingAttendee) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                409,
                json_encode(['error' => 'Email already registered'])
            );
        }

        $attendee = new Attendee();
        $attendee->setName($dto->name);
        $attendee->setEmail($dto->email);

        $this->em->persist($attendee);
        $this->em->flush();

        return $attendee;
    }

    // Service to update attendees based on $id passed from controller
    public function updateAttendee(int $id, AttendeeDTO $dto): Attendee
    {
        $attendee = $this->attendeeRepository->find($id);

        if (!$attendee) {
            throw new HttpException(404, json_encode(['error' => 'Attendee not found']));
        }

        // Check for duplicate email if changed
        $existing = $this->attendeeRepository->findOneBy(['email' => $dto->email]);
        if ($existing && $existing->getId() !== $attendee->getId()) {
            throw new HttpException(409, json_encode(['error' => 'Email already registered']));
        }

        $attendee->setName($dto->name);
        $attendee->setEmail($dto->email);

        $this->em->flush();

        return $attendee;
    }

    // Service to list all the attendees
    public function getAllAttendees(): array
    {
        return $this->attendeeRepository->findAll();
    }
}