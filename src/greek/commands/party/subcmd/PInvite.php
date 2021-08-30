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
use greek\network\player\NetworkPlayer;
use pocketmine\command\CommandSender;
use const greek\PREFIX;

class PInvite implements ISubCommand
{

    public function executeSub(CommandSender $player, array $args): void
    {
        if (!$player instanceof NetworkPlayer) return;

        if (!isset($args[0])) {
            $player->sendMessage(PREFIX . "§cUse: §a/party invite §6<player>");
            return;
        }

        $player->getPartyManager()->invitePlayer($args[0]);
    }
}