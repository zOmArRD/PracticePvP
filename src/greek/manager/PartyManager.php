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

namespace greek\manager;

use greek\event\party\PartyCreateEvent;
use greek\event\party\PartyDisbandEvent;
use greek\modules\party\Party;
use greek\modules\party\PartyFactory;
use greek\network\session\Session;
use const greek\PREFIX;

class PartyManager
{
    /** @var Session|null  */
    protected Session|null $session = null;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function createParty(): void
    {
        if (!$this->session->hasParty()) {
            $party = new Party(uniqid(), $this->session);

            $event = new PartyCreateEvent($party, $this->session);
            $event->call();

            if (!$event->isCancelled()) {
                $party->add($this->session);
                PartyFactory::addParty($party);
                $this->session->getPlayer()->getPartyItems();
            }
        } else {
            $this->session->getPlayer()->sendTranslatedMsg("message.party.exist");
        }
    }

    public function disbandParty(): void
    {
        $session = $this->session;
        $party = $session->getParty();

        if ($session->isPartyLeader()) {
            $event = new PartyDisbandEvent($party, $session);
            $event->call();

            if ($event->isCancelled()) return;

            foreach ($party->getMembers() as $member) {
                $party->remove($member);
                $member->getPlayer()->teleportToLobby();
            }
            PartyFactory::removeParty($party);
        } else {
            $session->getPlayer()->sendTranslatedMsg("message.party.noparty");
        }
    }

    public function sendPartyMembersMsg(): void
    {
        $members = $this->session->getParty()->getMembers();
        /*unset($members[array_search($this->session, $members, true)]);
        array_unshift($members, $this->session);*/

        if ($this->session->isPartyLeader()) {
            $this->session->sendMessage(PREFIX . "§6Party Members:");
            foreach ($members as $member) {
                $this->session->sendMessage("§a - {$member->getPlayerName()}");
            }
        }
    }
}