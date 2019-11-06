<?php
namespace App\Task;

/**
 * Model of a task that a user worker on
 */
class Task
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
     * @var string
     */
    private $title;

    /**
     * Task constructor.
     * @param int $id
     * @param int $userId
     * @param \DateTime $date
     * @param float $duration
     * @param string $title
     */
    public function __construct(
        int $id,
        int $userId,
        \DateTime $date,
        float $duration,
        string $title
    ){
        $this->id = $id;
        $this->userId = $userId;
        $this->date = $date;
        $this->duration = $duration;
        $this->title = $title;
    }

    /**
     * Creates Task builder
     * @return TaskBuilder
     */
    public static function build(): TaskBuilder
    {
        return new TaskBuilder();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return float
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
