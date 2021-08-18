<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 1/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek;

use greek\commands\Command;
use greek\events\ListenerManager;
use greek\modules\database\mysql\AsyncQueue;
use greek\modules\database\mysql\query\InsertQuery;
use greek\modules\languages\Lang;
use greek\network\config\Settings;
use greek\network\player\skin\PersonaSkinAdapter;
use greek\task\TaskManager;
use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginLogger;
use pocketmine\utils\Config;

final class Loader extends PluginBase
{

    /** @var string */
    public const CONFIG_VER = "1.0.0", ARCHIVE_STRING = "config.yml";

    /** @var Loader */
    public static Loader $instance;

    /** @var PluginLogger */
    public static PluginLogger $logger;

    public function onLoad(): void
    {
        self::setInstance($this);
        self::setLogger($this->getLogger());

        $this->verifySettings();

        /* Check in the database if the necessary Practice tables have been created. */
        AsyncQueue::submitQuery(new InsertQuery("CREATE TABLE IF NOT EXISTS settings(ign TEXT UNIQUE, language TEXT, ShowScoreboard INT)"));
        AsyncQueue::submitQuery(new InsertQuery("CREATE TABLE IF NOT EXISTS practice_downstream(ign TEXT UNIQUE, DuelType TEXT, QueueKit TEXT, isInviteDuel bool, playerInvited TEXT)"));
        AsyncQueue::submitQuery(new InsertQuery("CREATE TABLE IF NOT EXISTS ffa_data(ign TEXT, mode TEXT)"));
    }

    public function onEnable(): void
    {
        /* It is responsible for recording all events. */
        new ListenerManager();

        /* Register the plugin commands. */
        new Command();

        /* It is responsible for registering the tasks, and loading it. */
        new TaskManager();

        /* It is responsible for supporting the skin person. */
        SkinAdapterSingleton::set(new PersonaSkinAdapter());

        self::$logger->info(Settings::$prefix . "§a" . "plugin loaded");
    }

    /**
     * @param Loader $instance
     */
    public static function setInstance(Loader $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * @return Loader
     */
    public static function getInstance(): Loader
    {
        return self::$instance;
    }

    /**
     * @param PluginLogger $logger
     */
    public static function setLogger(PluginLogger $logger): void
    {
        self::$logger = $logger;
    }

    /**
     * It is responsible for verifying and loading the settings that are stored in the resources' folder of the plugin.
     */
    function verifySettings(): void
    {
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder() . "maps");
        $archive = self::ARCHIVE_STRING;

        foreach (['config.yml', 'duels-available.yml', 'scoreboard.yml', 'ffa-available.yml', 'server-info.yml', 'maps.yml'] as $dataCfg) $this->saveResource($dataCfg);

        $cfg = new Config($this->getDataFolder() . $archive, Config::YAML);

        /* This will verify that if the existing configuration file is not the same as the plugin version, it will be replaced. */
        if ($cfg->get('config.version') !== self::CONFIG_VER) {
            self::$logger->error("The version of the file $archive is not compatible with the current version of the plugin, the old configuration will be in /resources/{$this->getName()}");

            /* This replaces the file. */
            rename($this->getDataFolder() . 'config.yml', $this->getDataFolder() . 'config.yml.old');
            $this->saveResource($archive);
        }

        Settings::init(new Config($this->getDataFolder() . "config.yml", Config::YAML));

        /* I define the variable here below for reasons that if the configuration changes, the variable is updated. */
        Lang::$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

        foreach (Lang::$config->get("languages") as $language) {
            $iso = $language["ISOCode"];
            $this->saveResource("lang/$iso.yml");
            Lang::$lang[$iso] = new Config($this->getDataFolder() . "lang/$iso.yml");
            $this->getLogger()->notice("$iso has been loaded!");
        }
        self::$logger->notice("The configuration has been loaded successfully!");
    }
}