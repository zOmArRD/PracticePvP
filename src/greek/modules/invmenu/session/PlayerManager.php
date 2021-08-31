<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 31/8/2021
 *  
 *  Copyright © 2021 - All Rights Reserved.
 */

declare(strict_types=1);

namespace greek\modules\invmenu\session;

use greek\modules\invmenu\InvMenuEventHandler;
use greek\modules\invmenu\session\network\handler\PlayerNetworkHandlerRegistry;
use greek\network\player\NetworkPlayer;

final class PlayerManager
{

    /** @var PlayerSession[] */
    private static array $sessions = [];

    public static function create(NetworkPlayer $player): void
    {
        self::$sessions[$player->getRawUniqueId()] = new PlayerSession(
            $player,
            new PlayerNetwork(
                $player,
                PlayerNetworkHandlerRegistry::get(InvMenuEventHandler::pullCachedDeviceOS($player))
            )
        );
    }

    public static function destroy(NetworkPlayer $player): void
    {
        if (isset(self::$sessions[$uuid = $player->getRawUniqueId()])) {
            self::$sessions[$uuid]->finalize();
            unset(self::$sessions[$uuid]);
        }
    }

    public static function get(NetworkPlayer $player): ?PlayerSession
    {
        return self::$sessions[$player->getRawUniqueId()] ?? null;
    }

    public static function getNonNullable(NetworkPlayer $player): PlayerSession
    {
        return self::$sessions[$player->getRawUniqueId()];
    }
}