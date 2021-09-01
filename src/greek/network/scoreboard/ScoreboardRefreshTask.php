<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 6/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\network\scoreboard;

use greek\network\player\NetworkPlayer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class ScoreboardRefreshTask extends Task
{

    public function onRun(int $currentTick)
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (!$player instanceof NetworkPlayer) return;
           $player->getScoreboardSession()->setScore();
        }
    }
}