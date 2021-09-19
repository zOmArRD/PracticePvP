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

namespace greek\modules\database\mysql;

use Exception;
use mysqli;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

abstract class AsyncQuery extends AsyncTask
{
    /** @var string  */
    public string $host, $user, $password, $database;

    public function onRun()
    {
        $this->query($mysqli = new mysqli($this->host, $this->user, $this->password, $this->database));
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }
        $mysqli->close();
    }
    /**
     * @param Server $server
     */
    public function onCompletion(Server $server)
    {
        parent::onCompletion($server);
        AsyncQueue::activateCallback($this);
    }

    /**
     * @param mysqli $mysqli
     */
    abstract public function query(mysqli $mysqli): void;
}