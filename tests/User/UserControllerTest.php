<?php
namespace App\User;

use App\Response\JsonData;
use App\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class UserControllerTest extends UnitTestCase
{
    /**
     * @var UserController
     */
    private $controller;
    /**
     * @var MockObject
     */
    private $userProvider;

    public function setUp()
    {
        $this->controller =
            new UserController(
                $this->userProvider = $this->createMock(UserProviderInterface::class)
            );
    }

    public function testListUsersFailIfUnknownRole()
    {
        $this->controller->setCurrentUserContext(1, 500);
        $this->expectException('\RuntimeException');
        $this->controller->listUsers(new Request());
    }

    public function testListUsersWithAdminRoles()
    {
        $this->controller->setCurrentUserContext(1, UserRole::ADMIN);
        $this->userProvider
            ->expects($this->once())
            ->method('findUserById')
            ->with(1)
            ->willReturn(
                $u1 = User::build()
                    ->setId(1)
                    ->setLogin('login')
                    ->create()
            );
        $this->userProvider
            ->expects($this->once())
            ->method('getUsersByRoles')
            ->with([UserRole::USER, UserRole::MANAGER, UserRole::ADMIN])
            ->willReturn([
                $u2 = User::build()
                    ->setId(2)
                    ->setLogin('login2')
                    ->create()
                ]
            );

        // Current user selected on findUserById should be also selected from DB on getUsersByRoles,
        // so we don't check it here.
        $this->assertEquals(
            JsonData::data([$u2]),
            $this->controller->listUsers(new Request())
        );
    }

    public function testListUsersWithManagerRoles()
    {
        $this->controller->setCurrentUserContext(1, UserRole::MANAGER);
        $this->userProvider
            ->expects($this->once())
            ->method('findUserById')
            ->with(1)
            ->willReturn(
                $u1 = User::build()
                    ->setId(1)
                    ->setLogin('login')
                    ->create()
            );
        $this->userProvider
            ->expects($this->once())
            ->method('getUsersByRoles')
            ->with([UserRole::USER])
            ->willReturn([
                $u2 = User::build()
                    ->setId(2)
                    ->setLogin('login2')
                    ->create()
                ]
            );

        // For managers, we add current user to the list selected on getUsersByRoles
        $this->assertEquals(
            JsonData::data([$u2, $u1]),
            $this->controller->listUsers(new Request())
        );
    }

    public function testListUsersForUser()
    {
        $this->controller->setCurrentUserContext(1, UserRole::USER);
        $this->userProvider
            ->expects($this->once())
            ->method('findUserById')
            ->with(1)
            ->willReturn(
                $u1 = User::build()
                    ->setId(1)
                    ->setLogin('login')
                    ->create()
            );
        $this->userProvider
            ->expects($this->never())
            ->method('getUsersByRoles');

        $this->assertEquals(
            JsonData::data([$u1]),
            $this->controller->listUsers(new Request())
        );
    }

    public function testUpdateUserFailOnZeroHours()
    {
        $this->controller->setCurrentUserContext(1, UserRole::USER);
        $request = new Request([], [], [], [], [], [], json_encode([
            'workingHoursPerDay' => 0,
        ]));
        $this->userProvider
            ->expects($this->never())
            ->method('updateUser');
        $this->assertEquals(
            JsonData::error('Working hours must be a positive number'),
            $this->controller->updateUser($request)
        );
    }

    public function testUpdateUserFailOnNegativeHours()
    {
        $this->controller->setCurrentUserContext(1, UserRole::USER);
        $request = new Request([], [], [], [], [], [], json_encode([
            'workingHoursPerDay' => -1,
        ]));
        $this->userProvider
            ->expects($this->never())
            ->method('updateUser');
        $this->assertEquals(
            JsonData::error('Working hours must be a positive number'),
            $this->controller->updateUser($request)
        );
    }

    public function testUpdateUserFailIfUserNotFound()
    {
        $this->controller->setCurrentUserContext(1, UserRole::USER);
        $request = new Request([], [], [], [], [], [], json_encode([
            'workingHoursPerDay' => 1,
        ]));
        $this->userProvider
            ->expects($this->once())
            ->method('findUserById')
            ->with(1)
            ->willReturn(null);
        $this->userProvider
            ->expects($this->never())
            ->method('updateUser');
        $this->assertEquals(
            JsonData::error('User not found'),
            $this->controller->updateUser($request)
        );
    }

    public function testUpdateUserOk()
    {
        $this->controller->setCurrentUserContext(1, UserRole::USER);
        $request = new Request([], [], [], [], [], [], json_encode([
            'workingHoursPerDay' => 2,
        ]));
        $this->userProvider
            ->expects($this->once())
            ->method('findUserById')
            ->with(1)
            ->willReturn(
                User::build()
                    ->setId(1)
                    ->create()
            );
        $this->userProvider
            ->expects($this->once())
            ->method('updateUser')
            ->with(
                User::build()
                    ->setId(1)
                    ->setWorkingHoursPerDay(2)
                    ->create()
            );
        $this->assertEquals(
            JsonData::success(),
            $this->controller->updateUser($request)
        );
    }
}
