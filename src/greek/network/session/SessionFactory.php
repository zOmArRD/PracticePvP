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

use greek\network\player\NetworkPlayer;

final class SessionFactory
{
    /** @var Session[] */
    private static array $sessions = [];

    /**
     * @return Session[]
     */
    public static function getSessions(): array
    {
        return self::$sessions;
    }

    /**
     * @param string $username
     * @return Session|null
     */
    public static function getSessionByName(string $username): ?Session
    {
        return self::$sessions[strtolower($username)] ?? null;
    }

    /**
     * @param NetworkPlayer $player
     * @return Session|null
     */
    public static function getSession(NetworkPlayer $player): ?Session
    {
        return self::getSessionByName(strtolower($player->getName())) ?? null;
    }

    /**
     * @param string $username
     * @return bool
     */
    public static function hasSessionByName(string $username): bool
    {
        return array_key_exists(strtolower($username), self::$sessions);
    }

    /**
     * @param NetworkPlayer $player
     * @return bool
     */
    public static function hasSession(NetworkPlayer $player): bool
    {
        return self::hasSessionByName($player->getName());
    }

    /**
     * @param NetworkPlayer $player
     */
    public static function createSession(NetworkPlayer $player): void
    {
        if (!self::hasSession($player)) {
            self::$sessions[strtolower($player->getName())] = new Session($player);
        }
    }

    /**
     * @param NetworkPlayer $player
     */
    public static function removeSession(NetworkPlayer $player): void
    {
        if (!self::hasSession($player)) {
            unset(self::$sessions[strtolower($player->getName())]);
        }
    }
}