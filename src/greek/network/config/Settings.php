<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 2/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\network\config;

use greek\Loader;
use greek\network\utils\TextUtils;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use const greek\PREFIX;
use const greek\SPAWN_OPTIONS;

class Settings
{
    /** @var string */
    public static string $prefix, $serverName;

    /** @var array */
    public static array $database, $spawn_options;

    /**
     * It is responsible for giving the value to some important variables.
     *
     * @param Config $config
     */
    static final public function init(Config $config): void
    {
        $general = $config->get('general');

        self::$prefix = TextUtils::replaceColor($general['prefix']);

        /* Saves in an array the data from the database provided by the config. */
        self::$database = $config->get('database');

        /* Options for spawn */
        $jsonData = file_get_contents(self::getResourcesFolder() . 'spawn.options.json');
        $jsonData = json_decode($jsonData, true);
        self::$spawn_options = $jsonData['spawn'];


        define('greek\PREFIX', Settings::$prefix);
        define('greek\DATABASE', Settings::$database);
        define('greek\SPAWN_OPTIONS', Settings::$spawn_options);

        if (SPAWN_OPTIONS['enabled']) {
            $spawn = SPAWN_OPTIONS;
            if (!Server::getInstance()->isLevelLoaded($spawn['world.name'])) {
                Server::getInstance()->loadLevel($spawn['world.name']);
            }
            Server::getInstance()->getLevelByName($spawn['world.name'])->setTime(Level::TIME_DAY);
            Server::getInstance()->getLevelByName($spawn['world.name'])->stopTime();
        } else {
            Server::getInstance()->getDefaultLevel()->stopTime();
        }

        Loader::$logger->info(self::$prefix . "§a" . "Variable values loaded correctly.");
    }

    /**
     * @return Config
     */
    static public function getDefaultConfig(): Config
    {
        return Loader::getInstance()->getConfig();
    }

    /**
     * @param string $archive
     * @param int $type
     * @return Config
     */
    static public function getConfig(string $archive, int $type = Config::YAML): Config
    {
        return new Config(Loader::getInstance()->getDataFolder() . $archive, $type);
    }

    public static function getResourcesFolder(): string
    {
        return Loader::getInstance()->getResourcesFolder();
    }

    /**
     * Update the position of the Spawn.
     */
    static public function updateSpawn(string $world, array $spawnData = [0, 0, 0, 0, 0])
    {
        $config = self::getDefaultConfig()->getAll();
        $k = $config["spawn"];

        $k['enabled'] = true;
        $k['world.name'] = $world;
        $k['x'] = $spawnData[0];
        $k['y'] = $spawnData[1];
        $k['z'] = $spawnData[2];
        $k['yaw'] = $spawnData[3];
        $k['pitch'] = $spawnData[4];
        $k['min.void'] = SPAWN_OPTIONS['min.void'];

        self::getDefaultConfig()->setAll($k);
        self::getDefaultConfig()->save();
        //$player->sendMessage(PREFIX . "§a" ."You have successfully changed the spawn point.");
        var_dump($k);
    }
}