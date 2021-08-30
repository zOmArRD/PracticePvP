<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 28/8/2021
 *
 *  Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\commands\party\subcmd;

use greek\commands\ISubCommand;
use greek\commands\party\PartyCmd;
use pocketmine\command\CommandSender;
use const greek\PREFIX;

class PHelp implements ISubCommand
{

    public function executeSub(CommandSender $player, array $args): void
    {
        $player->sendMessage(PREFIX . "§bList of subcommands for party!");
        foreach (array_keys(PartyCmd::$subCmd) as $subCmd) {
            $player->sendMessage("§7- §a/party {$subCmd}");
        }
    }
}