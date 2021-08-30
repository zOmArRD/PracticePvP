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

use greek\event\party\PartyDisbandEvent;
use greek\modules\party\Party;
use greek\modules\party\PartyFactory;
use greek\network\player\NetworkPlayer;
use greek\network\session\SessionFactory;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

class SessionListener implements Listener
{
    /**
     * @param PlayerLoginEvent $event
     * @priority HIGHEST
     * @ignoreCancelled
     */
    public function onLogin(PlayerLoginEvent $event): void
    {
        $player = $event->getPlayer();
        if ($player instanceof NetworkPlayer) SessionFactory::createSession($player);
    }

    /**
     * @param PlayerQuitEvent $event
     * @priority LOWEST
     */
    public function onQuit(PlayerQuitEvent $event): void
    {
        $event->setQuitMessage(null);
        $player = $event->getPlayer();
        if (!$player instanceof NetworkPlayer) return;

        if (SessionFactory::hasSession($player)) {
            $session = SessionFactory::getSession($player);
            if ($session->isPartyLeader()) {
                /* TODO: Create a method to keep the party when exiting the server if it is for party duels. */
                $this->disbandParty($session->getParty());
            }
        }
    }

    private function disbandParty(Party $party): void
    {
        $event = new PartyDisbandEvent($party, $party->getLeader());
        $event->call();

        foreach ($party->getMembers() as $member) {
            $party->remove($member);
        }
        PartyFactory::removeParty($party);
    }
}