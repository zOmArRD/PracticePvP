<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 2/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\network;

use greek\network\config\Settings;
use greek\network\player\NetworkPlayer;
use pocketmine\network\mcpe\protocol\TransferPacket;

class Session
{
    /** @var NetworkPlayer */
    public NetworkPlayer $player;

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

    public function updateSession(): void
    {
        $player = $this->getPlayer();
        $player->teleportToLobby();
    }

    public function closeSession(): void
    {
        if (isset(NetworkPlayer::$data[$this->getPlayer()->getName()])) {
            unset(NetworkPlayer::$data[$this->getPlayer()->getName()]);
            $this->unSetPartyMode();
        }
    }

    /**
     * @todo Finalize
     */
    public function setPartyMode(): void
    {
        $player = $this->player;
        if (!$player->isPartyMode()) {
            $player->setPartyMode(true);
            $player->getPartyItems();
            $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.party.create"));
        } else {
            $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.party.exist"));
        }
    }

    /**
     * @todo Finalize
     */
    public function unSetPartyMode(): void
    {
        $player = $this->player;
        if ($player->isPartyMode()) {
            $player->setPartyMode(false);
            $player->teleportToLobby();
            $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.party.disband"));
        } else {
            $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.party.noparty"));
        }
    }


    /**
     * Transfer the player to another server (Must be proxied.)
     *
     * @param string $server
     */
    public function transfer(string $server): void
    {
        $this->player->sendMessage($this->player->getTranslatedMsg("message.server.connecting"));
        $pk = new TransferPacket();
        $pk->address = $server;
        $this->player->dataPacket($pk);
    }
}