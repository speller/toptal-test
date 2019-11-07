<?php
namespace App\Auth;

use App\Authentication\AuthController;
use App\Authentication\AuthServiceInterface;
use App\Authentication\PasswordServiceInterface;
use App\Response\JsonData;
use App\Tests\UnitTestCase;
use App\User\User;
use App\User\UserProviderInterface;
use App\User\UserRole;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthControllerTest extends UnitTestCase
{
    /**
     * @var AuthController
     */
    private $controller;
    /**
     * @var MockObject
     */
    private $userProvider;
    /**
     * @var MockObject
     */
    private $authService;
    /**
     * @var MockObject
     */
    private $passwordService;

    public function setUp()
    {
        $this->controller = new AuthController(
            $this->userProvider = $this->createMock(UserProviderInterface::class),
            $this->authService = $this->createMock(AuthServiceInterface::class),
            $this->passwordService = $this->createMock(PasswordServiceInterface::class)
        );
    }

    /**
     * Creates Request object with specific content
     * @param $content
     * @return Request
     */
    private function getRequest($content)
    {
        return new Request([], [], [], [], [], [], json_encode($content));
    }

    public function testSignInFailIfEmptyLogin()
    {
        $request = $this->getRequest([
            'login' => '',
            'password' => 'pass',
        ]);
        $this->assertEquals(
            JsonData::error('Login must not be empty'),
            $this->controller->signIn($request)
        );
    }

    public function testSignInFailIfEmptyPassword()
    {
        $request = $this->getRequest([
            'login' => 'login',
            'password' => '',
        ]);
        $this->assertEquals(
            JsonData::error('Password must not be empty'),
            $this->controller->signIn($request)
        );
    }

    public function testSignInFailIfUserNotFound()
    {
        $request = $this->getRequest([
            'login' => 'login',
            'password' => 'pass',
        ]);
        $this->passwordService
            ->expects($this->once())
            ->method('getDbPassword')
            ->with('pass')
            ->willReturn('db-pass');
        $this->userProvider
            ->expects($this->once())
            ->method('findUserByLoginAndPasswordHash')
            ->with('login', 'db-pass')
            ->willReturn(null);
        $this->assertEquals(
            JsonData::error('User not found or password is incorrect'),
            $this->controller->signIn($request)
        );
    }

    public function testSignInSuccess()
    {
        $request = $this->getRequest([
            'login' => 'login',
            'password' => 'pass',
        ]);
        $this->passwordService
            ->expects($this->once())
            ->method('getDbPassword')
            ->with('pass')
            ->willReturn('db-pass');
        $this->userProvider
            ->expects($this->once())
            ->method('findUserByLoginAndPasswordHash')
            ->with('login', 'db-pass')
            ->willReturn(
                $u =
                    User::build()
                        ->setId(1)
                        ->create()
            );
        $this->authService
            ->expects($this->once())
            ->method('sendAccessToken')
            ->with(new JsonResponse(JsonData::data($u)), $u)
            ->willReturn($jr = new JsonResponse(JsonData::data($u), 201)); // 201 to differentiate it from the input param
        $this->assertEquals(
            $jr,
            $this->controller->signIn($request)
        );
    }

    public function testSignUpFailIfEmptyLogin()
    {
        $request = $this->getRequest([
            'login' => '',
            'password' => 'pass',
        ]);
        $this->assertEquals(
            JsonData::error('Login must not be empty'),
            $this->controller->signUp($request)
        );
    }

    public function testSignUpFailIfEmptyPassword()
    {
        $request = $this->getRequest([
            'login' => 'login',
            'password' => '',
        ]);
        $this->assertEquals(
            JsonData::error('Password must not be empty'),
            $this->controller->signUp($request)
        );
    }

    public function testSignUpSuccess()
    {
        $request = $this->getRequest([
            'login' => 'login',
            'password' => 'pass',
            'role' => UserRole::ADMIN,
        ]);
        $this->passwordService
            ->expects($this->once())
            ->method('getDbPassword')
            ->with('pass')
            ->willReturn('db-pass');
        $this->userProvider
            ->expects($this->once())
            ->method('registerNewUser')
            ->with(
                $u =
                    User::build()
                        ->setWorkingHoursPerDay(8)
                        ->setLogin('login')
                        ->setPasswordHash('db-pass')
                        ->setRole(UserRole::ADMIN)
                        ->create()
            )
            ->willReturn(22);
        $u2 =
            User::build()
                ->assignFrom($u)
                ->setId(22)
                ->create();
        $this->authService
            ->expects($this->once())
            ->method('sendAccessToken')
            ->with(new JsonResponse(JsonData::data($u2)), $u2)
            ->willReturn($jr = new JsonResponse(JsonData::data($u2), 201)); // 201 to differentiate it from the input param

        $this->assertEquals(
            $jr,
            $this->controller->signUp($request)
        );
    }
}
