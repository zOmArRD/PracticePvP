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
use JetBrains\PhpStorm\NoReturn;
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

    #[NoReturn]
    public function onLoad(): void
    {
        self::setInstance($this);
        self::setLogger($this->getLogger());

        $this->verifySettings();

        /* Check in the database if the necessary Practice tables have been created. */
        AsyncQueue::submitQuery(new InsertQuery(sqlQuery: "CREATE TABLE IF NOT EXISTS settings(ign TEXT UNIQUE, language TEXT, ShowScoreboard INT)"));
        AsyncQueue::submitQuery(new InsertQuery(sqlQuery: "CREATE TABLE IF NOT EXISTS practice_downstream(ign TEXT UNIQUE, DuelType TEXT, QueueKit TEXT, isInviteDuel bool, playerInvited TEXT)"));
        AsyncQueue::submitQuery(new InsertQuery(sqlQuery: "CREATE TABLE IF NOT EXISTS ffa_data(ign TEXT, mode TEXT)"));
    }

    #[NoReturn]
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

        self::$logger->info(message: Settings::$prefix . "§a" . "plugin loaded");
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
        $archive = self::ARCHIVE_STRING;

        foreach (['config.yml', 'scoreboard.yml', 'network.data.yml'] as $dataCfg) $this->saveResource(filename: $dataCfg);

        $cfg = new Config(file: $this->getDataFolder() . $archive, type: Config::YAML);

        /* This will verify that if the existing configuration file is not the same as the plugin version, it will be replaced. */
        if ($cfg->get(k: 'config.version') !== self::CONFIG_VER) {
            self::$logger->error(message: "The version of the file $archive is not compatible with the current version of the plugin, the old configuration will be in /resources/{$this->getName()}");

            /* This replaces the file. */
            rename(from: $this->getDataFolder() . 'config.yml', to: $this->getDataFolder() . 'config.yml.old');
            $this->saveResource(filename: $archive);
        }

        Settings::init(new Config(file: $this->getDataFolder() . "config.yml", type: Config::YAML));

        /* I define the variable here below for reasons that if the configuration changes, the variable is updated. */
        Lang::$config = new Config(file: $this->getDataFolder() . "config.yml", type: Config::YAML);

        foreach (Lang::$config->get(k: "languages") as $language) {
            $iso = $language["ISOCode"];
            $this->saveResource(filename: "lang/$iso.yml");
            Lang::$lang[$iso] = new Config(file: $this->getDataFolder() . "lang/$iso.yml");
            $this->getLogger()->notice(message: "$iso has been loaded!");
        }
        self::$logger->notice(message: "The configuration has been loaded successfully!");
    }
}