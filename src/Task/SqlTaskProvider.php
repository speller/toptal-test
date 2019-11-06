<?php
namespace App\Task;

use App\Db\MySqlProviderHelper;

/**
 * MySQL Task data provider
 */
class SqlTaskProvider extends MySqlProviderHelper implements TaskProviderInterface
{
    public function __construct(\PDO $connection)
    {
        parent::__construct($connection, 'tasks');
    }

    /**
     * Build Task from raw DB data
     * @param $data
     * @return Task
     * @throws \Exception
     */
    protected function buildTasks($data): Task
    {
        return Task::build()
            ->setId($data->id)
            ->setUserId($data->user_id)
            ->setDate($data->date)
            ->setDuration($data->duration)
            ->setTitle($data->title)
            ->create();
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function findTaskById(int $id): ?Task
    {
        return $this->findById($id);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function addTask(Task $task): int
    {
        return (int)$this->insert([
            'user_id' => $task->getUserId(),
            'date' => $task->getDate(),
            'duration' => $task->getDuration(),
            'title' => $task->getTitle(),
        ]);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function deleteTask(int $taskId): void
    {
        $this->deleteById($taskId);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function updateTask(Task $task): void
    {
        $this->update(
            [
                'title' => $task->getTitle(),
                'duration' => $task->getDuration(),
                'date' => $task->getDate(),
            ],
            [
                'id' => $task->getId(),
            ]
        );
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
        if ($addUserRoles) {
            return $this->fetchBySql(
                "SELECT t.* FROM tasks t 
                JOIN users u ON u.id = t.user_id
                WHERE (t.`date` >= :date_begin) AND (t.`date` <= :date_last) AND 
                (t.user_id = :user_id OR u.role IN :roles)",
                [
                    'date_begin' => $dateBegin,
                    'date_last' => $dateLast,
                    'user_id' => $userId,
                    'roles' => $addUserRoles,
                ]
            );
        } else {
            return $this->fetchBy(
                [
                    'date_begin >=' => $dateBegin,
                    'date_last <=' => $dateLast,
                    'user_id' => $userId,
                ]
            );
        }
    }
}
