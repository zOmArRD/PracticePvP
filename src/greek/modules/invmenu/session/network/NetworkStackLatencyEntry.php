<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 31/8/2021
 *  
 *  Copyright © 2021 - All Rights Reserved.
 */

declare(strict_types=1);

namespace greek\modules\invmenu\session\network;

use Closure;

final class NetworkStackLatencyEntry
{

    /** @var int */
    public int $timestamp;

    /** @var int */
    public int $network_timestamp;

    /** @var Closure */
    public Closure $then;

    public function __construct(int $timestamp, Closure $then, ?int $network_timestamp = null)
    {
        $this->timestamp = $timestamp;
        $this->then = $then;
        $this->network_timestamp = $network_timestamp ?? $timestamp;
    }
}