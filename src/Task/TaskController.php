<?php
namespace App\Task;

use App\Authentication\RequireAuthenticationInterface;
use App\Response\JsonData;
use App\User\UserProviderInterface;
use App\User\UserRole;
use App\Utils\Commons;
use App\Utils\InputParamUtils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Task manipulations controller
 */
class TaskController implements RequireAuthenticationInterface
{
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
     * @var TaskProviderInterface
     */
    private $taskProvider;
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * TaskController constructor.
     * @param TaskProviderInterface $taskProvider
     * @param UserProviderInterface $userProvider
     */
    public function __construct(
        TaskProviderInterface $taskProvider,
        UserProviderInterface $userProvider
    ) {
        $this->taskProvider = $taskProvider;
        $this->userProvider = $userProvider;
    }

    /**
     * Add new task action
     * @param Request $request
     * @return JsonData
     * @throws \App\Exception\RequestFailException
     * @throws \Exception
     */
    public function addTask(Request $request)
    {
        $data = InputParamUtils::parseJsonRequest($request);
        if ($errors = $this->getTaskInputErrors($data)) {
            return JsonData::error('Error in input data: ' . implode(', ', $errors));
        }

        $id = $this->taskProvider->addTask(
            Task::build()
                ->setUserId($this->currentUserId)
                ->setDate(Commons::valueO($data, 'date'))
                ->setDuration(Commons::valueO($data, 'duration'))
                ->setTitle(Commons::valueO($data, 'title'))
                ->create()
        );
        return JsonData::data($id);
    }

    /**
     * Action that returns the list of tasks according to date filter and current user role
     * @param Request $request
     * @return JsonData
     * @throws \App\Exception\RequestFailException
     */
    public function listTasks(Request $request)
    {
        $dateBegin = $request->get('dateBegin');
        $dateLast = $request->get('dateLast');
        switch ($this->currentRole) {
            case UserRole::USER: $addRoles = []; break;
            case UserRole::MANAGER: $addRoles = [UserRole::USER]; break;
            case UserRole::ADMIN: $addRoles = [UserRole::USER, UserRole::MANAGER, UserRole::ADMIN]; break;
            default:
                throw new \RuntimeException('Unknown role');
        }
        $tasks =
            $this->taskProvider->searchTasks(
                \DateTime::createFromFormat('Y-m-d', $dateBegin)->setTime(0, 0, 0),
                \DateTime::createFromFormat('Y-m-d', $dateLast)->setTime(0, 0, 0),
                $this->currentUserId,
                $addRoles
            );
        return JsonData::data($tasks);
    }

    /**
     * Updates specific task
     * @param Request $request
     * @return JsonData
     * @throws \App\Exception\RequestFailException
     * @throws \Exception
     */
    public function updateTask(Request $request)
    {
        $data = InputParamUtils::parseJsonRequest($request);
        $id = Commons::valueOInt($data, 'id');
        $task = $this->taskProvider->findTaskById($id);
        if (!$task || !$this->checkAccess($task)) {
            return JsonData::error('Task not found');
        }
        if ($errors = $this->getTaskInputErrors($data)) {
            return JsonData::error('Error in input data: ' . implode(', ', $errors));
        }
        $this->taskProvider->updateTask(
            Task::build()
                ->setId($id)
                ->setTitle(Commons::valueO($data, 'title'))
                ->setDuration(Commons::valueO($data, 'duration'))
                ->setDate(Commons::valueO($data, 'date'))
                ->create()
        );
        return JsonData::success();
    }

    /**
     * Returns array of errors in input data for new and updated tasks
     * @param $data
     * @return array
     */
    protected function getTaskInputErrors($data): array
    {
        $result = [];
        $duration = Commons::valueO($data, 'duration');
        $date = Commons::valueO($data, 'date');
        $title = Commons::valueO($data, 'title');
        if (!$title) {
            $result[] = 'Title must not be empty';
        }
        if (!$date) {
            $result[] = 'Date must not be empty';
        }
        if ($duration <= 0) {
            $result[] = 'Duration must be a positive number';
        }
        return $result;
    }

    /**
     * Checks can we handle this task
     * @param Task $task
     * @return bool
     */
    protected function checkAccess(Task $task): bool
    {
        if ($task->getUserId() == $this->currentUserId) {
            return true;
        }
        if ($this->currentRole != UserRole::USER) {
            $taskUser = $this->userProvider->findUserById($task->getUserId());
            switch ($this->currentRole) {
                case UserRole::MANAGER:
                    return in_array($taskUser->getRole(), [UserRole::USER]);
                case UserRole::ADMIN:
                    return in_array($taskUser->getRole(), [UserRole::USER, UserRole::MANAGER, UserRole::ADMIN]);
            }
        }
        return false;
    }

    /**
     * Actin to delete the specified task.
     * @param Request $request
     * @return JsonData
     * @throws \App\Exception\RequestFailException
     */
    public function deleteTask(Request $request)
    {
        $data = InputParamUtils::parseJsonRequest($request);
        $id = Commons::valueOInt($data, 'id');
        $task = $this->taskProvider->findTaskById($id);
        if (!$task || !$this->checkAccess($task)) {
            return JsonData::error('Task not found');
        }
        $this->taskProvider->deleteTask($id);
        return JsonData::success();
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
