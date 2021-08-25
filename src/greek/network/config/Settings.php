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

class Settings
{
    /** @var string */
    public static string $prefix, $lobby, $serverName;

    /** @var array */
    public static array $database;

    /** @var float */
    public static float $x, $y, $z, $pitch, $yaw;

    static final public function init(Config $config): void
    {
        $general = $config->get(k: 'general');
        $world = $config->get(k: 'spawn-map');

        // Saves in an array the data from the database provided by the config.
        self::$database = $config->get(k: 'database');

        self::$prefix = TextUtils::replaceColor($general['prefix']);

        // World Spawn Settings.
        self::$lobby = $world['World-name'];
        self::$x = $world['X'];
        self::$y = $world['Y'];
        self::$z = $world['Z'];
        self::$yaw = $world['Yaw'];
        self::$pitch = $world['Pitch'];

        Loader::$logger->info(message: self::$prefix . "§a" . "config.yml data loaded successfully!");
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
        return new Config(file: Loader::getInstance()->getDataFolder() . $archive, type: $type);
    }

    /**
     * Update the position of the Spawn.
     *
     * @param Player $player
     */
    static public function updateSpawn(Player $player)
    {
        $config = self::getDefaultConfig()->getAll();

        $config['spawn-map']['Word-name'] = $player->getLevel()->getName();
        $config['spawn-map']['X'] = $player->getX();
        $config['spawn-map']['Y'] = $player->getY();
        $config['spawn-map']['Z'] = $player->getZ();
        $config['spawn-map']['Yaw'] = $player->getYaw();
        $config['spawn-map']['Pitch'] = $player->getPitch();

        self::getDefaultConfig()->setAll($config);
        self::getDefaultConfig()->save();
        $player->sendMessage(self::$prefix . "§a" ."You have successfully changed the spawn point.");
    }
}