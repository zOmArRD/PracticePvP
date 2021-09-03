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
use greek\modules\languages\Lang;
use greek\network\scoreboard\Scoreboard;
use greek\network\session\Session;
use greek\network\session\SessionFactory;
use greek\network\utils\TextUtils;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\Player;
use pocketmine\Server;
use const greek\SPAWN_OPTIONS;

class NetworkPlayer extends Player
{

    /** @var bool */
    public bool $isPerformanceViewer = false;

    /** @var Lang */
    public Lang $langSession;

    /** @var Session */
    public Session $session;

    /** @var Scoreboard */
    public Scoreboard $scoreboardSession;

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

    /**
     * This function is in charge of teleporting the player to the lobby, clearing his inventory, and giving him the items.
     */
    public function teleportToLobby(): void
    {
        $this->giveLobbyItems();
        $this->setGamemode(GameMode::ADVENTURE);
        $this->setHealth(20);
        $this->setFood(20);

        if (SPAWN_OPTIONS['enabled'] == true) {
            $spawn = SPAWN_OPTIONS;
            $this->teleport(new Position($spawn['x'], $spawn['y'], $spawn["z"], Server::getInstance()->getLevelByName($spawn['world.name'])), $spawn['yaw'], $spawn['pitch']);
        } else $this->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
    }

    /**
     * It is responsible for obtaining the world by entering the name.
     *
     * @param string $world
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
     * @return string
     */
    public function getTranslatedMsg(string $idMsg): string
    {
        $langClass = $this->getLangSession();
        return TextUtils::replaceColor($langClass->getString($idMsg));
    }

    public function sendTranslatedMsg(string $id): void
    {
        $this->sendMessage($this->getTranslatedMsg($id));
    }

    /**
     * This function is used to simplify the use of adding something to the player's inventory.
     *
     * @param int $index
     * @param Item $item
     */
    public function setItem(int $index, Item $item)
    {
        $pi = $this->getInventory();
        $pi->setItem($index, $item);
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
}