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
use pocketmine\utils\TextFormat;
use const greek\PREFIX;

final class LangCmd extends Command
{

    public function __construct()
    {
        $this->setPermission("test");

        parent::__construct("lang",
            "Change your language",
            "/lang",
            ["idioma", "language"]);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if ($sender instanceof NetworkPlayer) {
            $sender->getLangSession()->showForm();
        } else $sender->sendMessage(PREFIX . TextFormat::RED . "You cannot run this command.");
    }
}