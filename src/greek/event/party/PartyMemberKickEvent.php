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

final class PartyMemberKickEvent extends PartyEvent
{
    /** @var Session */
    private Session $member;

    /**
     * @param Party   $party
     * @param Session $session
     * @param Session $member
     */
    public function __construct(Party $party, Session $session, Session $member)
    {
        $this->member = $member;
        parent::__construct($party, $session);
    }

    /**
     * @return Session
     */
    public function getMember(): Session
    {
        return $this->member;
    }
}