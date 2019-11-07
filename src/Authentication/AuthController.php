<?php
namespace App\Authentication;

use App\Response\JsonData;
use App\User\User;
use App\User\UserProviderInterface;
use App\Utils\Commons;
use App\Utils\InputParamUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * SignIn, Register and SignOut controller
 */
class AuthController
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;
    /**
     * @var AuthServiceInterface
     */
    private $authService;
    /**
     * @var PasswordServiceInterface
     */
    private $passwordService;

    /**
     * AuthController constructor.
     * @param UserProviderInterface $userProvider
     * @param AuthServiceInterface $authService
     * @param PasswordServiceInterface $passwordService
     */
    public function __construct(
        UserProviderInterface $userProvider,
        AuthServiceInterface $authService,
        PasswordServiceInterface $passwordService
    ) {
        $this->userProvider = $userProvider;
        $this->authService = $authService;
        $this->passwordService = $passwordService;
    }

    /**
     * SignIn action
     * @param Request $request
     * @return JsonData|JsonResponse
     * @throws \App\Exception\RequestFailException
     */
    public function signIn(Request $request)
    {
        $data = InputParamUtils::parseJsonRequest($request);
        $login = Commons::valueO($data, 'login');
        $password = Commons::valueO($data, 'password');
        if (!$login) {
            return JsonData::error("Login must not be empty");
        }
        if (!$password) {
            return JsonData::error("Password must not be empty");
        }
        $user =
            $this->userProvider->findUserByLoginAndPasswordHash(
                $login,
                $this->passwordService->getDbPassword($password)
            );
        if ($user) {
            return
                $this->authService->sendAccessToken(
                    new JsonResponse(JsonData::data($user)),
                    $user,
                );
        } else {
            return JsonData::error('User not found or password is incorrect');
        }
    }

    /**
     * Register new user action. Authenticates at the same time.
     * @param Request $request
     * @return JsonData|JsonResponse
     * @throws \App\Exception\RequestFailException
     */
    public function signUp(Request $request)
    {
        $data = InputParamUtils::parseJsonRequest($request);
        $login = Commons::valueO($data, 'login');
        $password = Commons::valueO($data, 'password');
        if (!$login) {
            return JsonData::error("Login must not be empty");
        }
        if (!$password) {
            return JsonData::error("Password must not be empty");
        }
        $role = Commons::valueOInt($data, 'role');
        $user = User::build()
            ->setLogin($login)
            ->setRole($role)
            ->setWorkingHoursPerDay(8)
            ->setPasswordHash($this->passwordService->getDbPassword($password))
            ->create();
        $userId = $this->userProvider->registerNewUser($user);
        $user =
            User::build()
                ->assignFrom($user)
                ->setId($userId)
                ->create();
        return
            $this->authService->sendAccessToken(
                new JsonResponse(JsonData::data($user)),
                $user,
            );
    }
}
