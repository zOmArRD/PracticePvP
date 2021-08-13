<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 4/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\network\scoreboard;

use greek\Loader;
use greek\network\player\NetworkPlayer;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

abstract class ScoreboardAPI
{
    /** @var array $scoreboards */
    private array $scoreboards = [];

    /**
     * @param NetworkPlayer $player
     * @param string $objectiveName
     * @param string $displayName
     */
    public function new(NetworkPlayer $player, string $objectiveName, string $displayName): void
    {
        if (isset($this->scoreboards[$player->getName()])) {
            $this->remove($player);
        }
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = $objectiveName;
        $pk->displayName = $displayName;
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $player->sendDataPacket($pk);
        $this->scoreboards[$player->getName()] = $objectiveName;
    }

    /**
     * @param NetworkPlayer $player
     * @param int $score
     * @param string $message
     */
    public function setLine(NetworkPlayer $player, int $score, string $message): void
    {
        if (!isset($this->scoreboards[$player->getName()])) {
            Loader::$logger->error("Cannot set a score to a player with no scoreboard");
            return;
        }

        if ($score > 15 || $score < 0) {
            Loader::$logger->error("Score must be between the value of 1-15. $score out of range");
            return;
        }

        $objectiveName = $this->getObjectiveName($player);

        $entry = new ScorePacketEntry();
        $entry->objectiveName = $objectiveName;
        $entry->type = $entry::TYPE_FAKE_PLAYER;
        $entry->customName = $message;
        $entry->score = $score;
        $entry->scoreboardId = $score;

        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_CHANGE;
        $pk->entries[] = $entry;
        $player->sendDataPacket($pk);
    }

    /**
     * @param NetworkPlayer $player
     * @return string|null
     */
    public function getObjectiveName(NetworkPlayer $player): ?string
    {
        return $this->scoreboards[$player->getName()] ?? null;
    }

    /**
     * @param NetworkPlayer $player
     */
    public function remove(NetworkPlayer $player): void
    {
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = "greek.practice";
        $player->sendDataPacket($pk);
        unset($this->scoreboards[$player->getName()]);
    }
}