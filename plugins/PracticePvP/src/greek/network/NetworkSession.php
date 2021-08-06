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

use greek\network\player\NetworkPlayer;
use greek\network\scoreboard\Scoreboard;

class NetworkSession
{
    /** @var NetworkPlayer  */
    public NetworkPlayer $player;

    /** @var array  */
    public static array $playerData = [];

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
        if (isset(self::$playerData[$this->getPlayer()->getName()])) {
            unset(self::$playerData[$this->getPlayer()->getName()]);
        }
    }

}