<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 20/7/2021
 *
 * Copyright © 2021 Greek Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\network\server;

use greek\modules\database\mysql\AsyncQueue;
use greek\modules\database\mysql\query\SelectQuery;

class Server
{
    /** @var string  */
    public string $serverName;

    /** @var int  */
    public int $players;

    /** @var bool  */
    public bool $isOnline, $isWhitelisted;

    /**
     * @param string $server
     * @param int    $players
     * @param bool   $isOnline
     * @param bool   $isWhitelisted
     */
    public function __construct(string $server = "Unknown", int $players = 0, bool $isOnline = false, bool $isWhitelisted = false)
    {
        $this->update($server, $players, $isOnline, $isWhitelisted);
    }

    public function update(string $server = "Unknown", int $players = 0, bool $isOnline = false, bool $isWhitelisted = false): void
    {
        $this->setServerName($server);
        $this->setPlayers($players);
        $this->setIsOnline($isOnline);
        $this->setIsWhitelisted($isWhitelisted);
    }

    public function sync(): void{
        AsyncQueue::submitQuery(new SelectQuery("SELECT * FROM servers WHERE ServerName='$this->serverName';"), function ($rows) {
           $row = $rows[0];
           if ($row !== null) {
               $this->setIsOnline((bool)$row["isOnline"]);
               $this->setPlayers((int)$row["players"]);
               $this->setIsWhitelisted((bool)$row["isWhitelisted"]);
           } else {
               $this->setIsOnline((bool)0);
               $this->setPlayers(0);
               $this->setIsWhitelisted((bool)0);
           }

        });
    }

    /**
     * @param string $serverName
     */
    public function setServerName(string $serverName): void
    {
        $this->serverName = $serverName;
    }

    /**
     * @return string
     */
    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @param int $players
     */
    public function setPlayers(int $players): void
    {
        $this->players = $players;
    }

    /**
     * @return int
     */
    public function getPlayers(): int
    {
        return $this->players;
    }

    /**
     * @param bool $isOnline
     */
    public function setIsOnline(bool $isOnline): void
    {
        $this->isOnline = $isOnline;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->isOnline;
    }

    /**
     * @param bool $isWhitelisted
     */
    public function setIsWhitelisted(bool $isWhitelisted): void
    {
        $this->isWhitelisted = $isWhitelisted;
    }

    /**
     * @return bool
     */
    public function isWhitelisted(): bool
    {
        return $this->isWhitelisted;
    }
}