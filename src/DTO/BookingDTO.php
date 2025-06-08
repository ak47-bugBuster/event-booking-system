<?php
/*
 * Author: Akshaya Bhandare
 * Page: Data Transfer object used for validations
 * Created At: 08-Jun-2025 
*/
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class BookingDTO
{
    #[Assert\NotNull(message: 'Event is required')]
    #[Assert\Type('integer')]
    public $event_id;

    #[Assert\NotNull(message: 'Attendee is required')]
    #[Assert\Type('integer')]
    public $attendee_id;

    public function __construct(array $data)
    {
        $this->event_id = $data['event_id'] ?? null;
        $this->attendee_id = $data['attendee_id'] ?? null;
    }
}
