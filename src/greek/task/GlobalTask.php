<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 20/7/2021
 *
 * Copyright © 2021 Greek Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\task;

use greek\modules\cosmetics\MCosmetic;
use greek\network\player\NetworkPlayer;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\GenericParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\Particle;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use const pocketmine\START_TIME;

class GlobalTask extends Task
{
    /** @var int */
    private int $radiusFlame = 0, $radiusRain = 0, $radiusEmerald = 0, $radiusWitchCurse = 0, $darkflame = 0;

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        if ($currentTick % 30 === 0) {
            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                if (!$player instanceof NetworkPlayer) return;
                $player->getScoreboardSession()->setScore();
            }
        }

        if ($currentTick % 15 === 0) {
            $this->selectParticle("lava");
        }
        if ($currentTick % 10 === 0) {
            $this->selectParticle("heart");
        }
        if ($currentTick % 5 === 0) {
            $this->selectParticle("rain");
            $this->selectParticle("flamerings");
            $this->selectParticle("witchcurse");
            $this->selectParticle("bloodhelix");

        }
        if ($currentTick % 3 === 0) {
            $this->selectParticle("flame");
            $this->selectParticle("blood");
        }
    }


    private function selectParticle(string $particle): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            $level = $player->getLevel();
            $x = $player->getX();
            $y = $player->getY();
            $z = $player->getZ();
            $location = $player->getLocation();
            if (isset(MCosmetic::$particles[$player->getName()])) {
                switch ($particle) {
                    case "lava":
                        if (MCosmetic::$particles[$player->getName()] === "lava") {
                            $center = new Vector3($x, $y, $z);
                            for ($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 20) {
                                $x = -sin($yaw) + $center->x;
                                $z = cos($yaw) + $center->z;
                                $y = $center->y;
                                $level->addParticle(new LavaParticle(new Vector3($x, $y + 1.5, $z)));
                            }
                        }
                        break;
                    case "heart":
                        if (MCosmetic::$particles[$player->getName()] === "heart") {
                            $level->addParticle(new HeartParticle(new Vector3($x, $y + 2, $z)));
                            $level->addParticle(new HeartParticle(new Vector3($x + 0.6, $y + 1.3, $z + 0.6)));
                            $level->addParticle(new HeartParticle(new Vector3($x - 0.6, $y + 1, $z - 0.6)));
                            $level->addParticle(new HeartParticle(new Vector3($x + 0.6, $y + 0.4, $z - 0.6)));
                            $level->addParticle(new HeartParticle(new Vector3($x - 0.6, $y + 0.8, $z + 0.6)));
                        }
                        break;
                    case "flame":
                        if (MCosmetic::$particles[$player->getName()] === "flame") {
                            $size = 0.8;
                            $a = cos(deg2rad($this->radiusFlame / 0.04)) * $size;
                            $b = sin(deg2rad($this->radiusFlame / 0.04)) * $size;
                            $c = cos(deg2rad($this->radiusFlame / 0.04)) * 0.6;
                            $d = sin(deg2rad($this->radiusFlame / 0.04)) * 0.6;
                            $level->addParticle(new GenericParticle(new Vector3($x + $a, $y + $c + $d + 1.2, $z + $b), Particle::TYPE_FLAME));
                            $level->addParticle(new GenericParticle(new Vector3($x - $b, $y + $c + $d + 1.2, $z - $a), Particle::TYPE_FLAME));
                            $this->radiusFlame++;
                        }
                        break;
                    case "darkflame":
                        if (MCosmetic::$particles[$player->getName()] === "darkflame") {
                            $size = 0.8;
                            $a = cos(deg2rad($this->darkflame / 0.04)) * $size;
                            $b = sin(deg2rad($this->darkflame / 0.04)) * $size;
                            $c = cos(deg2rad($this->darkflame / 0.04)) * 0.6;
                            $d = sin(deg2rad($this->darkflame / 0.04)) * 0.6;
                            $level->addParticle(new GenericParticle(new Vector3($x + $a, $y + $c + $d + 1.2, $z + $b), Particle::TYPE_COLORED_FLAME));
                            $level->addParticle(new GenericParticle(new Vector3($x - $b, $y + $c + $d + 1.2, $z - $a), Particle::TYPE_COLORED_FLAME));
                            $this->darkflame++;
                        }
                        break;
                    case "rain":
                        if (MCosmetic::$particles[$player->getName()] === "rain") {
                            if ($this->radiusRain < 0) {
                                $this->radiusRain++;
                                return;
                            }
                            $a = cos(deg2rad($this->radiusRain / 0.04)) * 0.5;
                            $b = sin(deg2rad($this->radiusRain / 0.04)) * 0.5;
                            $c = cos(deg2rad($this->radiusRain / 0.04)) * 0.8;
                            $d = sin(deg2rad($this->radiusRain / 0.04)) * 0.8;
                            $level->addParticle(new GenericParticle(new Vector3($x - $a, $y + 3, $z - $b), Particle::TYPE_EVAPORATION));
                            $level->addParticle(new GenericParticle(new Vector3($x - $b, $y + 3, $z - $a), Particle::TYPE_EVAPORATION));
                            $level->addParticle(new GenericParticle(new Vector3($x - $a, $y + 2.3, $z - $b), Particle::TYPE_WATER_SPLASH));
                            $level->addParticle(new GenericParticle(new Vector3($x - $b, $y + 2.3, $z - $a), Particle::TYPE_WATER_SPLASH));
                            $level->addParticle(new GenericParticle(new Vector3($x + $c, $y + 3, $z + $d), Particle::TYPE_EVAPORATION));
                            $level->addParticle(new GenericParticle(new Vector3($x + $c, $y + 3, $z + $d), Particle::TYPE_EVAPORATION));
                            $level->addParticle(new GenericParticle(new Vector3($x, $y + 3, $z), Particle::TYPE_EVAPORATION));
                            $level->addParticle(new GenericParticle(new Vector3($x, $y + 2.3, $z), Particle::TYPE_WATER_SPLASH));
                            $this->radiusRain++;
                        }
                        break;
                    case "flamerings":
                        if (MCosmetic::$particles[$player->getName()] === "flamerings") {
                            for ($i = 5; $i > 0; $i -= 0.1) {
                                $radio = $i / 3;
                                $x = $radio * cos(3 * $i);
                                $y = 5 - $i;
                                $z = $radio * sin(3 * $i);
                                $level->addParticle(new FlameParticle($location->add($x, $y, $z)));
                            }

                            for ($i = 5; $i > 0; $i -= 0.1) {
                                $radio = $i / 3;
                                $x = -$radio * cos(3 * $i);
                                $y = 5 - $i;
                                $z = -$radio * sin(3 * $i);
                                $level->addParticle(new FlameParticle($location->add($x, $y, $z)));
                            }
                        }
                        break;
                    case "bloodhelix":
                        if (MCosmetic::$particles[$player->getName()] === "bloodhelix") {

                            if ($this->radiusEmerald < 0) {
                                $this->radiusEmerald++;
                                return;
                            }
                            $time = microtime(true) - START_TIME;
                            $seconds = floor($time % 14);
                            $size = $seconds / 10;
                            $a = cos(deg2rad($this->radiusEmerald / 0.04)) * $size;
                            $b = sin(deg2rad($this->radiusEmerald / 0.04)) * $size;

                            $t = microtime(true) - START_TIME;
                            $s = floor($t % 14);
                            $c = $s / 5;

                            $level->addParticle(new GenericParticle(new Vector3($x - $a, $y - $c + 2.8, $z - $b), Particle::TYPE_REDSTONE, ((255 & 0xff) << 24) | ((189 & 0xff) << 16) | ((3 & 0xff) << 8) | (0 & 0xff)));
                            $level->addParticle(new GenericParticle(new Vector3($x + $a, $y - $c + 2.8, $z + $b), Particle::TYPE_REDSTONE, ((255 & 0xff) << 24) | ((189 & 0xff) << 16) | ((3 & 0xff) << 8) | (0 & 0xff)));
                            $level->addParticle(new GenericParticle(new Vector3($x - $b, $y - $c + 2.8, $z + $a), Particle::TYPE_REDSTONE, ((255 & 0xff) << 24) | ((189 & 0xff) << 16) | ((3 & 0xff) << 8) | (0 & 0xff)));
                            $level->addParticle(new GenericParticle(new Vector3($x + $b, $y - $c + 2.8, $z - $a), Particle::TYPE_REDSTONE, ((255 & 0xff) << 24) | ((189 & 0xff) << 16) | ((3 & 0xff) << 8) | (0 & 0xff)));
                            $this->radiusEmerald++;
                        }
                        break;
                    case "witchcurse":
                        if (MCosmetic::$particles[$player->getName()] === "witchcurse") {
                            if ($this->radiusWitchCurse < 0) {
                                $this->radiusWitchCurse++;
                                return;
                            }
                            $a = cos($this->radiusWitchCurse * 0.2) * 2;
                            $b = sin($this->radiusWitchCurse * 0.2) * 2;
                            $level->addParticle(new GenericParticle(new Vector3($x + $a, $y + 1, $z + $b), Particle::TYPE_WITCH_SPELL));
                            $level->addParticle(new GenericParticle(new Vector3($x - $a, $y + 1, $z - $b), Particle::TYPE_WITCH_SPELL));
                            $level->addParticle(new GenericParticle(new Vector3($x + $b, $y + 1, $z - $a), Particle::TYPE_WITCH_SPELL));
                            $level->addParticle(new GenericParticle(new Vector3($x - $b, $y + 1, $z + $a), Particle::TYPE_WITCH_SPELL));
                            $this->radiusWitchCurse++;
                        }
                        break;
                }
            }
        }
    }
}

