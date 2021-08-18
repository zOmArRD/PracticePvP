<?php
/*
 * Copyright © GreekMC Network - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Gottfried Rosenberger <gottfried@greekmc.net>, 2021
 */

namespace greek\arena\instances;

use greek\Loader;
use PharData;
use pocketmine\level\Level;
use pocketmine\Server;

class Map {
    // Setup details
    public string $id;
    public string $name;
    public string $level;
    public array $slots;
    public string $type;
    public int $maxy;
    public int $miny;

    public function __construct(string $id, string $name, string $level, array $slots, string $type, int $miny = 0, int $maxy = 256)
    {
        $this->miny = $miny;
        $this->maxy = $maxy;
        $this->id = $id;
        $this->name = $name;
        $this->level = $level;
        $this->slots = $slots;
        $this->type = $type;
    }

    public function generateMap(int $time): ?Level
    {
        $tries = 0;
        if (!is_file($file = Loader::getInstance()->getDataFolder() . "maps/" . "{$this->level}/" . "/{$this->level}.tar") && !is_file($file = Loader::getInstance()->getDataFolder() . "maps/" . "{$this->level}/" . "/{$this->level}.tar")) {
            return null;
        }

        // Loop in case there are already maps with that name.
        while (true) {
            $name = $this->level . $tries;
            if (!Server::getInstance()->getLevelByName($name) && !is_dir(Server::getInstance()->getDataPath() . "worlds/" . $name)) {
                $tar = new PharData($file);
                $tar->extractTo(Server::getInstance()->getDataPath() . "worlds/" . $name, null, true);
                Server::getInstance()->loadLevel($name);
                $level = Server::getInstance()->getLevelByName($name);
                $level->setAutoSave(false);

                $times = [23000, 1000, 18000];
                $level->setTime($times[$time]);
                $level->stopTime();

                return $level;
            }
            $tries++;
        }
    }
}