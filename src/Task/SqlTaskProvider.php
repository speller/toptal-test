<?php
namespace Task;

use App\Task\Task;
use App\Task\TaskProviderInterface;

/**
 * MySQL Task data provider
 */
class SqlTaskProvider implements TaskProviderInterface
{

    /**
     * @inheritDoc
     */
    public function findTaskById(int $id): ?Task
    {
        return Task::build()->setId(1)->setDuration(1)->setDate(new \DateTime())->create();
    }

    /**
     * @inheritDoc
     */
    public function addTask(Task $task): int
    {
        return 1;
    }

    /**
     * @inheritDoc
     */
    public function deleteTask(int $taskId): void
    {
        // TODO: Implement deleteTask() method.
    }

    /**
     * @inheritDoc
     */
    public function updateTask(Task $task): void
    {
        // TODO: Implement updateTask() method.
    }

    /**
     * @inheritDoc
     */
    public function searchTasks(\DateTime $dateBegin, \DateTime $dateLast, array $userRoles): array
    {
        return [Task::build()->setId(1)->setDuration(1)->setDate(new \DateTime())->create()];
    }
}
