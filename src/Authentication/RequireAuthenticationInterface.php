<?php
namespace App\Authentication;

/**
 * Interface to mark a controller as requires authentication
 */
interface RequireAuthenticationInterface
{
    /**
     * Sets current request user id and role.
     * Dirty hack when the Request object is immutable and we don't want to implement
     * a bunch of workarounds on that.
     * @param int $userId
     * @param int $role
     * @return mixed
     */
    public function setCurrentUserContext(int $userId, int $role);
}
