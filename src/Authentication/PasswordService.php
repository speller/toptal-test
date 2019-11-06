<?php
namespace App\Authentication;

/**
 * Simple password encoding based on sha hash and salt
 */
class PasswordService implements PasswordServiceInterface
{
    /**
     * @var string
     */
    private $salt;

    /**
     * PasswordService constructor.
     * @param string $salt
     */
    public function __construct(
        string $salt
    ) {
        $this->salt = $salt;
    }

    /**
     * Performs transformation password to hash
     * @param string $password
     * @return string
     */
    protected function encode(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['salt' => $this->salt]);
    }

    /**
     * @inheritDoc
     */
    public function getDbPassword(string $password): string
    {
        return $this->encode($password);
    }

    /**
     * @inheritDoc
     */
    public function isPasswordValid(string $password, string $dbValue): bool
    {
        return $this->encode($password) === $dbValue;
    }
}
