<?php

class User {
    private $username;
    private $email;
    private $firstName;
    private $lastName;
    private $passwordHash;
    private $salt;
    private $dateCreated;
    private $accountType; // e.g., 'buyer', 'seller', 'admin'

    public function __construct($username, $email, $password, $firstName, $lastName, $accountType) {
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->dateCreated = new DateTime();
        $this->salt = bin2hex(random_bytes(32));
        $this->accountType = $accountType;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->passwordHash);
    }
}
