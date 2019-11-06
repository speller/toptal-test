<?php
namespace App\Task;

/**
 * MySQL Task data provider
 */
class SqlTaskProvider implements TaskProviderInterface
{

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function findTaskById(int $id): ?Task
    {
        return Task::build()->setId(1)->setDuration(1)->setUserId(1)->setDate(new \DateTime())->create();
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
     * @throws \Exception
     */
    public function searchTasks(
        \DateTime $dateBegin,
        \DateTime $dateLast,
        int $userId,
        array $addUserRoles
    ): array
    {
        return [Task::build()->setId(1)->setDuration(1)->setUserId(1)->setDate(new \DateTime())->create()];
    }
}
