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

use greek\network\scoreboard\ScoreboardRefreshTask;

class TaskManager extends TaskBase
{
    public function __construct()
    {
        $this->loadTask();
    }

    function loadTask(): void
    {
        $this->registerTask(new ScoreboardRefreshTask(), 20);
    }
}