<?php
/*
 * Author: Akshaya Bhandare
 * Page: Entity for booking table
 * Created At: 07-Jun-2025 
*/
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Event;
use App\Entity\Attendee;

#[ORM\Entity]
#[ORM\Table(name: "booking")]
#[ORM\UniqueConstraint(name: "unique_booking", columns: ["event_id", "attendee_id"])]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: "bookings")]
    #[ORM\JoinColumn(nullable: false)]
    private Event $event;

    #[ORM\ManyToOne(targetEntity: Attendee::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Attendee $attendee;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): self
    {
        $this->event = $event;
        return $this;
    }

    public function getAttendee(): ?Attendee
    {
        return $this->attendee;
    }

    public function setAttendee(Attendee $attendee): self
    {
        $this->attendee = $attendee;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
