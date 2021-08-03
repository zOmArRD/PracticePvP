<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 2/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\events;

use greek\events\player\HandlePlayer;

class ListenerManager extends ListenerBase
{
    public function __construct()
    {
        $this->loadEvents();
    }

    public function loadEvents(): void
    {
        foreach ([new HandlePlayer()] as $listener) {
            $this->registerEvent($listener);
        }
    }
}