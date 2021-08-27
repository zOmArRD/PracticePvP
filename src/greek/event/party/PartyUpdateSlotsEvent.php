<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 27/8/2021
 *
 *  Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\event\party;

use greek\modules\party\Party;
use greek\network\session\Session;
use JetBrains\PhpStorm\Pure;

class PartyUpdateSlotsEvent extends PartyEvent
{
    /** @var int  */
    private int $slots;

    /**
     * @param Party $party
     * @param Session $session
     * @param int $slots
     */
    #[Pure]
    public function __construct(Party $party, Session $session, int $slots)
    {
        $this->slots = $slots;
        parent::__construct($party, $session);
    }

    /**
     * @return int
     */
    public function getSlots(): int
    {
        return $this->slots;
    }
}