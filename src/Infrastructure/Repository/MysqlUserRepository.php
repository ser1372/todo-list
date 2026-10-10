<?php

namespace App\Infrastructure\Repository;

use App\Domain\User\Email;
use App\Domain\User\User;
use App\Domain\User\UserAlreadyExist;
use App\Domain\User\UserId;
use App\Domain\User\UserNotFound;
use App\Domain\User\UserRepositoryInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\IntegrityException;

final readonly class MysqlUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private ConnectionInterface $db,
    ) {}

    public function getUserByEmail(Email $email): User
    {
        $data = $this->db->createQuery()
            ->select(['id', 'email', 'name', 'password_hash'])
            ->from('users')
            ->where(['email' => $email->toString()])
            ->one();

        if(null === $data) {
            throw new UserNotFound();
        }

        return User::fromArray($data);
    }

    public function updateUser(User $user): void
    {
        $this->db->createCommand()
            ->update('users', $user->toArray(), ['id' => $user->id->toString()])
            ->execute();
    }

    public function createUser(User $user): void
    {
        try {
            $this->db->createCommand()
                ->insert('users', $user->toArray())
                ->execute();
        } catch (IntegrityException) {
            throw new UserAlreadyExist();
        }
    }

    public function getUserById(UserId $id): User
    {
        $data = $this->db->createQuery()
            ->select(['id', 'email', 'name', 'password_hash'])
            ->from('users')
            ->where(['id' => $id->toString()])
            ->one();

        if(null === $data) {
            throw new UserNotFound();
        }

        return User::fromArray($data);
    }
}
