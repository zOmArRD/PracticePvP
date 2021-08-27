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

class PartyLeaderPromoteEvent extends PartyEvent
{
    /** @var Session  */
    private Session $newLeader;

    /**
     * @param Party $party
     * @param Session $session
     * @param Session $newLeader
     */
    #[Pure]
    public function __construct(Party $party, Session $session, Session $newLeader)
    {
        $this->newLeader = $newLeader;
        parent::__construct($party, $session);
    }

    /**
     * @return Session
     */
    public function getNewLeader(): Session
    {
        return $this->newLeader;
    }
}