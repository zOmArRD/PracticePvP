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
use greek\Loader;
use JetBrains\PhpStorm\Pure;
use pocketmine\command\Command as PMCommand;
use pocketmine\Server;

abstract class CommandManager
{
    /**
     * @param string $prefix
     * @param PMCommand $command
     */
    public function registerCmd(string $prefix, PMCommand $command): void
    {
        $this->getServer()->getCommandMap()->register(fallbackPrefix: $prefix, command: $command);
    }

    #[Pure]
    public function getServer(): Server
    {
        return Loader::getInstance()->getServer();
    }

    /**
     * It is responsible for registering the plugin commands.
     */
    public function loadCommands(): void
    {
        foreach (["lang" => new LangCmd(), "config" => new ConfigCmd()] as $prefix => $command) {
            $this->registerCmd(prefix: $prefix, command: $command);
            Loader::$logger->info(message: "The command $prefix has been registered.");
        }
    }
}