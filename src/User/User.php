<?php
namespace App\User;

/**
 * User model
 */
class User implements \JsonSerializable
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
     * User constructor.
     * @param int $id
     * @param string $login
     * @param string $passwordHash
     * @param int $role
     * @param float $workingHoursPerDay
     */
    public function __construct(
        int $id,
        string $login,
        string $passwordHash,
        int $role,
        float $workingHoursPerDay
    ) {
        $this->id = $id;
        $this->login = $login;
        $this->role = $role;
        $this->workingHoursPerDay = $workingHoursPerDay;
        $this->passwordHash = $passwordHash;
    }

    /**
     * Create builder for the User class
     * @return UserBuilder
     */
    public static function build(): UserBuilder
    {
        return new UserBuilder();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return int
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return float
     */
    public function getWorkingHoursPerDay()
    {
        return $this->workingHoursPerDay;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $result = get_object_vars($this);
        unset($result['passwordHash']);
        return $result;
    }
}
