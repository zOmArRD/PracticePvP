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

use greek\modules\database\mysql\AsyncQueue;
use greek\modules\database\mysql\query\InsertQuery;
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

    /** @var Session[] */
    private array $members = [];

    /** @var bool */
    private bool $public = false;

    public function __construct(string $id, Session $leader)
    {
        $this->id = $id;
        $this->leader = $leader;
        $this->members[] = $leader;
        $this->slots = 12;
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
        $this->updateMySQL();
    }

    /**
     * @return Session
     */
    public function getLeader(): Session
    {
        return $this->leader;
    }

    /**
     * @return string
     */
    public function getLeaderName(): string
    {
        return $this->getLeader()->getPlayer()->getName();
    }

    public function setLeader(Session $leader): void
    {
        $this->leader = $leader;
        $this->updateMySQL();
    }

    /**
     * @return Session[]
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
        $this->updateMySQL();
    }

    public function add(Session $session): void
    {
        if (!$this->hasMember($session)) {
            $this->members[] = $session;
        }
        $session->setParty($this);
        $this->updateMySQL();
    }

    public function remove(Session $session): void
    {
        if ($this->hasMember($session)) {
            unset($this->members[array_search($session, $this->getMembers(), true)]);
        }
        $session->setParty(null);
        $session->setPartyChat(false);
        $this->updateMySQL();
    }

    public function sendMessage(string $message, ?Session $ignore_member = null): void
    {
        foreach ($this->members as $member) {
            if ($ignore_member !== null and $member->getPlayerName() === $ignore_member->getPlayerName()) continue;
            $member->sendMessage($message);
        }
    }

    public function uploadToMySQL(){
        $membersNames = "";
        foreach ($this->members as $member) {
            $membersNames .= $member->getPlayerName() . ",";
        }
        $isPublic = $this->public ? 1 : 0;
        AsyncQueue::submitQuery(new InsertQuery("INSERT INTO parties(id, leader, members, slots, public) VALUES ('{$this->getId()}', '{$this->getLeaderName()}', '{$membersNames}', {$this->getSlots()}, {$isPublic});"));
    }

    public function updateMySQL(){
        $membersNames = "";
        foreach ($this->members as $member) {
            $membersNames .= $member->getPlayerName() . ",";
        }
        $isPublic = $this->public ? 1 : 0;
        AsyncQueue::submitQuery(new InsertQuery("UPDATE parties SET leader = '{$this->getLeaderName()}', members = '{$membersNames}', slots = {$this->getSlots()}, public = {$isPublic} WHERE id='{$this->getId()}';"));
    }

    public function removeFromMySQL(){
        AsyncQueue::submitQuery(new InsertQuery("DELETE FROM parties WHERE id='{$this->getId()}';"));
    }
}