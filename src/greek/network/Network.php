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

namespace greek\network;

use greek\Loader;
use greek\network\utils\TextUtils;
use pocketmine\plugin\PluginManager;
use pocketmine\scheduler\TaskScheduler;

final class Network
{
    /**
     * @return Loader
     */
    public function plugin(): Loader
    {
        return Loader::getInstance();
    }

    /**
     * @return TextUtils
     */
    public function getTextUtils(): TextUtils
    {
        return new TextUtils();
    }

    /**
     * @return PluginManager
     */
    public function getPluginManager(): PluginManager
    {
        return $this->plugin()->getServer()->getPluginManager();
    }

    /**
     * @return TaskScheduler
     */
    public function getTaskManager(): TaskScheduler
    {
        return $this->plugin()->getScheduler();
    }
}