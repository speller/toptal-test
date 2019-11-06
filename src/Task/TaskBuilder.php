<?php
namespace App\Task;

/**
 * Task model builder
 */
class TaskBuilder
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var int
     */
    private $userId;
    /**
     * @var \DateTime
     */
    private $date;
    /**
     * @var float
     */
    private $duration;

    /**
     * Creates new Task object
     * @return Task
     */
    public function create(): Task
    {
        return new Task(
            (int)$this->id,
            (int)$this->userId,
            $this->date,
            (float)$this->duration,
        );
    }
    /**
     * @param int $id
     * @return TaskBuilder
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param int $userId
     * @return TaskBuilder
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param \DateTime $date
     * @return TaskBuilder
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param float $duration
     * @return TaskBuilder
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }
}
