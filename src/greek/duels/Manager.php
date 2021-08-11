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
}