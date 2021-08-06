<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 1/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\modules\database\mysql\query;

use Exception;
use greek\modules\database\mysql\AsyncQuery;
use mysqli;
use pocketmine\Server;

class InsertQuery extends AsyncQuery
{
    public $res;

    public string $query;

    public function __construct(string $sqlQuery)
    {
        $this->query = $sqlQuery;
    }

    public function query(mysqli $mysqli): void
    {
        $result = $mysqli->query($this->query);
        $this->res = serialize($result);
    }

    public function onCompletion(Server $server)
    {
        try {
            $this->res = unserialize($this->res);
        } catch (Exception $exception) {
            $this->res = null;
        }
        parent::onCompletion($server);
    }
}