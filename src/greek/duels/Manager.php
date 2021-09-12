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

namespace greek\duels;

use greek\modules\database\mysql\AsyncQueue;
use greek\modules\database\mysql\query\InsertQuery;

class Manager
{
    public function changeFFAMode(string $mode, string $playerName): void
    {
        AsyncQueue::submitQuery(new InsertQuery("UPDATE ffa_data SET mode = '$mode' WHERE ign = '$playerName'"));
    }

    /**
     * It is responsible for uploading the data provided to MySQL.
     *
     * @param string     $key
     * @param string|int $value
     * @param string     $ign
     * @deprecated
     */
    public function updateTable(string $key, string|int $value, string $ign): void
    {
        AsyncQueue::submitQuery(new InsertQuery("UPDATE duel_data SET $key=$value WHERE ign=$ign"));
    }

    /**
     * It is responsible for uploading the data provided to MySQL.
     *
     * @param string $player
     * @param string $duelType
     * @param string $queueKit
     * @param int    $isInviteDuel
     * @param string $playerInvited
     */
    public function updateDownStreamData(string $player, string $duelType, string $queueKit, int $isInviteDuel = 0, string $playerInvited = ""): void
    {
        AsyncQueue::submitQuery(new InsertQuery("UPDATE duel_data SET DuelType = '$duelType', QueueKit = '$queueKit', isInviteDuel = $isInviteDuel, playerInvited = '$playerInvited' WHERE ign = '$player';"));
    }
}