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

use greek\commands\config\ConfigCmd;
use greek\commands\lang\LangCmd;
use greek\commands\party\PartyCmd;
use greek\Loader;
use pocketmine\command\Command as PMCommand;
use pocketmine\Server;
use const greek\PREFIX;

abstract class CommandManager
{
    /**
     * @param string    $prefix
     * @param PMCommand $command
     */
    private function registerCmd(string $prefix, PMCommand $command): void
    {
        $this->getServer()->getCommandMap()->register($prefix, $command);
    }

    /**
     * @return Server
     */
    private function getServer(): Server
    {
        return Loader::getInstance()->getServer();
    }

    /**
     * It is responsible for registering the plugin commands.
     */
    public function loadCommands(): void
    {
        foreach (["lang" => new LangCmd(), "config" => new ConfigCmd(), "party" => new PartyCmd()] as $prefix => $command) {
            $this->registerCmd($prefix, $command);
            Loader::$logger->info(PREFIX . "The command $prefix has been registered.");
        }
    }
}