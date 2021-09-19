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

use greek\commands\ISubCommand;
use greek\commands\party\subcmd\PAccept;
use greek\commands\party\subcmd\PCreate;
use greek\commands\party\subcmd\PDisband;
use greek\commands\party\subcmd\PHelp;
use greek\commands\party\subcmd\PInvite;
use greek\commands\party\subcmd\PKick;
use greek\commands\party\subcmd\PLeave;
use greek\commands\party\subcmd\PMembers;
use greek\network\player\NetworkPlayer;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

final class PartyCmd extends Command
{
    /** @var ISubCommand[] */
    public static array $subCmd = [];

    public function __construct()
    {
        parent::__construct("party",
            "Party Command",
            "/party help",
            ["p", "fiesta"]);
        $this->registerSubCmd();
    }

    public function registerSubCmd(): void
    {
        foreach (["help" => new PHelp(),
                     "create" => new PCreate(),
                     "disband" => new PDisband(),
                     "players" => new PMembers(),
                     "invite" => new PInvite(),
                     "kick" => new PKick(),
                     "leave" => new PLeave(),
                     "accept" => new PAccept()] as $prefix => $subCmd) self::$subCmd[$prefix] = $subCmd;
    }

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param array         $args
     *
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof NetworkPlayer) return;

        if (!isset($args[0])) {
            self::$subCmd[$this->getSubCmd("help")]->executeSub($sender, []);
            return;
        }
        $prefix = $args[0];

        if ($this->getSubCmd($prefix) === null) {
            self::$subCmd[$this->getSubCmd("help")]->executeSub($sender, []);
            return;
        }

        array_shift($args);
        $subCmd = self::$subCmd[$this->getSubCmd($prefix)];

        $subCmd->executeSub($sender, $args);
    }

    public function getSubCmd(string $prefix): ?string
    {
        return match ($prefix) {
            "help" => "help",
            "create" => "create",
            "disband" => "disband",
            "players" => "players",
            "invite" => "invite",
            "kick" => "kick",
            "leave" => "leave",
            "accept" => "accept",
            default => null,
        };
    }
}