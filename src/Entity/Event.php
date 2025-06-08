<?php
/*
 * Author: Akshaya Bhandare
 * Page: Entity for event table
 * Created At: 07-Jun-2025 
*/
namespace App\Entity;

use App\Entity\Booking;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "event")]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    #[Groups(['event:list'])]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['event:list'])]
    private string $title;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    #[Groups(['event:list'])]
    private string $description;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 100)]
    #[Assert\Callback([self::class, 'validateLocation'])]
    #[Groups(['event:list'])]
    private string $location;

    #[ORM\Column(type: "integer")]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Groups(['event:list'])]
    private int $capacity;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotNull]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeInterface $startsAt = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotNull]
    #[Assert\Type(type: \DateTimeInterface::class)]
    #[Assert\GreaterThan(
        propertyPath: "startsAt",
        message: "End date must be after start date."
    )]
    private ?\DateTimeInterface $endsAt = null;

    #[ORM\OneToMany(mappedBy: "event", targetEntity: Booking::class, cascade: ["remove"])]
    private Collection $bookings;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    public static function validateLocation(string $location, ExecutionContextInterface $context): void
    {
        $countries = array_values(Countries::getNames());

        if (!in_array($location, $countries, true)) {
            $context->buildViolation('Please enter a valid country name.')
                ->addViolation();
        }
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

    public function setStartsAt(?\DateTimeInterface $startsAt): self
    {
        $this->startsAt = $startsAt;
        return $this;
    }

    public function getEndsAt(): ?\DateTimeInterface
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeInterface $endsAt): self
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
