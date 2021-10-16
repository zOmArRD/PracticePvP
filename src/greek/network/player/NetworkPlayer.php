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

use greek\items\ItemsManager;
use greek\Loader;
use greek\manager\PartyManager;
use greek\modules\cosmetics\MCosmetic;
use greek\modules\languages\Lang;
use greek\network\Network;
use greek\network\scoreboard\Scoreboard;
use greek\network\server\ServerManager;
use greek\network\session\Session;
use greek\network\session\SessionFactory;
use greek\network\utils\TextUtils;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\SetTimePacket;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\Player;
use pocketmine\Server;
use const greek\PREFIX;
use const greek\SPAWN_OPTIONS;

class NetworkPlayer extends Player
{

    /** @var bool */
    public bool $isPerformanceViewer = false, $isQueue = false;

    /** @var array|null */
    public ?array $queueData;

    /** @var Lang */
    public Lang $langSession;

    /** @var Session */
    public Session $session;

    /** @var Scoreboard */
    public Scoreboard $scoreboardSession;

    /** @var MCosmetic */
    public MCosmetic $MCosmetic;

    public function isQueue(): bool
    {
        return $this->isQueue;
    }

    /**
     * @param string $duelKit
     * @param string $type
     * @param bool   $isQueue
     */
    public function setIsQueue(bool $isQueue = true, string $duelKit = "", string $type = ""): void
    {
        if (!$isQueue) {
            $this->isQueue = false;
            return;
        }
        $this->queueData["kit"] = $duelKit;
        $this->queueData["type"] = $type;
        $this->isQueue = $isQueue;
    }

    /**
     * @param bool $isPerformanceViewer
     */
    public function setIsPerformanceViewer(bool $isPerformanceViewer): void
    {
        $this->isPerformanceViewer = $isPerformanceViewer;
    }

    /**
     * @return bool
     */
    public function isPerformanceViewer(): bool
    {
        return $this->isPerformanceViewer;
    }

    /**
     * Sets the Language Session to the player.
     */
    public function setLangSession(): void
    {
        $this->langSession = new Lang($this);
    }

    /**
     * Returns the session of the player's Lang class.
     *
     * @return Lang
     */
    public function getLangSession(): Lang
    {
        return $this->langSession;
    }

    /**
     * Establishes the Scoreboard Session of the player.
     */
    public function setScoreboardSession(): void
    {
        $this->scoreboardSession = new Scoreboard($this);
    }

    /**
     * @return Scoreboard
     */
    public function getScoreboardSession(): Scoreboard
    {
        return $this->scoreboardSession;
    }

    /**
     * @return PartyManager
     */
    public function getPartyManager(): PartyManager
    {
        return new PartyManager(SessionFactory::getSession($this));
    }

    public function setMCosmetic(): void
    {
        $this->MCosmetic = new MCosmetic($this);
    }

    /**
     * @return MCosmetic
     */
    public function getMCosmetic(): MCosmetic
    {
        return $this->MCosmetic;
    }

    /**
     * This function is in charge of teleporting the player to the lobby, clearing his inventory, and giving him the items.
     */
    public function teleportToLobby(): void
    {
        $this->giveLobbyItems();
        $this->setGamemode(GameMode::ADVENTURE);
        $this->setHealth(20);
        $this->setFood(20);

        if (SPAWN_OPTIONS['enabled']) {
            $spawn = SPAWN_OPTIONS;
            $this->teleport(new Position($spawn['x'], $spawn['y'], $spawn["z"], Server::getInstance()->getLevelByName($spawn['world.name'])), $spawn['yaw'], $spawn['pitch']);
        } else {
            $this->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
        }
    }

    /**
     * It is responsible for obtaining the world by entering the name.
     *
     * @param string $world
     *
     * @return Level|null
     */
    public function getWorld(string $world): Level|null
    {
        return Loader::$instance->getServer()->getLevelByName($world) ?? null;
    }

    /**
     * It is responsible for returning the translated message and with the colors.
     *
     * @param string $idMsg
     *
     * @return string
     */
    public function getTranslatedMsg(string $idMsg): string
    {
        $langClass = $this->getLangSession();
        return (new Network())->getTextUtils()->replaceColor($langClass->getString($idMsg));
    }

    /**
     * @param string $id
     */
    public function sendTranslatedMsg(string $id): void
    {
        $this->sendMessage($this->getTranslatedMsg($id));
    }

    /**
     * This function is used to simplify the use of adding something to the player's inventory.
     *
     * @param int  $index
     * @param Item $item
     */
    public function setItem(int $index, Item $item)
    {
        $pi = $this->getInventory();
        if (isset($pi)) {
            $pi->setItem($index, $item);
        }
    }

    /**
     * He is in charge of giving the Lobby items to the player.
     */
    public function giveLobbyItems(): void
    {
        $inventory = $this->getInventory();
        if (isset($inventory)) {
            $inventory->clearAll();
        }
        foreach (["item.unranked" => 0, "item.ranked" => 1, "item.ffa" => 2, "item.party" => 4, "item.hostevent" => 6, "item.cosmetics" => 7, "item.settings" => 8] as $item => $index) {
            $this->setItem($index, ItemsManager::get($item, $this));
        }
    }

    public function getPartyItems(): void
    {
        $inventory = $this->getInventory();
        if (isset($inventory)) {
            $inventory->clearAll();
        }
        foreach (['item.partyevent' => 0, 'item.partymember' => 7, 'item.disband' => 8] as $item => $index) {
            $this->setItem($index, ItemsManager::get($item, $this));
        }
    }

    public function handleLevelSoundEvent(LevelSoundEventPacket $packet): bool
    {
        return true;
    }

    /**
     * Changes the time of the world where the player is (Only applies to this player)
     *
     * @param int $time
     */
    public function changeTime(int $time)
    {
        $player = $this;
        $pk = new SetTimePacket();
        $pk->time = $time & 0xffffffff;
        $player->dataPacket($pk);
//        if ($player instanceof Player) {
//            $this->getServer()->broadcastPacket([$player], $pk);
//        }
    }

    /**
     * @todo check if the down-stream has arenas available for transfer.
     *
     * @param string $serverTarget
     */
    public function transferServer(string $serverTarget)
    {
        $servers = ServerManager::getServers();
        if (count($servers) <= 0) {
            $this->setIsQueue(false);
            $this->sendMessage(PREFIX . (new Network())->getTextUtils()->replaceColor("{red}Could not connect to this server!") . " [error n01]");
            return;
        }
        foreach ($servers as $server) {
            if ($server->getServerName() == $serverTarget) {
                if ($server->isOnline()) {
                    if (!$server->isWhitelisted()) {
                        /*$pk = new TransferPacket();
                        $pk->address = $server->getServerName();
                        $this->directDataPacket($pk);*/
                        $this->sendMessage(PREFIX . $this->getTranslatedMsg("message.server.connecting"));
                    } else {
                        $this->sendMessage(PREFIX . (new Network())->getTextUtils()->replaceColor("{red}The server is under maintenance"));
                    }
                } else {
                    $this->sendMessage(PREFIX . (new Network())->getTextUtils()->replaceColor("{red}The server is offline!"));
                }
            } else {
                $this->sendMessage(PREFIX . (new Network())->getTextUtils()->replaceColor("{red}Could not connect to this server!") . " [error n02]");
            }
        }
        $this->setIsQueue(false);
    }
}