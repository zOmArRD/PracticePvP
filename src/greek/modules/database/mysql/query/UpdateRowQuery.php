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

namespace greek\modules\database\mysql\query;

use greek\Loader;
use greek\modules\database\mysql\AsyncQuery;
use mysqli;

class UpdateRowQuery extends AsyncQuery
{
    /** @var string|null  */
    public ?string $table;

    public string $updates, $playerName;

    public function __construct(array $updates, string $playerName, string $table = null)
    {
        $this->updates = serialize($updates);
        $this->playerName = $playerName;

        if ($table === null) {
            Loader::$logger->error("Unable to update the changes in the database");
            return;
        }
        $this->table = $table;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void
    {
        $updates = [];
        foreach (unserialize($this->updates) as $k => $v) {
            $updates = "$k='$v'";
        }
        $mysqli->query("UPDATE {$this->table} SET " . implode(",", $updates) . " WHERE ign='{$this->playerName}';");
    }
}