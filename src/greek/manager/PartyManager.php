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
use greek\event\party\PartyLeaderPromoteEvent;
use greek\event\party\PartyLeaveEvent;
use greek\event\party\PartyMemberKickEvent;
use greek\gui\PartyMembersGui;
use greek\modules\form\lib\ModalForm;
use greek\modules\form\lib\SimpleForm;
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

    public function create(): void
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

    public function disband(): void
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

    public function openMembersGui(): void
    {
        $session = $this->session;
        $party = $session->getParty();
        $members = $party->getMembers();

        if (!$session->hasParty()) {
            $session->sendMessage(PREFIX . $session->getPlayer()->getTranslatedMsg("message.party.noparty"));
            return;
        }

        $gui = new PartyMembersGui($party->getLeaderName() . "'s party members");

        foreach ($members as $member) {
            $gui->addPlayerToGui($member->getPlayerName());
        }
        $gui->sendTo($session->getPlayer());
    }

    public function inviteEvent(Session $target): void
    {
        $session = $this->session;

        $event = new PartyInviteEvent($session->getParty(), $session, $target);
        $event->call();
    }

    public function invite(string $name): void
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

    public function leave(): void
    {
        $session = $this->session;
        $player = $session->getPlayer();

        if (!$session->hasParty()) {
            $player->sendMessage(PREFIX . $player->getTranslatedMsg("message.player.stupid"));
        } elseif ($session->isPartyLeader()) {
            $this->disband();
        } else {
            $party = $session->getParty();

            $event = new PartyLeaveEvent($party, $session);
            $event->call();

            $party->remove($session);
        }
    }

    public function kick(string $target): void
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
            $this->disband();
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

    public function setLeader(string $target): void
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
            $translatedMsg = $player->getTranslatedMsg("message.party.setleader.idiot");
            $player->sendMessage(PREFIX . $translatedMsg);
        } else {
            foreach ($members as $member) {
                if ($member->getPlayerName() == $target) {
                    if ($member->isOnline()) {
                        $party = $session->getParty();

                        $event = new PartyLeaderPromoteEvent($party, $session, $member);
                        $event->call();

                        $party->setLeader($member);
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

    /**
     * @todo: Finish | Possibly I should change the Forms api.
     *
     * @param string $target
     */
    public function openPartyPlayerForm(string $target): void
    {
        $session = $this->session;
        $player = $session->getPlayer();
        $player->getInventory()->close($player);

        $form = new SimpleForm(function (NetworkPlayer $player, $data) {
            if (isset($data)) {
                if ($data === "back") {
                    $this->openMembersGui();
                    return;
                }
                $split = explode("-", $data);
                $this->executeAction($split[0], $split[1]);
            }
        });
        $form->setTitle("§7Party Manager");
        $form->setContent("§aWhat do you want to do with §6{$target}§a?");

        //$form->addButton($target, 0, "", "player_$target");

        $form->addButton("§cKick from the party", 0, "", "kick-$target");
        $form->addButton("§bSet Leader", 0, "", "setleader-$target");

        $form->addButton($player->getTranslatedMsg("form.button.back"), 0, "", "back");
        $player->sendForm($form);
    }

    public function executeAction(string $action, string $target): void
    {
        switch ($action) {
            case "kick":
                $this->kick($target);
                break;
            case "setleader":
                $this->setLeader($target);
                break;
        }
    }
}