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
use greek\items\PluginItems;
use greek\Loader;
use greek\modules\languages\Lang;
use greek\network\config\Settings;
use greek\network\utils\TextUtils;
use pocketmine\item\Item;
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

    public function teleportToLobby(): void
    {
        $this->giveLobbyItems();
        $this->setHealth(20);
        $this->setFood(20);

        try {
            $this->setRotation(Settings::$yaw, Settings::$pitch);
            $this->teleport(new Position(Settings::$x, Settings::$y, Settings::$z, $this->getWorld(Settings::$lobby)));
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
     * Transfer the player to another server (Must be proxied.)
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

    /**
     * This function is used to simplify the use of adding something to the player's inventory.
     * @param int $index
     * @param Item $item
     * @return bool
     */
    public function setItem(int $index, Item $item): bool
    {
        $pi = $this->getInventory();
        $pi->clearAll();
        return $pi->setItem($index, $item);
    }

    /**
     * He is in charge of giving the Lobby items to the player.
     */
    public function giveLobbyItems(): void
    {
        foreach (['selector.duel' => 0, 'selector.ffa' => 1] as $item => $index) {
            $this->setItem($index, PluginItems::getItem($item, $this));
        }
    }
}