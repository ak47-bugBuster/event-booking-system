<?php
/*
 * Author: Akshaya Bhandare
 * Page: Data transfer object used for validations
 * Created At: 08-Jun-2025 
*/
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AttendeeDTO
{
    #[Assert\NotBlank(message: "Name is required.")]
    public ?string $name;

    #[Assert\NotBlank(message: "Email is required.")]
    #[Assert\Email(message: "Email is not valid.")]
    public ?string $email;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? null;
        $this->email = $data['email'] ?? null;
    }
}
