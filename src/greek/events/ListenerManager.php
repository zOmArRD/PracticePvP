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

use greek\events\interact\InteractEvents;
use greek\events\network\NetworkEvents;
use greek\events\player\PlayerEvents;

class ListenerManager extends ListenerBase
{
    public function __construct()
    {
        $this->loadEvents();
    }

    public function loadEvents(): void
    {
        foreach ([new PlayerEvents(), new InteractEvents(), new NetworkEvents()] as $listener) {
            $this->registerEvent(event: $listener);
        }
    }
}