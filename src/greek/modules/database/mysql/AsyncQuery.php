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
        try {
            $this->query($mysqli = new mysqli($this->host,
                $this->user,
                $this->password,
                $this->database));
            $mysqli->close();
        } catch (Exception $exception) {
            var_dump($exception->getMessage());
        }
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