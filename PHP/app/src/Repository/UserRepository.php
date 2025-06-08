<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database;
use App\Entity\User;
use PDO;

/**
 * Class UserRepository
 * Handles database operations for User entities.
 */
class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Finds a user by their email address.
     *
     * @param string $email The user's email.
     * @return User|null The User object if found, null otherwise.
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT id, username, email, password_hash, created_at FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            return new User(
                (int)$userData['id'],
                $userData['username'],
                $userData['email'],
                $userData['password_hash'],
                $userData['created_at']
            );
        }
        return null;
    }

    /**
     * Finds a user by their username.
     *
     * @param string $username The user's username.
     * @return User|null The User object if found, null otherwise.
     */
    public function findByUsername(string $username): ?User
    {
        $stmt = $this->db->prepare("SELECT id, username, email, password_hash, created_at FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            return new User(
                (int)$userData['id'],
                $userData['username'],
                $userData['email'],
                $userData['password_hash'],
                $userData['created_at']
            );
        }
        return null;
    }

    /**
     * Creates a new user in the database.
     *
     * @param string $username The username.
     * @param string $email The email address.
     * @param string $passwordHash The hashed password.
     * @return int|false The ID of the newly created user, or false on failure.
     */
    public function create(string $username, string $email, string $passwordHash): int|false
    {
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $passwordHash);
        if ($stmt->execute()) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }
}