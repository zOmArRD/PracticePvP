<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 20/7/2021
 *
 * Copyright © 2021 Greek Network - All Rights Reserved.
 */

declare(strict_types=1);

namespace greek\task;

use greek\modules\cosmetics\task\TParticles;
use greek\network\scoreboard\ScoreboardRefreshTask;

class TaskManager extends TaskBase
{
    public function __construct()
    {
        $this->loadTask();
    }

    function loadTask(): void
    {
        $this->registerTask(new ScoreboardRefreshTask(), 40);
        $this->registerTask(new TParticles(), 3);
    }
}