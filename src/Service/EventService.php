<?php
/*
 * Author: Akshaya Bhandare
 * Page: Service for event used for optimization
 * Created At: 08-Jun-2025 
*/
namespace App\Service;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

class EventService
{
    public function __construct(private EntityManagerInterface $em) {}

    // Service to add new event
    public function saveEvent(Event $event): void
    {
        $this->em->persist($event);
        $this->em->flush();
    }

    // Service to find event based on ID
    public function findEvent(int $id): ?Event
    {
        return $this->em->getRepository(Event::class)->find($id);
    }

    // Service to get all the events
    public function getAllEvents(): array
    {
        return $this->em->getRepository(Event::class)->findAll();
    }

    // Service to delete the event using event id
    public function deleteEvent(Event $event): void
    {
        $this->em->remove($event);
        $this->em->flush();
    }

    // this will filter the events based on options like: location, title, startdate and enddate
    public function getFilteredEvents(array $filters, int $page, int $limit): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('e')
            ->from(Event::class, 'e');

        if (isset($filters['location'])) {
            $qb->andWhere('e.location = :location')
            ->setParameter('location', $filters['location']);
        }

        if (isset($filters['title'])) {
            $qb->andWhere('LOWER(e.title) LIKE LOWER(:title)')
            ->setParameter('title', '%'.$filters['title'].'%');
        }

        if (isset($filters['startDate'])) {
            $qb->andWhere('e.startsAt >= :startDate')
            ->setParameter('startDate', new \DateTime($filters['startDate']));
        }

        if (isset($filters['endDate'])) {
            $qb->andWhere('e.endsAt <= :endDate')
            ->setParameter('endDate', new \DateTime($filters['endDate']));
        }

        $qb->orderBy('e.startsAt', 'ASC')
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit);

        $events = $qb->getQuery()->getResult();

        // Count total matching records
        $countQb = clone $qb;
        $countQb->select('COUNT(e.id)')
                ->setFirstResult(null)
                ->setMaxResults(null);

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            'data' => $events,
            'total' => $total,
        ];
    }
}
