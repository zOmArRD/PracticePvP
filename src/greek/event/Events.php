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
use pocketmine\event\Listener;
use pocketmine\plugin\PluginManager;

abstract class Events
{
    /**
     * @return PluginManager
     */
    public function getPluginManager(): PluginManager
    {
        return Loader::$instance->getServer()->getPluginManager();
    }

    /**
     * @return Loader
     */
    public function getPlugin(): Loader
    {
        return Loader::getInstance();
    }

    /**
     * @param Listener $event
     */
    public function register(Listener $event): void
    {
        $this->getPluginManager()->registerEvents($event, $this->getPlugin());
    }
}