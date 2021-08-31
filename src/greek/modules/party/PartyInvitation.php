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

namespace greek\modules\party;

use greek\event\party\PartyJoinEvent;
use greek\network\session\Session;

class PartyInvitation
{
    /** @var int  */
    public const INVITATION_LENGTH = 60;

    /** @var Session  */
    private Session $sender, $target;

    /** @var string  */
    private string $partyId;

    /**
     * @param Session $sender
     * @param Session $target
     * @param string $partyId
     */
    public function __construct(Session $sender, Session $target, string $partyId)
    {
        $this->sender = $sender;
        $this->target = $target;
        $this->partyId = $partyId;
    }

    public function getSender(): Session
    {
        return $this->sender;
    }

    public function getTarget(): Session
    {
        return $this->target;
    }

    public function getPartyId(): string
    {
        return $this->partyId;
    }

    public function attemptToAccept(): void
    {
        $this->target->removeInvitation($this);

        $target = $this->target->getPlayer();

        if ($this->target->hasParty()) {
            $target->sendMessage($target->getTranslatedMsg("message.party.exist"));
            return;
        } elseif (!$this->sender->isOnline()) {
            $target->sendMessage($target->getTranslatedMsg("message.party.cantjoin"));
            return;
        }

        $party = $this->sender->getParty();

        if ($party === null or $party->getId() !== $this->partyId) {
            $target->sendMessage($target->getTranslatedMsg("message.party.noid"));
            return;
        } elseif ($party->isFull()) {
            $target->sendMessage($target->getTranslatedMsg("message.party.full"));
            return;
        }

        $event = new PartyJoinEvent($party, $this->target);

        $event->call();
        $party->add($this->target);
        $this->target->removeInvitationsFromParty($party);
    }
}