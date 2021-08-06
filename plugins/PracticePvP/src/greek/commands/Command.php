<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 4/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\commands;


class Command extends CommandManager
{
    public function __construct()
    {
        $this->loadCommands();
    }
}