<?php
namespace App\Task;

/**
 * Task data provider interface
 */
interface TaskProviderInterface
{
    /**
     * Search task by id.
     * @param int $id
     * @return Task|null
     */
    public function findTaskById(int $id): ?Task;

    /**
     * Add new task. Returns new task id.
     * @param Task $task
     * @return int
     */
    public function addTask(Task $task): int;

    /**
     * Delete task by id.
     * @param int $taskId
     */
    public function deleteTask(int $taskId): void;

    /**
     * Update task. The id property must be set.
     * @param Task $task
     */
    public function updateTask(Task $task): void;

    /**
     * Returns list of Tasks filtered by the date range and the list of user roles
     * @param \DateTime $dateBegin
     * @param \DateTime $dateLast
     * @param array $userRoles
     * @return Task[]
     */
    public function searchTasks(
        \DateTime $dateBegin,
        \DateTime $dateLast,
        array $userRoles
    ): array;
}
