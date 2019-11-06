<?php
namespace App\Authentication;

use App\User\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface of a service that manage authentication of API calls
 */
interface AuthServiceInterface
{
    /**
     * Authenticates request. Throws exception on failure
     * @param Request $request
     * @return array Returns array [userId, role]
     */
    public function authenticateRequest(Request $request): array;

    /**
     * Sends authentication data with a response
     * @param JsonResponse $response
     * @param User $user
     * @return JsonResponse
     */
    public function sendAccessToken(JsonResponse $response, User $user): JsonResponse;
}
