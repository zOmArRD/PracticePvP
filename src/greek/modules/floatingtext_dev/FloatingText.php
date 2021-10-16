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

namespace greek\modules\floatingtext;

use greek\network\config\Settings;
use greek\network\player\NetworkPlayer;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;

final class FloatingText extends FloatingTextBackend
{
    private NetworkPlayer $player;
    
    public function __construct(NetworkPlayer $player)
    {
        $this->player = $player;
        $this->loadText();
    }
    
    function loadText(): void
    {
        foreach ($this->getConfig()->get("text.data") as $text){
            $position = explode(",", $text["Position"]);
            $this->create($text["Id"], $this->player, new Vector3((float)$position[0], (float)$position[1], (float)$position[2]));
            $this->updateText($text["Id"], $text["Text"]);
        }
    }
    
    function getConfig(): Config
    {
        return Settings::getConfig("floatingtext.yml");
    }
}