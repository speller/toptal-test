<?php
namespace App\User;

use App\Db\MySqlProviderHelper;

/**
 * MySQL DB User Provider
 */
class SqlUserProvider extends MySqlProviderHelper implements UserProviderInterface
{
    /**
     * SqlUserProvider constructor.
     * @param \PDO $connection
     */
    public function __construct(\PDO $connection)
    {
        parent::__construct($connection, 'users');
    }

    /**
     * Build User from raw DB data
     * @param $data
     * @return User
     */
    protected function buildUsers($data): User
    {
        return User::build()
            ->setId($data->id)
            ->setLogin($data->login)
            ->setPasswordHash($data->password_hash)
            ->setRole($data->role)
            ->setWorkingHoursPerDay($data->hours_per_day)
            ->create();
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function findUserByLoginAndPasswordHash(string $login, string $passwordHash): ?User
    {
        return $this->findBy([
            'login' => $login,
            'password_hash' => $passwordHash,
        ]);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function registerNewUser(User $user): int
    {
        return (int)$this->insert([
            'login' => $user->getLogin(),
            'password_hash' => $user->getPasswordHash(),
            'role' => $user->getRole(),
            'hours_per_day' => $user->getWorkingHoursPerDay(),
        ]);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function findUserById(int $id): ?User
    {
        return $this->findById($id);
    }
}
