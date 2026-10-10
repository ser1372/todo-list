<?php

declare(strict_types=1);

namespace App\Domain\User;

final class User
{
    public function __construct(
        public readonly UserId         $id,
        private(set) readonly Email    $email,
        private(set) readonly UserName $name,
        private string                 $passwordHash,
    ) {}

    public function isValidPassword(Password $password, PasswordHasherInterface $passwordHasher): bool
    {
        return $passwordHasher->validate($password, $this->passwordHash);
    }

    public function changePassword(Password $password, PasswordHasherInterface $passwordHasher): void
    {
        $this->setPassword($password, $passwordHasher);
    }

    private function setPassword(Password $password, PasswordHasherInterface $passwordHasher): void
    {
        $this->passwordHash = $passwordHasher->hash($password);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'email' => $this->email->toString(),
            'name' => $this->name->toString(),
            'password_hash' => $this->passwordHash,
        ];
    }

    public static function fromArray(array $data): User
    {
        return new self(
            id: UserId::fromString($data['id']),
            email: new Email($data['email']),
            name: new UserName($data['name']),
            passwordHash: $data['password_hash'],
        );
    }
}
