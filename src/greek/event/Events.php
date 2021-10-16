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

use greek\Loader;
use greek\network\Network;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginManager;

abstract class Events
{
    public function getNetwork(): Network
    {
        return new Network();
    }

    /**
     * @return PluginManager
     */
    private function getPluginManager(): PluginManager
    {
        return $this->getNetwork()->getPluginManager();
    }

    /**
     * @return Loader
     */
    private function getPlugin(): Loader
    {
        return $this->getNetwork()->plugin();
    }

    /**
     * @param Listener $event
     */
    public function register(Listener $event): void
    {
        $this->getPluginManager()->registerEvents($event, $this->getPlugin());
    }
}