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
use greek\items\ItemsManager;
use greek\Loader;
use greek\modules\languages\Lang;
use greek\network\config\Settings;
use greek\network\scoreboard\Scoreboard;
use greek\network\Session;
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

    /** @var array */
    public static array $data;

    /** @var bool */
    protected bool $partyMode = false;

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
     * Establishes the session of the player.
     */
    public function setSession(): void
    {
        $this->session = new Session($this);
    }

    /**
     * The player's session returns.
     *
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;
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
     * Puts the player in Party mode.
     *
     * @param bool $partyMode
     */
    public function setPartyMode(bool $partyMode): void
    {
        $this->partyMode = $partyMode;
    }

    /**
     * Returns a (bool) depending on whether the player is in party mode.
     *
     * @return bool
     */
    public function isPartyMode(): bool
    {
        return $this->partyMode;
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

        if (Server::getInstance()->isLevelGenerated(Settings::$lobby)) {
            $this->setRotation(Settings::$yaw, Settings::$pitch);
            $this->teleport(new Position(Settings::$x, Settings::$y, Settings::$z, $this->getWorld(Settings::$lobby)));
        } else $this->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
    }

    /**
     * It is responsible for obtaining the world by entering the name.
     *
     * @param string $world
     * @return Level|null
     */
    public function getWorld(string $world): ?Level
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
        $this->getInventory()->clearAll();
        foreach (["item.unranked" => 0, "item.ranked" => 1, "item.ffa" => 2, "item.party" => 4, "item.hostevent" => 6, "item.cosmetics" => 7, "item.settings" => 8] as $item => $index) {
            $this->setItem($index, ItemsManager::get($item, $this));
        }
    }

    public function getPartyItems(): void
    {
        $this->getInventory()->clearAll();
        foreach (['item.partyevent' => 0, 'item.partymember' => 7, 'item.disband' => 8] as $item => $index) {
            $this->setItem($index, ItemsManager::get($item, $this));
        }
    }
}