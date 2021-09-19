<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 26/8/2021
 *
 *  Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\network\session;

use greek\modules\party\Party;
use greek\modules\party\PartyInvitation;
use greek\network\player\NetworkPlayer;

class Session
{
    /** @var NetworkPlayer */
    public NetworkPlayer $player;

    /** @var Party */
    private Party $party;

    /** @var bool */
    private bool $partyChat = false;

    /** @var PartyInvitation[] */
    private array $invitations = [];

    /** @var array */
    public static array $playerData = [];

    /**
     * @param NetworkPlayer $player
     */
    public function __construct(NetworkPlayer $player)
    {
        $this->setPlayer($player);
    }

    /**
     * @param NetworkPlayer $player
     */
    public function setPlayer(NetworkPlayer $player): void
    {
        $this->player = $player;
    }

    /**
     * @return NetworkPlayer
     */
    public function getPlayer(): NetworkPlayer
    {
        return $this->player;
    }

    /**
     * @return string
     */
    public function getPlayerName(): string
    {
        return $this->getPlayer()->getName();
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return SessionFactory::hasSession($this->getPlayer());
    }

    /**
     * @return Party|null
     */
    public function getParty(): ?Party
    {
        return $this->party;
    }

    /**
     * @param Party|null $party
     */
    public function setParty(?Party $party): void
    {
        $this->party = $party;
    }

    /**
     * @return bool
     */
    public function hasParty(): bool
    {
        return $this->getParty() !== null;
    }

    /**
     * @return bool
     */
    public function isPartyLeader(): bool
    {
        return $this->hasParty() && $this->getParty()->getLeaderName() === $this->getPlayerName();
    }

    /**
     * @return bool
     */
    public function hasPartyChat(): bool
    {
        return $this->partyChat;
    }

    /**
     * @param bool $partyChat
     */
    public function setPartyChat(bool $partyChat): void
    {
        $this->partyChat = $partyChat;
    }

    /**
     * @return PartyInvitation[]
     */
    public function getInvitations(): array
    {
        foreach ($this->invitations as $time => $invitation) {
            if (microtime(true) - $time >= PartyInvitation::INVITATION_LENGTH) {
                $this->removeInvitation($invitation);
            }
        }
        return $this->invitations;
    }

    /**
     * @param PartyInvitation $invitation
     * @return bool
     */
    public function hasInvitation(PartyInvitation $invitation): bool
    {
        return in_array($invitation, $this->invitations, true);
    }

    /**
     * @param Session $session
     * @return bool
     */
    public function hasSessionInvitation(Session $session): bool
    {
        foreach ($this->getInvitations() as $invitation) {
            if ($invitation->getSender()->getPlayerName() === $session->getPlayerName()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param PartyInvitation $invitation
     */
    public function addInvitation(PartyInvitation $invitation): void
    {
        $this->invitations[microtime(true)] = $invitation;
    }

    /**
     * @param PartyInvitation $invitation
     */
    public function removeInvitation(PartyInvitation $invitation): void {
        if($this->hasInvitation($invitation)) {
            unset($this->invitations[array_search($invitation, $this->invitations, true)]);
        }
    }

    /**
     * @param Party $party
     */
    public function removeInvitationsFromParty(Party $party): void {
        foreach($this->getInvitations() as $invitation) {
            if($invitation->getPartyId() === $party->getId()) {
                $this->removeInvitation($invitation);
            }
        }
    }

    public function sendMessage(string $message): void
    {
        $this->getPlayer()->sendMessage($message);
    }
}