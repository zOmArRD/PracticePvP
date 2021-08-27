<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 27/8/2021
 *
 *  Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\listener;

use greek\event\party\PartyCreateEvent;
use greek\event\party\PartyDisbandEvent;
use greek\event\party\PartyInviteEvent;
use greek\network\config\Settings;
use pocketmine\event\Listener;

class PartyListener implements Listener
{
    /**
     * @param PartyCreateEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onCreate(PartyCreateEvent $event): void
    {
        $player = $event->getPlayer();

        $player->sendMessage(Settings::$prefix . "You have created a party. To invite other players use '/p invite <player>'");
    }

    /**
     * @param PartyDisbandEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onDisband(PartyDisbandEvent $event): void
    {
        $party = $event->getParty();
        $player = $event->getPlayer();
        $session = $event->getSession();

        $player->sendMessage(Settings::$prefix . "§cYou have disbanded your party.");
        $party->sendMessage(Settings::$prefix . "§cThis party has been disbanded because " . $party->getLeaderName() . "§cleft the party", $session);
    }

    /**
     * @param PartyInviteEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onInvite(PartyInviteEvent $event): void
    {
        $player = $event->getPlayer();
        $target = $event->getTarget();

        $player->sendMessage(Settings::$prefix . "§aYou have invited §6" . $target->getPlayerName() . " §ato the party, he has 1 minute to accept the invitation");
    }
}