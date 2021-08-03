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

namespace greek\network\player;

use Exception;
use greek\Loader;
use greek\modules\languages\Lang;
use greek\network\config\Settings;
use greek\network\utils\TextUtils;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\Player;

class NetworkPlayer extends Player
{
    /** @var array */
    public static array $playerData = [];

    public Lang $langClass;

    public function setLangClass(): void
    {
        $this->langClass = new Lang($this);
    }

    /**
     * @return Lang
     */
    public function getLangClass(): Lang
    {
        return $this->langClass;
    }

    public function handleFirstJoin(): void
    {
        $playerInventory = $this->getInventory();
        $playerInventory->clearAll();

        $this->setHealth(20);
        $this->setFood(20);

        try {
            if (Settings::$pitch || Settings::$yaw !== null) $this->setRotation(Settings::$yaw, Settings::$pitch);
            if (Settings::$x || Settings::$y || Settings::$z || Settings::$lobby !== null) $this->teleport(new Position(Settings::$x, Settings::$y, Settings::$z, $this->getWorld(Settings::$lobby)));

        } catch (Exception $exception) {
            var_dump($exception->getMessage());
        }
    }

    /**
     * @param string $world
     * @return Level
     */
    public function getWorld(string $world): Level
    {
        return Loader::$instance->getServer()->getLevelByName($world);
    }

    /**
     * It is responsible for returning the translated message and with the colors.
     *
     * @param string $idMsg
     * @return string
     */
    public function getTranslatedMsg(string $idMsg): string
    {
        $langClass = $this->getLangClass();
        return TextUtils::replaceColor($langClass->getString($idMsg));
    }

    /**
     * Transfiere al jugador a otro servidor (Debe estar proxieado.)
     *
     *
     * @param string $server
     */
    public function networkTransfer(string $server): void
    {
        $this->sendMessage($this->getTranslatedMsg("message.server.connecting"));
        $pk = new TransferPacket();
        $pk->address = $server;
        $this->dataPacket($pk);
    }
}