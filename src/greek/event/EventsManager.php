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

namespace greek\event;

use greek\listener\InteractListener;
use greek\listener\NetworkListener;
use greek\listener\PartyListener;
use greek\listener\PlayerListener;
use greek\listener\SessionListener;

class EventsManager extends Events
{
    public function __construct()
    {
        $this->load();
    }

    /**
     * It is in charge of recording the events.
     */
    public function load(): void
    {
        foreach ([new PlayerListener(),
                     new InteractListener(),
                     new NetworkListener(),
                     new PartyListener(),
                     new SessionListener()] as $listener) {
            $this->register(event: $listener);
        }
    }
}