<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 26/8/2021
 *
 *  Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\modules\party;

use greek\network\session\Session;
use greek\network\session\SessionFactory;

class Party
{
    /** @var string */
    private string $id;

    /** @var int */
    private int $slots;

    /** @var Session */
    private Session $leader;

    /** @var array */
    private array $members = [];

    /** @var bool */
    private bool $public = false;

    public function __construct(string $id, Session $leader)
    {
        $this->id = $id;
        $this->leader = $leader;
        $this->members[] = $leader;
        $this->slots = 4;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSlots(): int
    {
        return $this->slots;
    }

    public function setSlots(int $slots): void
    {
        $this->slots = $slots;
    }

    public function getLeader(): Session
    {
        return $this->leader;
    }

    public function getLeaderName(): string
    {
        return $this->getLeader()->getPlayer()->getName();
    }

    public function setLeader(Session $leader): void
    {
        $this->leader = $leader;
    }

    /**
     * @return array
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    public function hasMember(Session $session): bool
    {
        return in_array($session, $this->getMembers(), true);
    }

    public function hasMemberByName(string $username): bool
    {
        return SessionFactory::hasSessionByName($username) && $this->hasMember(SessionFactory::getSessionByName($username));
    }

    public function isFull(): bool
    {
        return count($this->getMembers()) >= $this->slots;
    }

    public function isPublic(bool $public): bool
    {
        return $this->public;
    }

    public function setPublic(bool $value = false)
    {
        $this->public = $value;
    }

    public function add(Session $session): void
    {
        if (!$this->hasMember($session)) {
            $this->members[] = $session;
        }
        $session->setParty($this);
    }

    public function remove(Session $session): void
    {
        if ($this->hasMember($session)) {
            unset($this->members[array_search($session, $this->getMembers(), true)]);
        }
        $session->setParty(null);
        $session->setPartyChat(false);
    }

    public function sendMessage(string $message, ?Session $ignore_member = null): void
    {
        foreach ($this->members as $member) {
            if ($ignore_member !== null and $member->getPlayerName() === $ignore_member->getPlayerName()) continue;
            $member->sendMessage($message);
        }
    }

    public function uploadToMySQL(){

    }
}