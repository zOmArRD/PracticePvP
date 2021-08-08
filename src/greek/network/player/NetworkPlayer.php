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
use greek\network\NetworkSession;
use greek\network\utils\TextUtils;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class NetworkPlayer extends Player
{
    /** @var Lang */
    public Lang $langClass;

    /** @var NetworkSession */
    public NetworkSession $session;

    /** @var array  */
    public static array $data;


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


    public function setSession(): void
    {
        $this->session = new NetworkSession($this);
    }

    /**
     * @return NetworkSession
     */
    public function getSession(): NetworkSession
    {
        return $this->session;
    }

    /**
     * This function is in charge of teleporting the player to the lobby, clearing his inventory, and giving him the items.
     */
    public function teleportToLobby(): void
    {
        $this->getInventory()->clearAll();
        $this->giveLobbyItems();
        $this->setHealth(20);
        $this->setFood(20);

        try {
            if (Server::getInstance()->isLevelGenerated(Settings::$lobby)) {
                $this->setRotation(Settings::$yaw, Settings::$pitch);
                $this->teleport(new Position(Settings::$x, Settings::$y, Settings::$z, $this->getWorld(Settings::$lobby)));
            }
        } catch (Exception $exception) {
            $this->teleport(new Position($this->getServer()->getDefaultLevel()->getSafeSpawn()));
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
        return $pi->setItem($index, $item);
    }

    /**
     * He is in charge of giving the Lobby items to the player.
     */
    public function giveLobbyItems(): void
    {
        foreach (["item.unranked" => 0, "item.ranked" => 1, "item.cosmetics" => 7, "item.settings" => 8] as $item => $index) {
            $this->setItem($index, PluginItems::getItem($item, $this));
        }
    }
}