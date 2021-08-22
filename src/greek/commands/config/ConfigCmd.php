<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 13/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\commands\config;

use greek\network\config\SettingsForm;
use greek\network\player\NetworkPlayer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class ConfigCmd extends Command
{

    public function __construct()
    {
        parent::__construct("config", "Server configuration.", "/config", ['configuration']);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof NetworkPlayer) return;

        new SettingsForm($sender);
    }
}