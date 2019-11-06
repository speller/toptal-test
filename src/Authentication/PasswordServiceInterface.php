<?php
namespace App\Authentication;

/**
 * Password encoding routines interface
 */
interface PasswordServiceInterface
{
    /**
     * Returns encoded password value to store in DB
     * @param string $password
     * @return string
     */
    public function getDbPassword(string $password): string;

    /**
     * Checks is provided password matches stored DB value
     * @param string $password
     * @param string $dbValue
     * @return bool
     */
    public function isPasswordValid(string $password, string $dbValue): bool;
}
