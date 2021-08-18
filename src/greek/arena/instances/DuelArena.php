<?php
/*
 * Copyright © GreekMC Network - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Gottfried Rosenberger <gottfried@greekmc.net>, 2021
 */

namespace greek\arena\instances;

use pocketmine\level\Level;
use pocketmine\Player;

class DuelArena {

    /**
     * Behavior values
     */
    public bool $damage = false;
    public bool $fallDamage = false;
    public bool $breakable = false;

    /**
     * Arrays of variable data
     * @var Player[] $players
     * @var Player[] $spectators
     */
    public array $players = [];
    public array $playerSlots = [];
    public array $spectators = [];

    /**
     * General information
     */
    public Level $level;
    public Map $map;

    /**
     * Variable integers.
     */
    public int $countdown = 5;
    public int $seconds = 0;
    public int $status;
    public bool $won = false;
    public Player $winner;


    public function __construct(Map $map)
    {
        $this->map = $map;
        $this->level = $map->generateMap(1);
    }

}