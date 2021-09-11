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

use greek\modules\database\mysql\query\InsertQuery;
use pocketmine\Server;
use const greek\DATABASE;

class AsyncQueue
{
    /** @var array  */
    private static array $callbacks = [];

    /**
     * @param AsyncQuery    $asyncQuery
     * @param callable|null $callbackFunction
     */
    static public function submitQuery(AsyncQuery $asyncQuery, ?callable $callbackFunction = null): void
    {
        self::$callbacks[spl_object_hash($asyncQuery)] = $callbackFunction;
        $asyncQuery->host = DATABASE['host'];
        $asyncQuery->user = DATABASE['user'];
        $asyncQuery->password = DATABASE['password'];
        $asyncQuery->database = DATABASE['database'];
        Server::getInstance()->getAsyncPool()->submitTask($asyncQuery);
    }

    /**
     * @param AsyncQuery $asyncQuery
     */
    static public function activateCallback(AsyncQuery $asyncQuery): void
    {
        $callable = self::$callbacks[spl_object_hash($asyncQuery)] ?? null;
        if (is_callable($callable)) $callable($asyncQuery["rows"]);
    }

    public static function insertQuery(string $sqlQuery): void
    {
        AsyncQueue::submitQuery(new InsertQuery($sqlQuery));
    }
}