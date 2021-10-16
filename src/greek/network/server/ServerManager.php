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

use greek\Loader;
use greek\modules\database\mysql\AsyncQueue;
use greek\modules\database\mysql\query\RegisterServerQuery;
use greek\modules\database\mysql\query\SelectQuery;
use greek\modules\database\mysql\query\UpdateRowQuery;
use greek\network\config\Settings;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use const greek\PREFIX;

final class ServerManager
{
    /** @var int */
    protected const REFRESH_TICKS = 60;

    /** @var Server[] */
    public static array $servers = [];

    /** @var Server */
    private static Server $currentServer;

    private static function getConfig(): Config
    {
        return Settings::getConfig("network.data.yml");
    }

    public static function init(): void
    {
        /** @var string $currentServerName */
        $currentServerName = self::getConfig()->get('current.server');
        AsyncQueue::submitQuery(new RegisterServerQuery($currentServerName));

        AsyncQueue::submitQuery(new SelectQuery("SELECT * FROM servers"), function ($rows) use ($currentServerName) {
            foreach ($rows as $row) {
                $server = new Server($row["ServerName"], (int)$row["players"], (bool)$row["isOnline"], (bool)$row["isWhitelisted"]);
                if ($row["ServerName"] === $currentServerName) {
                    self::$currentServer = $server;
                } else {
                    self::$servers[] = $server;
                    Loader::$logger->notice(PREFIX . "A new server has been registered | ($server->serverName)");
                }
            }
        });
        Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($currentServerName): void {
            $players = count(Loader::getInstance()->getServer()->getOnlinePlayers());
            $isWhitelist = (Loader::getInstance()->getServer()->hasWhitelist() ? 1 : 0);
            AsyncQueue::submitQuery(new UpdateRowQuery(["players" => "$players", "isWhitelisted" => "$isWhitelist"], "ServerName", $currentServerName, "servers"));

            foreach (self::getServers() as $server) {
                $server->sync();
            }
        }), self::REFRESH_TICKS);
    }

    public static function reloadServers(): void
    {
        self::$servers = [];

        /** @var string $currentServerName */
        $currentServerName = self::getConfig()->get('current.server');
        AsyncQueue::submitQuery(new SelectQuery("SELECT * FROM servers"), function ($rows) use ($currentServerName) {
            foreach ($rows as $row) {
                $server = new Server($row["ServerName"], (int)$row["players"], (bool)$row["isOnline"], (bool)$row["isWhitelisted"]);
                if ($row["ServerName"] === $currentServerName) {
                    self::$currentServer = $server;
                } else {
                    self::$servers[] = $server;
                    Loader::$logger->notice(PREFIX . "A new server has been registered | ($server->serverName)");
                }
            }
        });
    }

    /**
     * @param string $serverName
     * @param int    $players
     * @param bool   $isOnline
     * @param bool   $isWhitelisted
     *
     * @deprecated Function not tested, possibly not used.
     */
    public static function updateServerData(string $serverName, int $players = 0, bool $isOnline = false, bool $isWhitelisted = false)
    {
        if (!isset(self::$servers[$serverName])) {
            self::$servers[$serverName] = new Server($serverName, $players, $isOnline, $isWhitelisted);
            Loader::$logger->notice("A new server has been registered | ($serverName)");
            return;
        }

        self::$servers[$serverName]->update($serverName, $players, $isOnline, $isWhitelisted);
    }

    /**
     * @param string $name
     *
     * @return Server|null
     * @deprecated Function not tested, possibly not used.
     */
    public static function getServer(string $name): ?Server
    {
        return self::$servers[$name] ?? null;
    }

    /**
     * @return Server
     */
    public static function getCurrentServer(): Server
    {
        return self::$currentServer;
    }

    /**
     * @return Server[]
     */
    public static function getServers(): array
    {
        return self::$servers;
    }

    /**
     * @param string $name
     *
     * @return Server|null
     */
    public static function getServerByName(string $name): ?Server
    {
        $finalServer = null;
        foreach (self::getServers() as $server) {
            if ($server->getServerName() === $name) {
                $finalServer = $server;
            }
        }
        return $finalServer;
    }

    /**
     * @return int
     */
    public static function getPracticePlayers(): int
    {
        $players = 0;
        foreach (self::getServers() as $server) {
            if ($server->getServerName() === self::getConfig()->get("downstream.server")) {
                $players =+$server->getPlayers();
            }
        }
        return (int)$players;
    }
}