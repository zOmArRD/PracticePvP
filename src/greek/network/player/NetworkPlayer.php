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
use greek\network\config\Settings;
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

class NetworkPlayer extends Player
{
    /** @var Lang */
    public Lang $langSession;

    /** @var Session */
    public Session $session;

    /** @var Scoreboard  */
    public Scoreboard $scoreboardSession;

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
        $this->setGamemode(gm: GameMode::ADVENTURE);
        $this->setHealth(amount: 20);
        $this->setFood(new: 20);

        if (Server::getInstance()->isLevelGenerated(name: Settings::$lobby)) {
            $this->setRotation(yaw: Settings::$yaw, pitch: Settings::$pitch);
            $this->teleport(pos: new Position(x: Settings::$x, y: Settings::$y, z: Settings::$z, level: $this->getWorld(world: Settings::$lobby)));
        } else $this->teleport(pos: Server::getInstance()->getDefaultLevel()->getSafeSpawn());
    }

    /**
     * It is responsible for obtaining the world by entering the name.
     *
     * @param string $world
     * @return Level|null
     */
    public function getWorld(string $world): Level|null
    {
        return Loader::$instance->getServer()->getLevelByName(name: $world) ?? null;
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
        return TextUtils::replaceColor($langClass->getString(id: $idMsg));
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
        $pi->setItem(index: $index, item: $item);
    }

    /**
     * He is in charge of giving the Lobby items to the player.
     */
    public function giveLobbyItems(): void
    {
        $this->getInventory()->clearAll();
        foreach (["item.unranked" => 0, "item.ranked" => 1, "item.ffa" => 2, "item.party" => 4, "item.hostevent" => 6, "item.cosmetics" => 7, "item.settings" => 8] as $item => $index) {
            $this->setItem(index: $index, item: ItemsManager::get(itemId: $item, player: $this));
        }
    }

    public function getPartyItems(): void
    {
        $this->getInventory()->clearAll();
        foreach (['item.partyevent' => 0, 'item.partymember' => 7, 'item.disband' => 8] as $item => $index) {
            $this->setItem(index: $index, item: ItemsManager::get(itemId: $item, player: $this));
        }
    }
}