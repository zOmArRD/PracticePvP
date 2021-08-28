<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 20/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\commands\party;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class PartyCmd extends Command
{

    public function __construct()
    {
        parent::__construct("party",
            "Party Command",
            "/party help",
            ["p", "fiesta"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        // TODO: Implement execute() method.
    }
}