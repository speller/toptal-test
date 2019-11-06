<?php
namespace App\User;


class SqlUserProvider implements UserProviderInterface
{

    /**
     * @inheritDoc
     */
    public function findUserByLoginAndPasswordHash(string $login, string $passwordHash): ?User
    {
        return User::build()->setId(1)->setLogin('user')->create();
    }

    /**
     * @inheritDoc
     */
    public function registerNewUser(User $user): int
    {
        return 1;
    }

    /**
     * @inheritDoc
     */
    public function findUserById(int $id): ?User
    {
        return User::build()->setId(1)->setLogin('user')->create();
    }
}
