<?php
namespace App\Authentication;

use App\User\User;
use App\Utils\Commons;
use App\Utils\InputParamUtils;
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
        $inputData = $request->getContent();
        $data = InputParamUtils::parseAsJson($inputData);
        $token = Commons::valueO($data, 'accessToken');
        if (!$token) {
            throw new \RuntimeException('Authentication required');
        }
        [$userId, $role] = explode(':', $token);
        if ($userId === null || $role === null) {
            throw new \RuntimeException('Authentication required');
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
