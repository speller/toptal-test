<?php
/**
 * Created by PhpStorm.
 * User: pravdin
 * Date: 08.11.2019
 * Time: 1:34
 */

namespace App\Task;

use App\Response\JsonData;
use App\Tests\UnitTestCase;
use App\User\User;
use App\User\UserProviderInterface;
use App\User\UserRole;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class TaskControllerTest extends UnitTestCase
{
    /**
     * @var TaskController|MockObject
     */
    private $controller;
    /**
     * @var MockObject
     */
    private $taskProvider;
    /**
     * @var MockObject
     */
    private $userProvider;

    public function setUp()
    {
        $this->controller =
            $this
                ->getMockBuilder(TaskController::class)
                ->setMethods($this->getProtectedMethods(TaskController::class))
                ->setConstructorArgs([
                    $this->taskProvider = $this->createMock(TaskProviderInterface::class),
                    $this->userProvider = $this->createMock(UserProviderInterface::class)
                ])
                ->getMock();
//        $this->controller = new TaskController(
//            $this->taskProvider = $this->createMock(TaskProviderInterface::class),
//            $this->userProvider = $this->createMock(UserProviderInterface::class)
//        );
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

    public function testGetTaskInputErrorsForEmptyFields()
    {
        $this->assertEquals(
            [
                'Title must not be empty',
                'Date must not be empty',
                'Duration must be a positive number',
            ],
            $this->callOriginalMethod(
                $this->controller,
                'getTaskInputErrors',
                [(object)[
                ]]
            )
        );
    }

    public function testGetTaskInputErrorsForNegativeDuration()
    {
        $this->assertEquals(
            [
                'Duration must be a positive number',
            ],
            $this->callOriginalMethod(
                $this->controller,
                'getTaskInputErrors',
                [(object)[
                    'title' => 'title',
                    'date' => 'date',
                    'duration' => -1,
                ]]
            )
        );
    }

    public function testGetTaskInputErrorsNoErrors()
    {
        $this->assertEquals(
            [],
            $this->callOriginalMethod(
                $this->controller,
                'getTaskInputErrors',
                [(object)[
                    'title' => 'title',
                    'date' => 'date',
                    'duration' => 1,
                ]]
            )
        );
    }

    public function testAddTaskFailIfErrorsInData()
    {
        $request = $this->getRequest($d = [
            'title' => '',
            'date' => 'date',
            'duration' => 0,
        ]);

        $this->controller
            ->expects($this->once())
            ->method('getTaskInputErrors')
            ->with((object)$d)
            ->willReturn(['error1', 'error2']);

        $this->assertEquals(
            JsonData::error('Error in input data: error1, error2'),
            $this->controller->addTask($request)
        );
    }

    public function testAddTaskSuccess()
    {
        $request = $this->getRequest($d = [
            'title' => 'title',
            'date' => '2019-11-04',
            'duration' => 1.5,
        ]);

        $this->controller->setCurrentUserContext(1, UserRole::ADMIN);

        $this->controller
            ->expects($this->once())
            ->method('getTaskInputErrors')
            ->with((object)$d)
            ->willReturn([]);

        $this->taskProvider
            ->expects($this->once())
            ->method('addTask')
            ->with(
                Task::build()
                    ->setTitle('title')
                    ->setDuration(1.5)
                    ->setDate(new \DateTime('2019-11-04'))
                    ->setUserId(1)
                    ->create()
            )
            ->willReturn(21);

        $this->assertEquals(
            JsonData::data(21),
            $this->controller->addTask($request)
        );
    }

    public function testListTasksForUser()
    {
        $request = new Request([
            'dateBegin' => '2019-11-03',
            'dateLast' => '2019-11-04',
        ]);
        $this->controller->setCurrentUserContext(1, UserRole::USER);
        $this->taskProvider
            ->expects($this->once())
            ->method('searchTasks')
            ->with(
                \DateTime::createFromFormat('Y-m-d', '2019-11-03')->setTime(0, 0, 0),
                \DateTime::createFromFormat('Y-m-d', '2019-11-04')->setTime(0, 0, 0),
                1,
                []
            )
            ->willReturn([1]);
        $this->assertEquals(
            JsonData::data([1]),
            $this->controller->listTasks($request)
        );
    }

    public function testListTasksForManager()
    {
        $request = new Request([
            'dateBegin' => '2019-11-03',
            'dateLast' => '2019-11-04',
        ]);
        $this->controller->setCurrentUserContext(1, UserRole::MANAGER);
        $this->taskProvider
            ->expects($this->once())
            ->method('searchTasks')
            ->with(
                \DateTime::createFromFormat('Y-m-d', '2019-11-03')->setTime(0, 0, 0),
                \DateTime::createFromFormat('Y-m-d', '2019-11-04')->setTime(0, 0, 0),
                1,
                [UserRole::USER]
            )
            ->willReturn([2]);
        $this->assertEquals(
            JsonData::data([2]),
            $this->controller->listTasks($request)
        );
    }

    public function testListTasksForAdmin()
    {
        $request = new Request([
            'dateBegin' => '2019-11-03',
            'dateLast' => '2019-11-04',
        ]);
        $this->controller->setCurrentUserContext(1, UserRole::ADMIN);
        $this->taskProvider
            ->expects($this->once())
            ->method('searchTasks')
            ->with(
                \DateTime::createFromFormat('Y-m-d', '2019-11-03')->setTime(0, 0, 0),
                \DateTime::createFromFormat('Y-m-d', '2019-11-04')->setTime(0, 0, 0),
                1,
                [UserRole::USER, UserRole::MANAGER, UserRole::ADMIN]
            )
            ->willReturn([2]);
        $this->assertEquals(
            JsonData::data([2]),
            $this->controller->listTasks($request)
        );
    }

    public function testUpdateTaskFailIfNotFound()
    {
        $request = $this->getRequest([
            'id' => 10,
            'title' => '',
            'date' => 'date',
            'duration' => 0,
        ]);

        $this->taskProvider
            ->expects($this->once())
            ->method('findTaskById')
            ->with(10)
            ->willReturn(null);

        $this->assertEquals(
            JsonData::error('Task not found'),
            $this->controller->updateTask($request)
        );
    }

    public function testUpdateTaskFailIfIncorrectOwnership()
    {
        $request = $this->getRequest([
            'id' => 10,
            'title' => '',
            'date' => 'date',
            'duration' => 0,
        ]);

        $this->taskProvider
            ->expects($this->once())
            ->method('findTaskById')
            ->with(10)
            ->willReturn(
                $t = Task::build()
                    ->setId(10)
                    ->setDate('2019-01-01')
                    ->create()
            );

        $this->controller
            ->expects($this->once())
            ->method('checkAccess')
            ->with($t)
            ->willReturn(false);

        $this->assertEquals(
            JsonData::error('Task not found'),
            $this->controller->updateTask($request)
        );
    }

    public function testUpdateTaskFailIfErrorsInData()
    {
        $request = $this->getRequest($d = [
            'id' => 10,
            'title' => '',
            'date' => 'date',
            'duration' => 0,
        ]);

        $this->taskProvider
            ->expects($this->once())
            ->method('findTaskById')
            ->with(10)
            ->willReturn(
                $t = Task::build()
                    ->setId(10)
                    ->setDate('2019-01-01')
                    ->create()
            );

        $this->controller
            ->expects($this->once())
            ->method('checkAccess')
            ->with($t)
            ->willReturn(true);

        $this->controller
            ->expects($this->once())
            ->method('getTaskInputErrors')
            ->with((object)$d)
            ->willReturn(['error1', 'error2']);

        $this->assertEquals(
            JsonData::error('Error in input data: error1, error2'),
            $this->controller->updateTask($request)
        );
    }

    public function testUpdateTaskSuccess()
    {
        $request = $this->getRequest($d = [
            'id' => 10,
            'title' => 'title',
            'date' => '2019-01-01',
            'duration' => 2,
        ]);

        $this->controller->setCurrentUserContext(1, UserRole::ADMIN);

        $this->taskProvider
            ->expects($this->once())
            ->method('findTaskById')
            ->with(10)
            ->willReturn(
                $t = Task::build()
                    ->setId(10)
                    ->setDate('2019-01-01')
                    ->setUserId(1)
                    ->setDuration(2)
                    ->create()
            );

        $this->controller
            ->expects($this->once())
            ->method('checkAccess')
            ->with($t)
            ->willReturn(true);

        $this->controller
            ->expects($this->once())
            ->method('getTaskInputErrors')
            ->with((object)$d)
            ->willReturn([]);

        $this->assertEquals(
            JsonData::success(),
            $this->controller->updateTask($request)
        );
    }

    public function testCheckAccessAlwaysTrueForSameUser()
    {
        $this->userProvider
            ->expects($this->never())
            ->method('findUserById');

        $this->controller->setCurrentUserContext(1, UserRole::ADMIN);

        $this->assertTrue(
            $this->callOriginalMethod(
                $this->controller,
                'checkAccess',
                [
                    Task::build()
                        ->setUserId(1)
                        ->setDate(new \DateTime())
                        ->create()
                ]
            )
        );
    }

    public function testCheckAccessAlwaysFalseForWrongUserWithUserRole()
    {
        $this->userProvider
            ->expects($this->never())
            ->method('findUserById');

        $this->controller->setCurrentUserContext(1, UserRole::USER);

        $this->assertFalse(
            $this->callOriginalMethod(
                $this->controller,
                'checkAccess',
                [
                    Task::build()
                        ->setUserId(2)
                        ->setDate(new \DateTime())
                        ->create()
                ]
            )
        );
    }

    public function testCheckAccessAllowUserTaskForManager()
    {
        $this->userProvider
            ->expects($this->once())
            ->method('findUserById')
            ->with(2)
            ->willReturn(
                User::build()
                    ->setRole(UserRole::USER)
                    ->create()
            );

        $this->controller->setCurrentUserContext(1, UserRole::MANAGER);

        $this->assertTrue(
            $this->callOriginalMethod(
                $this->controller,
                'checkAccess',
                [
                    Task::build()
                        ->setUserId(2)
                        ->setDate(new \DateTime())
                        ->create()
                ]
            )
        );
    }

    public function testCheckAccessDisallowOtherManagerTaskForManager()
    {
        $this->userProvider
            ->expects($this->once())
            ->method('findUserById')
            ->with(2)
            ->willReturn(
                User::build()
                    ->setRole(UserRole::MANAGER)
                    ->create()
            );

        $this->controller->setCurrentUserContext(1, UserRole::MANAGER);

        $this->assertFalse(
            $this->callOriginalMethod(
                $this->controller,
                'checkAccess',
                [
                    Task::build()
                        ->setUserId(2)
                        ->setDate(new \DateTime())
                        ->create()
                ]
            )
        );
    }

    public function testCheckAccessAllowUserTaskForAdmin()
    {
        $this->userProvider
            ->expects($this->once())
            ->method('findUserById')
            ->with(2)
            ->willReturn(
                User::build()
                    ->setRole(UserRole::USER)
                    ->create()
            );

        $this->controller->setCurrentUserContext(1, UserRole::ADMIN);

        $this->assertTrue(
            $this->callOriginalMethod(
                $this->controller,
                'checkAccess',
                [
                    Task::build()
                        ->setUserId(2)
                        ->setDate(new \DateTime())
                        ->create()
                ]
            )
        );
    }

    public function testCheckAccessAllowManagerTaskForAdmin()
    {
        $this->userProvider
            ->expects($this->once())
            ->method('findUserById')
            ->with(2)
            ->willReturn(
                User::build()
                    ->setRole(UserRole::MANAGER)
                    ->create()
            );

        $this->controller->setCurrentUserContext(1, UserRole::ADMIN);

        $this->assertTrue(
            $this->callOriginalMethod(
                $this->controller,
                'checkAccess',
                [
                    Task::build()
                        ->setUserId(2)
                        ->setDate(new \DateTime())
                        ->create()
                ]
            )
        );
    }

    public function testCheckAccessAllowOtherAdminTaskForAdmin()
    {
        $this->userProvider
            ->expects($this->once())
            ->method('findUserById')
            ->with(2)
            ->willReturn(
                User::build()
                    ->setRole(UserRole::ADMIN)
                    ->create()
            );

        $this->controller->setCurrentUserContext(1, UserRole::ADMIN);

        $this->assertTrue(
            $this->callOriginalMethod(
                $this->controller,
                'checkAccess',
                [
                    Task::build()
                        ->setUserId(2)
                        ->setDate(new \DateTime())
                        ->create()
                ]
            )
        );
    }

    public function testDeleteTaskFailIfTaskNotFound()
    {
        $request = $this->getRequest([
            'id' => 10,
        ]);
        $this->taskProvider
            ->expects($this->once())
            ->method('findTaskById')
            ->with(10)
            ->willReturn(null);
        $this->controller
            ->expects($this->never())
            ->method('checkAccess');
        $this->assertEquals(
            JsonData::error('Task not found'),
            $this->controller->deleteTask($request)
        );
    }

    public function testDeleteTaskFailIfNotAccessible()
    {
        $request = $this->getRequest([
            'id' => 10,
        ]);
        $this->taskProvider
            ->expects($this->once())
            ->method('findTaskById')
            ->with(10)
            ->willReturn(
                $t = Task::build()
                    ->setDate('2019-01-02')
                    ->create()
            );
        $this->controller
            ->expects($this->once())
            ->method('checkAccess')
            ->with($t)
            ->willReturn(false);

        $this->taskProvider
            ->expects($this->never())
            ->method('deleteTask');

        $this->assertEquals(
            JsonData::error('Task not found'),
            $this->controller->deleteTask($request)
        );
    }

    public function testDeleteTaskSuccess()
    {
        $request = $this->getRequest([
            'id' => 10,
        ]);
        $this->taskProvider
            ->expects($this->once())
            ->method('findTaskById')
            ->with(10)
            ->willReturn(
                $t = Task::build()
                    ->setDate('2019-01-02')
                    ->create()
            );
        $this->controller
            ->expects($this->once())
            ->method('checkAccess')
            ->with($t)
            ->willReturn(true);

        $this->taskProvider
            ->expects($this->once())
            ->method('deleteTask')
            ->with(10);

        $this->assertEquals(
            JsonData::success(),
            $this->controller->deleteTask($request)
        );
    }


}
