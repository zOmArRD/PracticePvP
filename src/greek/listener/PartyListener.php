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
use greek\event\party\PartyJoinEvent;
use greek\event\party\PartyLeaderPromoteEvent;
use greek\event\party\PartyLeaveEvent;
use greek\event\party\PartyMemberKickEvent;
use greek\event\party\PartySetPrivateEvent;
use greek\event\party\PartySetPublicEvent;
use greek\event\party\PartyUpdateSlotsEvent;
use pocketmine\event\Listener;
use const greek\PREFIX;

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

        $player->getPartyItems();
        $player->sendMessage(PREFIX . "§aYou have created a party. To invite other players use '/p invite <player>'");

        $event->getParty()->uploadToMySQL();
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

        foreach ($party->getMembers() as $member) {
            $member->getPlayer()->teleportToLobby();
        }
        $player->sendMessage(PREFIX . "§cYou have disbanded your party.");
        $party->sendMessage(PREFIX . "§cThis party has been disbanded because §6{$party->getLeaderName()} §cleft the party", $session);

        $event->getParty()->removeFromMySQL();
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

        $player->sendMessage(PREFIX . "§aYou have invited §6{$target->getPlayerName()} §ato the party, he has 1 minute to accept the invitation");
        $event->getParty()->sendMessage(PREFIX . "§6{$target->getPlayerName()} §has been invited to the party!", $event->getSession());
        $target->sendMessage(PREFIX . "§aYou have received an invitation to join §6{$event->getSession()->getPlayerName()}§a's party!");
    }

    /**
     * @param PartyJoinEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onJoin(PartyJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $party = $event->getParty();

        $player->getPartyItems();
        $player->sendMessage(PREFIX . "§aYou have joined §6{$party->getLeaderName()}§a's party!");
        $party->sendMessage(PREFIX . "§6{$player->getName()} §ahas joined the party!");
    }

    /**
     * @param PartyLeaderPromoteEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onLeaderPromote(PartyLeaderPromoteEvent $event): void
    {
        $session = $event->getSession();
        $newLeader = $event->getNewLeader();
        $party = $event->getParty();

        $sessionName = $session->getPlayerName();
        $newLeaderName = $newLeader->getPlayerName();

        $session->sendMessage(PREFIX . "§aYou have made §6{$newLeaderName} §athe leader of the party. ");
        $newLeader->sendMessage(PREFIX . "§6{$sessionName}§a promoted you to the leader of the party.");
        $party->sendMessage(PREFIX . "§6{$sessionName} §ahas promoted §6{$newLeaderName}§a as the leader of the party", $session);
    }

    /**
     * @param PartyLeaveEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onLeave(PartyLeaveEvent $event): void
    {
        $session = $event->getSession();
        $party = $event->getParty();

        $session->getPlayer()->teleportToLobby();
        $session->sendMessage(PREFIX . "§cYou have left §6{$party->getLeaderName()}§c's party!");
        $party->sendMessage(PREFIX . "§6{$session->getPlayerName()} §chas left the party!", $session);
    }

    /**
     * @param PartyMemberKickEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onMemberKick(PartyMemberKickEvent $event): void
    {
        $member = $event->getMember();

        $member->getPlayer()->teleportToLobby();
        $member->sendMessage(PREFIX . "§cYou have been kicked from §6{$event->getSession()->getPlayerName()}§c's party!");
        $event->getParty()->sendMessage(PREFIX . "§6{$member->getPlayerName()} §chas been kicked from the party!", $member);
    }

    /**
     * @param PartySetPrivateEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onLock(PartySetPrivateEvent $event): void
    {
        $event->getParty()->sendMessage(PREFIX . "§aThe party is now private.");
    }

    /**
     * @param PartySetPublicEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onUnlock(PartySetPublicEvent $event): void
    {
        $event->getParty()->sendMessage(PREFIX . "§aThe party is now public.");
    }

    /**
     * @param PartyUpdateSlotsEvent $event
     * @ignoreCancelled
     * @priority HIGHEST
     */
    public function onUpdateSlots(PartyUpdateSlotsEvent $event): void
    {
        $event->getParty()->sendMessage(PREFIX . "§aThe party slots has been updated to {$event->getSlots()}!");
    }
}