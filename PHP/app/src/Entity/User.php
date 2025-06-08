<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * Class User
 * Represents a user entity.
 */
class User
{
    private ?int $id;
    private string $username;
    private string $email;
    private string $passwordHash;
    private ?string $createdAt;

    public function __construct(
        ?int $id,
        string $username,
        string $email,
        string $passwordHash,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }
}