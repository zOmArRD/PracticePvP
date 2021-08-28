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

class PartyInviteEvent extends PartyEvent
{
    /** @var Session  */
    private Session $target;

    /**
     * @param Party $party
     * @param Session $session
     * @param Session $target
     */
    public function __construct(Party $party, Session $session, Session $target)
    {
        $this->target = $target;
        parent::__construct($party, $session);
    }

    /**
     * @return Session
     */
    public function getTarget(): Session
    {
        return $this->target;
    }
}