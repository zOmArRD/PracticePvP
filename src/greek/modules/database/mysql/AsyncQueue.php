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

use greek\network\config\Settings;
use pocketmine\Server;

class AsyncQueue
{
    /** @var array  */
    private static array $callbacks = [], $values = [];

    /**
     * @param AsyncQuery $asyncQuery
     * @param callable|null $callbackFunction
     * @param array|null $valuesToPass
     */
    static public function submitQuery(AsyncQuery $asyncQuery, ?callable $callbackFunction = null, ?array $valuesToPass = null): void
    {
        self::$callbacks[spl_object_hash(object: $asyncQuery)] = $callbackFunction;
        self::$values[spl_object_hash(object: $asyncQuery)] = $valuesToPass;
        $asyncQuery->host = Settings::$database['host'];
        $asyncQuery->user = Settings::$database['user'];
        $asyncQuery->password = Settings::$database['password'];
        $asyncQuery->database = Settings::$database['database'];
        Server::getInstance()->getAsyncPool()->submitTask(task: $asyncQuery);
    }

    /**
     * @param AsyncQuery $asyncQuery
     */
    static public function activateCallback(AsyncQuery $asyncQuery): void
    {
        $callable = self::$callbacks[spl_object_hash(object: $asyncQuery)] ?? null;
        $values = self::$values[spl_object_hash($asyncQuery)] ?? null;
        if (is_callable($callable)) $callable($asyncQuery["rows"], $values);
    }
}