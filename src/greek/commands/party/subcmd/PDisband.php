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

final class PDisband implements ISubCommand
{
    /**
     * @param CommandSender $player
     * @param array         $args
     */
    public function executeSub(CommandSender $player, array $args): void
    {
        if ($player instanceof NetworkPlayer) $player->getPartyManager()->disband();
    }
}