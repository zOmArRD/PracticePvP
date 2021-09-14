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

class TaskManager extends TaskBase
{
    public function __construct()
    {
        $this->loadTask();
    }

    function loadTask(): void
    {
        $this->registerTask(new GlobalTask(), 1);
    }
}