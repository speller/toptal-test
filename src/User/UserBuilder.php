<?php
namespace App\User;

/**
 * User model builder
 */
class UserBuilder
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $login;
    /**
     * @var int
     */
    private $role;
    /**
     * @var float
     */
    private $workingHoursPerDay;
    /**
     * @var string
     */
    private $passwordHash;

    /**
     * Create User object
     * @return User
     */
    public function create(): User
    {
        return new User(
            (int)$this->id,
            (string)$this->login,
            (string)$this->passwordHash,
            (int)$this->role,
            (float)$this->workingHoursPerDay,
        );
    }

    /**
     * Assign values in the builder from another user object
     * @param User $src
     * @return UserBuilder
     */
    public function assignFrom(User $src): UserBuilder
    {
        $this->id = $src->getId();
        $this->role = $src->getRole();
        $this->passwordHash = $src->getPasswordHash();
        $this->role = $src->getRole();
        $this->workingHoursPerDay = $src->getWorkingHoursPerDay();
        return $this;
    }

    /**
     * @param int $id
     * @return UserBuilder
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $login
     * @return UserBuilder
     */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @param int $role
     * @return UserBuilder
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @param float $workingHoursPerDay
     * @return UserBuilder
     */
    public function setWorkingHoursPerDay($workingHoursPerDay)
    {
        $this->workingHoursPerDay = $workingHoursPerDay;
        return $this;
    }

    /**
     * @param string $passwordHash
     * @return UserBuilder
     */
    public function setPasswordHash($passwordHash)
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

}
