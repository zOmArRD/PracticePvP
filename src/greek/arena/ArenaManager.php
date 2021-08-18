<?php
namespace greek\arena;

use greek\arena\instances\DuelArena;
use greek\arena\instances\Map;
use greek\Loader;
use pocketmine\utils\Config;

class ArenaManager {

    /**
     * @var Map[] $maps
     * @var DuelArena[] $arenas
     */
    public static array $maps = [];
    public static array $arenas = [];
    public static ArenaManager $instance;

    public function __construct()
    {
        self::$instance = $this;
    }

    public static function getInstance(): ArenaManager {
        return self::$instance;
    }

    public function getMapFile() : Config {
        return new Config(Loader::getInstance()->getDataFolder() . "maps.yml");
    }

    public function registerAllMaps(){
        foreach ($this->getMapFile()->get("maps") as $key => $mapInfo){
            $this->registerMap((int)$key);
        }
    }

    public function registerMap(int $id){
        $all = $this->getMapFile()->getAll();
        if(isset($all[$id])){

        }
    }
}