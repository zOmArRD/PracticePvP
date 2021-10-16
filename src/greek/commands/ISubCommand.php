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

namespace greek\commands;

use pocketmine\command\CommandSender;

interface ISubCommand
{
    /**
     * @param CommandSender $player
     * @param array         $args
     */
    public function executeSub(CommandSender $player, array $args): void;
}