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
use greek\modules\form\lib\ModalForm;
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

            $party->add($this->session);
            PartyFactory::addParty($party);
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

            foreach ($party->getMembers() as $member) {
                $party->remove($member);
            }
            PartyFactory::removeParty($party);
        } else {
            $session->sendMessage(PREFIX . $session->getPlayer()->getTranslatedMsg("message.party.noparty"));
        }
    }

    public function sendPartyMembersMsg(): void
    {
        $session = $this->session;

        if (!$session->hasParty()) {
            $session->sendMessage(PREFIX . $session->getPlayer()->getTranslatedMsg("message.party.noparty"));
            return;
        }

        $party = $session->getParty();
        $members = $party->getMembers();
        $leaderName = $party->getLeaderName();

        $this->session->sendMessage(PREFIX . "§a{$leaderName}'s party §6members §7(§a" . count($members) . "§7/§a{$party->getSlots()}§7)");
        foreach ($members as $member) {
            $this->session->sendMessage("§7 - §a{$member->getPlayerName()}");
        }
    }

    public function inviteEvent(Session $target)
    {
        $session = $this->session;

        $event = new PartyInviteEvent($session->getParty(), $session, $target);
        $event->call();
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
            return;
        }

        if ($sTarget->hasParty()) {
            $translatedMsg = $player->getTranslatedMsg("message.party.invite.haveparty");
            $player->sendMessage(PREFIX . TextUtils::replaceVars($translatedMsg, ["{player.name}" => $name]));
            return;
        }

        if ($sTarget->hasSessionInvitation($sPlayer)) {
            $translatedMsg = $player->getTranslatedMsg("message.party.invite.haveinvite");
            $player->sendMessage(PREFIX . TextUtils::replaceVars($translatedMsg, ["{player.name}" => $name]));
            return;
        }

        $sTarget->addInvitation(new PartyInvitation($sPlayer, $sTarget, $sPlayer->getParty()->getId()));
        $this->inviteEvent($sTarget);

        $form = new ModalForm(function (NetworkPlayer $target, $data) {
            if (isset($data)) {
                if ($data == true) {
                    $this->acceptParty(SessionFactory::getSession($target));
                } else {
                    $target->sendMessage(PREFIX . "§aYou have declined this invitation, you can accept it by putting /p accept <party>");
                }
            }
        });

        $form->setTitle("§a§lInvitation to Party!");
        $form->setContent("You want to join §6{$sPlayer->getParty()->getLeaderName()}§f's party");
        $form->setButton1("§aYes");
        $form->setButton2("§cNo");
        $target->sendForm($form);
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

            $party->remove($session);
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
                if ($member->getPlayerName() == $target) {
                    if ($member->isOnline()) {
                        $party = $session->getParty();

                        $event = new PartyMemberKickEvent($party, $session, $member);
                        $event->call();

                        $party->remove($member);
                    }
                } else {
                    $translatedMsg = $player->getTranslatedMsg("message.party.player.noexist");
                    $player->sendMessage(PREFIX . TextUtils::replaceVars($translatedMsg, ["{player.name}" => $target]));
                }
            }
        }
    }

    public function acceptParty(Session $target): void
    {
        $session = $target;
        $invitations = $session->getInvitations();

        if (!empty($invitations)) {
            foreach ($invitations as $invitation) {
                $invitation->attemptToAccept();
            }
        } else {
            $session->sendMessage(PREFIX . "§cYou dont have any invitations!");
        }
    }

    public function acceptPartyCmd(string $party): void
    {
        $session = $this->session;
        $invitations = $session->getInvitations();

        if (!empty($invitations)) {
            foreach ($invitations as $invitation) {
                if ($invitation->getSender()->getPlayerName() == $party) {
                    $invitation->attemptToAccept();
                } else {
                    $session->sendMessage(PREFIX . "§cYou dont have any invitations!");
                }
            }
        } else {
            $session->sendMessage(PREFIX . "§cYou dont have any invitations!");
        }
    }
}