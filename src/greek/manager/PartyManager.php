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
use greek\event\party\PartyInviteEvent;
use greek\event\party\PartyLeaveEvent;
use greek\event\party\PartyMemberKickEvent;
use greek\modules\party\Party;
use greek\modules\party\PartyFactory;
use greek\modules\party\PartyInvitation;
use greek\network\player\NetworkPlayer;
use greek\network\session\Session;
use greek\network\session\SessionFactory;
use greek\network\utils\TextUtils;
use pocketmine\Server;
use const greek\PREFIX;

class PartyManager
{
    /** @var Session|null */
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
            $this->session->sendMessage(PREFIX . $this->session->getPlayer()->getTranslatedMsg("message.party.exist"));
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
            $session->sendMessage(PREFIX . $session->getPlayer()->getTranslatedMsg("message.party.noparty"));
        }
    }

    public function sendPartyMembersMsg(): void
    {
        $members = $this->session->getParty()->getMembers();

        if ($this->session->isPartyLeader()) {
            $this->session->sendMessage(PREFIX . "§6Party Members:");
            foreach ($members as $member) {
                $this->session->sendMessage("§a - {$member->getPlayerName()}");
            }
        }
    }

    public function isCancelled(Session $target): bool
    {
        $session = $this->session;

        $event = new PartyInviteEvent($session->getParty(), $session, $target);
        $event->call();
        return $event->isCancelled();
    }

    public function invitePlayer(string $name)
    {
        $sPlayer = $this->session;
        $player = $sPlayer->getPlayer();

        $target = Server::getInstance()->getPlayer($name);

        if (!$sPlayer->hasParty()) {
            $translatedMsg = $player->getTranslatedMsg("message.party.noparty");
            $player->sendMessage(PREFIX . $translatedMsg);
            return;
        }

        if ((!$target instanceof NetworkPlayer) || !$target->isOnline()) {
            $translatedMsg = $player->getTranslatedMsg("message.player.notonline");
            $player->sendMessage(PREFIX . TextUtils::replaceVars($translatedMsg, ["{player.name}" => $name]));
            return;
        }

        if ($name === $player->getName()) {
            $player->sendMessage(PREFIX . $player->getTranslatedMsg("message.player.stupid"));
            return;
        }

        $sTarget = SessionFactory::getSession($target);

        if ($sTarget === null) {
            $translatedMsg = $player->getTranslatedMsg("message.player.notonline");
            $player->sendMessage(PREFIX . TextUtils::replaceVars($translatedMsg, ["{player.name}" => $name]));
        } elseif ($sTarget->hasParty()) {
            $translatedMsg = $player->getTranslatedMsg("message.party.invite.haveparty");
            $player->sendMessage(PREFIX . TextUtils::replaceVars($translatedMsg, ["{player.name}" => $name]));
        } elseif ($sTarget->hasSessionInvitation($sPlayer)) {
            $translatedMsg = $player->getTranslatedMsg("message.party.invite.haveinvite");
            $player->sendMessage(PREFIX . TextUtils::replaceVars($translatedMsg, ["{player.name}" => $name]));
        } elseif ($this->isCancelled($sTarget)) {
            $sTarget->addInvitation(new PartyInvitation($sPlayer, $sTarget, $sPlayer->getParty()->getId()));
        }
    }

    public function leaveParty()
    {
        $session = $this->session;
        $player = $session->getPlayer();

        if (!$session->hasParty()) {
            $player->sendMessage(PREFIX . $player->getTranslatedMsg("message.player.stupid"));
        } elseif ($session->isPartyLeader()) {
            $this->disbandParty();
        } else {
            $party = $session->getParty();

            $event = new PartyLeaveEvent($party, $session);
            $event->call();

            if (!$event->isCancelled()) {
                $party->remove($session);
            }
        }
    }

    public function kickPlayer(string $target)
    {
        $session = $this->session;
        $player = $session->getPlayer();

        if (!$session->hasParty()) {
            $translatedMsg = $player->getTranslatedMsg("message.party.noparty");
            $player->sendMessage(PREFIX . $translatedMsg);
            return;
        }

        $members = $session->getParty()->getMembers();

        if ($target === $player->getName()) {
            $this->disbandParty();
        } else {
            foreach ($members as $member) {
                if ($member->getPlayerName() === $target) {
                    if ($member->isOnline()) {
                        $party = $session->getParty();

                        $event = new PartyMemberKickEvent($party, $session, $member);
                        $event->call();

                        if (!$event->isCancelled()) {
                            $party->remove($member);
                        }
                    }
                } else {
                    $translatedMsg = $player->getTranslatedMsg("message.party.player.noexist");
                    $player->sendMessage(PREFIX . TextUtils::replaceVars($translatedMsg, ["{player.name}" => $target]));
                }
            }
        }
    }
}