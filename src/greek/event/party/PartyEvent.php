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
use greek\network\player\NetworkPlayer;
use greek\network\session\Session;
use pocketmine\event\Cancellable;
use pocketmine\event\Event;

abstract class PartyEvent extends Event implements Cancellable
{
    /** @var Party */
    private Party $party;

    /** @var Session */
    private Session $session;

    /**
     * @param Party   $party
     * @param Session $session
     */
    public function __construct(Party $party, Session $session)
    {
        $this->party = $party;
        $this->session = $session;
    }

    /**
     * @return Party
     */
    public function getParty(): Party
    {
        return $this->party;
    }

    /**
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
    }

    /**
     * @return NetworkPlayer
     */
    public function getPlayer(): NetworkPlayer
    {
        return $this->getSession()->getPlayer();
    }
}