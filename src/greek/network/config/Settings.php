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
use pocketmine\Player;
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

        /* Array Options for spawn */
        self::$spawn_options = $config->get('spawn.options');

        define('greek\PREFIX', Settings::$prefix);
        define('greek\DATABASE', Settings::$database);
        define('greek\SPAWN_OPTIONS', Settings::$spawn_options);

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

    /**
     * Update the position of the Spawn.
     *
     * @param Player $player
     */
    static public function updateSpawn(Player $player)
    {
        $config = self::getDefaultConfig()->getAll();
        $k = $config["spawn.options"];

        $k['enabled'] = true;
        $k['world.name'] = $player->level->getName();
        $k['x'] = $player->getX();
        $k['y'] = $player->getY();
        $k['z'] = $player->getZ();
        $k['yaw'] = $player->getYaw();
        $k['pitch'] = $player->getPitch();
        $k['min.void'] = SPAWN_OPTIONS['min.void'];

        self::getDefaultConfig()->setAll($config);
        self::getDefaultConfig()->save();
        $player->sendMessage(PREFIX . "§a" ."You have successfully changed the spawn point.");
    }
}