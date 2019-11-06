<?php
namespace App\Authentication;

use App\User\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Simplest authentication service with plain open access tokens (for demo purposes)
 */
class SimpleAuthService implements AuthServiceInterface
{

    /**
     * @inheritDoc
     * @throws \App\Exception\RequestFailException
     */
    public function authenticateRequest(Request $request): array
    {
        if (!$request->headers->has('authentication')) {
            throw new \RuntimeException('Authentication required');
        }
        $token = $request->headers->get('authentication');
        [$type, $token] = explode(' ', $token);
        if (strtolower($type) !== 'bearer' || !$token) {
            throw new \RuntimeException('Invalid authentication data');
        }
        [$userId, $role] = explode(':', $token);
        if ($userId === null || $role === null) {
            throw new \RuntimeException('Invalid authentication data');
        }
        return [(int)$userId, (int)$role];
    }

    /**
     * @inheritDoc
     */
    public function sendAccessToken(JsonResponse $response, User $user): JsonResponse
    {
        $result = json_decode($response->getContent());
        $result->accessToken = "{$user->getId()}:{$user->getRole()}";
        return new JsonResponse($result, $response->getStatusCode(), $response->headers->all());
    }
}
