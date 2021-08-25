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

namespace greek\commands\lang;

use greek\network\player\NetworkPlayer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class LangCmd extends Command
{

    public function __construct()
    {
        $this->setPermission("test");
        parent::__construct(name: "lang", description: "Change your language", usageMessage: "/lang", aliases: ["idioma", "language"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof NetworkPlayer) $sender->getLangSession()->showForm();
    }
}