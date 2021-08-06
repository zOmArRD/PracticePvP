<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 6/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\task;

use greek\Loader;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\scheduler\TaskScheduler;

abstract class TaskBase
{
    /**
     * @return TaskScheduler
     */
    public function getTaskManager(): TaskScheduler
    {
        return Loader::getInstance()->getScheduler();
    }

    /**
     * @param Task $task
     * @param int $period
     * @return TaskHandler
     */
    public function registerTask(Task $task, int $period): TaskHandler
    {
        return $this->getTaskManager()->scheduleRepeatingTask($task, $period);
    }

    /**
     * @param int $seconds
     * @return int
     */
    public static function secondsToTicks(int $seconds): int
    {
        return $seconds * 20;
    }
}
