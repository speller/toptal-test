<?php
namespace App\User;

use App\Authentication\RequireAuthenticationInterface;
use App\Response\JsonData;
use App\Utils\Commons;
use App\Utils\InputParamUtils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller with actions for users manipulations
 */
class UserController implements RequireAuthenticationInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;
    /**
     * Dirty hack when the Request object is immutable and we don't want to implement
     * a bunch of workarounds on that.
     * @var int
     */
    private $currentUserId;
    /**
     * @var int
     */
    private $currentRole;

    /**
     * UserController constructor.
     * @param UserProviderInterface $userProvider
     */
    public function __construct(
        UserProviderInterface $userProvider
    )
    {
        $this->userProvider = $userProvider;
    }

    /**
     * Returns list of users available to the current user to manage and edit
     * @param Request $request
     * @return JsonData
     */
    public function listUsers(Request $request)
    {
        switch ($this->currentRole) {
            case UserRole::USER: $addRoles = []; break;
            case UserRole::MANAGER: $addRoles = [UserRole::USER]; break;
            case UserRole::ADMIN: $addRoles = [UserRole::USER, UserRole::MANAGER, UserRole::ADMIN]; break;
            default:
                throw new \RuntimeException('Unknown role');
        }
        if ($addRoles) {
            $result = $this->userProvider->getUsersByRoles($addRoles);
        } else {
            $result = [$this->userProvider->findUserById($this->currentUserId)];
        }
        return JsonData::data($result);
    }

    /**
     * Update user's working hours value
     * @param Request $request
     * @return JsonData
     * @throws \App\Exception\RequestFailException
     */
    public function updateUser(Request $request)
    {
        $data = InputParamUtils::parseJsonRequest($request);
        $hours = Commons::valueO($data, 'workingHoursPerDay');
        if ($hours <= 0) {
            return JsonData::error("Working hours must be a positive number");
        }
        $user = $this->userProvider->findUserById($this->currentUserId);
        $user = User::build()
            ->assignFrom($user)
            ->setWorkingHoursPerDay($hours)
            ->create();
        $this->userProvider->updateUser($user);
        return JsonData::data('OK');
    }

    /**
     * @inheritDoc
     */
    public function setCurrentUserContext(int $userId, int $role)
    {
        $this->currentUserId = $userId;
        $this->currentRole = $role;
    }
}
