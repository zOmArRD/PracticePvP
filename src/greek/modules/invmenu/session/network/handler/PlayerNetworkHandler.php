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

interface PlayerNetworkHandler
{
    /**
     * @param Closure $then
     * @return NetworkStackLatencyEntry
     */
    public function createNetworkStackLatencyEntry(Closure $then): NetworkStackLatencyEntry;
}