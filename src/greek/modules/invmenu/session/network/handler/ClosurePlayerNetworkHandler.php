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

namespace greek\modules\invmenu\session\network\handler;

use Closure;
use greek\modules\invmenu\session\network\NetworkStackLatencyEntry;

final class ClosurePlayerNetworkHandler implements PlayerNetworkHandler
{

    /** @var Closure */
    private Closure $creator;

    /**
     * @param Closure $creator
     *
     * @phpstan-param Closure(Closure) : NetworkStackLatencyEntry $creator
     */
    public function __construct(Closure $creator)
    {
        $this->creator = $creator;
    }

    public function createNetworkStackLatencyEntry(Closure $then): NetworkStackLatencyEntry
    {
        return ($this->creator)($then);
    }
}