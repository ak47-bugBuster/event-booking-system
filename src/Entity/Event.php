<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Booking;

#[ORM\Entity]
#[ORM\Table(name: "event")]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    private string $title;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank]
    private string $description;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank]
    private string $location;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private int $capacity;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank]
    private \DateTimeInterface $startsAt;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(propertyPath: "startsAt", message: "End date must be after start date.")]
    private \DateTimeInterface $endsAt;

    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: "event", cascade: ["remove"])]
    private Collection $bookings;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;
        return $this;
    }

    public function getStartsAt(): ?\DateTimeInterface
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeInterface $startsAt): self
    {
        $this->startsAt = $startsAt;
        return $this;
    }

    public function getEndsAt(): ?\DateTimeInterface
    {
        return $this->endsAt;
    }

    public function setEndsAt(\DateTimeInterface $endsAt): self
    {
        $this->endsAt = $endsAt;
        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function getBookedCount(): int
    {
        return $this->bookings->count();
    }
}
