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
use pocketmine\utils\TextFormat;
use const greek\PREFIX;

class ConfigCmd extends Command
{
    public function __construct()
    {
        parent::__construct(name: "config",
            description: "Server configuration.",
            usageMessage: "/config",
            aliases: ['configuration']);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof NetworkPlayer) {
            new SettingsForm(player: $sender);
        } else $sender->sendMessage(PREFIX . TextFormat::RED . "You cannot run this command.");
    }
}