<?php

declare(strict_types=1);

namespace App\Domain\User;

final class User
{
    public readonly UserId $id;
    private(set) Email $email;
    private(set) UserName $name;
    private string $passwordHash;
    private(set) string $authKey;

    public function __construct(
        UserId $id,
        Email $email,
        UserName $name,
        string $passwordHash,
        string $authKey,
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->passwordHash = $passwordHash;
        $this->authKey = $authKey;
    }

    public function isValidPassword(Password $password, PasswordHasherInterface $passwordHasher): bool
    {
        return $passwordHasher->validate($password, $this->passwordHash);
    }

    public function changePassword(Password $password, PasswordHasherInterface $passwordHasher): void
    {
        $this->setPassword($password, $passwordHasher);
    }

    public function generateAuthKey(AuthKeyGeneratorInterface $authKeyGenerator): void
    {
        $this->authKey = $authKeyGenerator->generate();
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
            'auth_key' => $this->authKey,
        ];
    }

    public static function fromArray(array $data): User
    {
        return new self(
            id: UserId::fromString($data['id']),
            email: new Email($data['email']),
            name: new UserName($data['name']),
            passwordHash: $data['password_hash'],
            authKey: $data['auth_key'],
        );
    }
}
