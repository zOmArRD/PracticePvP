<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 12/7/2021
 *
 * Copyright © 2021 Greek Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zOmArRD\core;

use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginLogger;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

final class Loader extends PluginBase
{
    /** @var string  */
    public const CONFIG_VER = "1.0";

    /** @var Loader  */
    public static Loader $instance;

    /** @var PluginLogger  */
    public static PluginLogger $logger;

    public function onLoad()
    {
        self::setInstance($this);
        self::setLogger($this->getLogger());
        $this->verifySettings();
    }

    public function onEnable()
    {
        $logger = $this->getLogger();


        $logger->info(TextFormat::GREEN . "Plugin enabled successfully");
    }

    public function onDisable()
    {
        $logger = $this->getLogger();


        $logger->info(TextFormat::GREEN . "Plugin disabled successfully!");
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

    public function verifySettings(): void
    {
        @mkdir($this->getDataFolder());

        foreach (['config.yml'] as $dataCfg) $this->saveResource($dataCfg);

        $cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($cfg->get('config-version') !== self::CONFIG_VER) {
            self::$logger->error("La version del archivo config.yml no es compatible con la version actual del plugin, la vieja configuracion estara en /resources/{$this->getName()}");

            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.yml.old");
            $this->saveResource("config.yml");
        }

        self::$logger->notice("The configuration has been loaded successfully!");
    }
}