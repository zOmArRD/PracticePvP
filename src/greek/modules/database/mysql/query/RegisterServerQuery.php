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

use greek\modules\database\mysql\AsyncQuery;
use mysqli;

class RegisterServerQuery extends AsyncQuery
{
    /** @var string */
    public string $serverName;

    public function __construct(string $serverName)
    {
        $this->serverName = $serverName;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void
    {
        $result = $mysqli->query("SELECT * FROM servers WHERE ServerName='$this->serverName';");
        $assoc = $result->fetch_assoc();
        if (is_null($assoc)) {
            $mysqli->query("INSERT INTO servers(ServerName, isOnline, isWhitelisted, players) VALUES ('$this->serverName', 1, 0, 0);");
            return;
        }
        $mysqli->query("UPDATE servers SET isOnline=1 WHERE ServerName='$this->serverName';");
    }
}