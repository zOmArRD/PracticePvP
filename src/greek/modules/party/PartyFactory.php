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

class PartyFactory
{
    /** @var array */
    private static array $parties = [];

    public static function getParties(): array
    {
        return self::$parties;
    }

    public static function getParty(string $id): ?Party
    {
        return self::$parties[$id];
    }

    public static function existParty(Party $party): bool
    {
        return array_key_exists($party->getId(), self::$parties);
    }

    public static function addParty(Party $party): void
    {
        if (!self::existParty($party)) {
            self::$parties[$party->getId()] = $party;
        }
    }

    public static function removeParty(Party $party): void
    {
        if (self::existParty($party)) {
            unset(self::$parties[$party->getId()]);
        }
    }

    public static function getPartyFromMySQL()
    {

    }
}