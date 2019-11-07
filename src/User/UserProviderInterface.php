<?php
namespace App\User;

/**
 * User data provider interface
 */
interface UserProviderInterface
{
    /**
     * Search for existing user by login and password
     * @param string $login
     * @param string $passwordHash
     * @return User|null
     */
    public function findUserByLoginAndPasswordHash(string $login, string $passwordHash): ?User;

    /**
     * Register new user
     * @param User $user
     * @return int
     */
    public function registerNewUser(User $user): int;

    /**
     * Lookup user by id.
     * @param int $id
     * @return User|null
     */
    public function findUserById(int $id): ?User;

    /**
     * Returns list of users by roles
     * @param int[] $roles
     * @return array
     */
    public function getUsersByRoles(array $roles): array;

    /**
     * Updates user data
     * @param User $user
     */
    public function updateUser(User $user): void;
}
