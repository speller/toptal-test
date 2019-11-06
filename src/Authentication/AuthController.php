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
        $inputData = $request->getContent();
        $data = InputParamUtils::parseAsJson($inputData);
        $login = Commons::valueO($data, 'login');
        $password = Commons::valueO($data, 'password');
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
    public function register(Request $request)
    {
        $inputData = $request->getContent();
        $data = InputParamUtils::parseAsJson($inputData);
        $login = Commons::valueO($data, 'login');
        $password = Commons::valueO($data, 'password');
        $role = Commons::valueOInt($data, 'role');
        $user = User::build()
            ->setLogin($login)
            ->setRole($role)
            ->setPasswordHash($this->passwordService->getDbPassword($password))
            ->create();
        $userId = $this->userProvider->registerNewUser($user);
        $user = $this->userProvider->findUserById($userId);
        return
            $this->authService->sendAccessToken(
                new JsonResponse(JsonData::data($user)),
                $user,
            );
    }
}
