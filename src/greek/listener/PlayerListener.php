<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 2/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\listener;

use greek\modules\database\mysql\AsyncQueue;
use greek\modules\database\mysql\query\InsertQuery;
use greek\modules\database\mysql\query\SelectQuery;
use greek\network\config\Settings;
use greek\network\player\NetworkPlayer;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\EmotePacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Server;
use ReflectionClass;
use ReflectionException;

class PlayerListener implements Listener
{
    /** @var array */
    public array $login, $join, $move;

    /**
     * @throws ReflectionException
     */
    public function dataReceive(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        $player = $event->getPlayer();

        switch (true) {
            case $packet instanceof LoginPacket:
                if (isset($packet->clientData["Waterdog_IP"])) {
                    $class = new ReflectionClass($player);
                    $property = $class->getProperty(name: "ip");
                    $property->setAccessible(accessible: true);
                    $property->setValue($player, $packet->clientData["Waterdog_IP"]);
                }

                if (isset($packet->clientData["Waterdog_XUID"])) {
                    $class = new ReflectionClass($player);

                    $property = $class->getProperty(name:"xuid");
                    $property->setAccessible(accessible: true);
                    $property->setValue(objectOrValue:  $player, value: $packet->clientData["Waterdog_XUID"]);
                    $packet->xuid = $packet->clientData["Waterdog_XUID"];
                }
                break;
            case $packet instanceof EmotePacket:
                $emoteId = $packet->getEmoteId();
                Server::getInstance()->broadcastPacket(players: $player->getViewers(), packet: EmotePacket::create(entityRuntimeId: $player->getId(), emoteId: $emoteId, flags: 1 << 0));
                break;
        }
    }

    public function playerCreation(PlayerCreationEvent $event): void
    {
        $event->setPlayerClass(class: NetworkPlayer::class);
    }

    public function onPreLogin(PlayerPreLoginEvent $event): void
    {
        $player = $event->getPlayer();

        if (!$player instanceof NetworkPlayer) return;

        $name = $player->getName();
        $player->setLangSession();
        $player->setSession();
        $player->setScoreboardSession();

        AsyncQueue::submitQuery(new SelectQuery(sqlQuery: "SELECT * FROM settings WHERE ign='$name'"), function ($result, $data) {
            $player = $data[0];
            $name = $player->getName();
            $lang = "en_ENG";

            if (sizeof($result) === 0) {
                AsyncQueue::submitQuery(new InsertQuery(sqlQuery: "INSERT INTO settings(ign, language, ShowScoreboard) VALUES ('$name', '$lang', 1);"));
            }
        }, [$player]);

        AsyncQueue::submitQuery(new SelectQuery(sqlQuery: "SELECT * FROM practice_downstream WHERE ign='$name'"), function ($result, $data) {
            $player = $data[0];
            $name = $player->getName();

            if (sizeof($result) === 0) {
                AsyncQueue::submitQuery(new InsertQuery(sqlQuery: "INSERT INTO practice_downstream(ign) VALUES ('$name');"));
            }
        }, [$player]);
    }

    public function onLogin(PlayerLoginEvent $event)
    {
        $player = $event->getPlayer();

        if (!$player instanceof NetworkPlayer) return;

        $name = $player->getName();

        AsyncQueue::submitQuery(new SelectQuery(sqlQuery: "SELECT * FROM settings WHERE ign='$name'"), function ($result, $data) {
            $player = $data[0];
            $name = $player->getName();

            NetworkPlayer::$data[$name] = $result[0];
            $this->updateLang(player: $player);
        }, [$player]);

        $this->login[$name] = 1;
    }

    public function updateLang(NetworkPlayer $player): void
    {
        $player->getLangSession()->applyLanguage();
    }

    public function pJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();

        $event->setJoinMessage(joinMessage: null);
        $player->setImmobile();

        if (!$player instanceof NetworkPlayer) return;

        if (isset($this->login[$name])) {
            unset($this->login[$name]);
            $this->join[$name] = 1;
        }

        $player->getSession()->updateSession();
    }

    public function pQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $player->getInventory()->clearAll();
        if ($player instanceof NetworkPlayer) $player->getSession()->closeSession();
        $event->setQuitMessage(quitMessage: null);
    }

    public function handleExhaust(PlayerExhaustEvent $event)
    {
        $event->setCancelled();
    }

    public function pMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $name = $player->getName();

        if (isset($this->login[$name]) || isset($this->move[$name])) {
            $event->setCancelled();
        }

        if (isset($this->join[$name])) {
            unset($this->join[$name]);
            $this->move[$player->getName()] = 1;
            return;
        }

        if (isset($this->move[$name])) {
            $player->setImmobile(value: false);
            unset($this->move[$name]);
        }
    }

    public function slotChange(InventoryTransactionEvent $event): void
    {
        $entity = $event->getTransaction()->getSource();

        if ($entity->getLevel()->getName() === Settings::$lobby || $entity->getLevel()->getName() === Server::getInstance()->getDefaultLevel()->getName()) {
            if (!$entity->isOp()) {
                $event->setCancelled();
            }
        }
    }

    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player->getLevel()->getName() === Settings::$lobby || $player->getLevel()->getName() === Server::getInstance()->getDefaultLevel()->getName()) {
            if (!$player->isOp()) {
                $event->setCancelled();
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player->getLevel()->getName() === Settings::$lobby || $player->getLevel()->getName() === Server::getInstance()->getDefaultLevel()->getName()) {
            if (!$player->isOp()) {
                $event->setCancelled();
            }
        }
    }
}