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
use greek\Loader;
use greek\modules\database\mysql\AsyncQuery;
use mysqli;
use pocketmine\Server;

class SelectQuery extends AsyncQuery
{
    public $rows;

    public string $query;

    public function __construct(string $sqlQuery)
    {
        $this->query = $sqlQuery;
    }

    public function query(mysqli $mysqli): void
    {
        $result = $mysqli->query($this->query);
        $rows = [];
        try {
            if ($result !== false) {
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                $this->rows = serialize($rows);
            }
        } catch (Exception $exception) {
            var_dump($exception->getMessage());
        }
    }

    public function onCompletion(Server $server)
    {
        if ($this->rows === null) Loader::$logger->error("Error while executing query. Please check database settnigs and try again.");
        $this->rows = unserialize($this->rows);
        parent::onCompletion($server);
    }
}