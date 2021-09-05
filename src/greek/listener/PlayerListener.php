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
use greek\network\player\NetworkPlayer;
use greek\network\session\Session;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\EmotePacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Server;
use ReflectionClass;
use ReflectionException;
use const greek\SPAWN_OPTIONS;

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
                    $property = $class->getProperty("ip");
                    $property->setAccessible(true);
                    $property->setValue($player, $packet->clientData["Waterdog_IP"]);
                }

                if (isset($packet->clientData["Waterdog_XUID"])) {
                    $class = new ReflectionClass($player);

                    $property = $class->getProperty("xuid");
                    $property->setAccessible(true);
                    $property->setValue($player, $packet->clientData["Waterdog_XUID"]);
                    $packet->xuid = $packet->clientData["Waterdog_XUID"];
                }
                break;
            case $packet instanceof EmotePacket:
                $emoteId = $packet->getEmoteId();
                Server::getInstance()->broadcastPacket($player->getViewers(), EmotePacket::create($player->getId(), $emoteId, 1 << 0));
                break;
        }
    }

    public function playerCreation(PlayerCreationEvent $event): void
    {
        $event->setPlayerClass(NetworkPlayer::class);
    }

    public function onPreLogin(PlayerPreLoginEvent $event): void
    {
        $player = $event->getPlayer();

        if (!$player instanceof NetworkPlayer) return;

        $name = $player->getName();
        $player->setLangSession();
        $player->setScoreboardSession();

        AsyncQueue::submitQuery(new SelectQuery("SELECT * FROM settings WHERE ign='$name'"), function ($result, $data) {
            $player = $data[0];
            $name = $player->getName();
            $lang = "en_ENG";

            if (sizeof($result) === 0) {
                AsyncQueue::submitQuery(new InsertQuery("INSERT INTO settings(ign, language, ShowScoreboard) VALUES ('$name', '$lang', 1);"));
            }
        }, [$player]);

        AsyncQueue::submitQuery(new SelectQuery("SELECT * FROM practice_downstream WHERE ign='$name'"), function ($result, $data) {
            $player = $data[0];
            $name = $player->getName();

            if (sizeof($result) === 0) {
                AsyncQueue::submitQuery(new InsertQuery("INSERT INTO practice_downstream(ign) VALUES ('$name');"));
            }
        }, [$player]);
    }

    public function onLogin(PlayerLoginEvent $event)
    {
        $player = $event->getPlayer();

        if (!$player instanceof NetworkPlayer) return;

        $name = $player->getName();

        AsyncQueue::submitQuery(new SelectQuery("SELECT * FROM settings WHERE ign='$name'"), function ($result, $data) {
            $player = $data[0];
            $name = $player->getName();

            Session::$data[$name] = $result[0];
            $this->updateLang($player);
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

        $event->setJoinMessage(null);
        $player->setImmobile();
        if (!$player instanceof NetworkPlayer) return;

        $player->teleportToLobby();

        if (isset($this->login[$name])) {
            unset($this->login[$name]);
            $this->join[$name] = 1;
        }

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
            $player->setImmobile(false);
            unset($this->move[$name]);
        }

        if ($player->getY() <= SPAWN_OPTIONS["min.void"]) {
            if (SPAWN_OPTIONS['enabled'] == true) {
                $spawn = SPAWN_OPTIONS;
                $player->teleport(new Position($spawn['x'], $spawn['y'], $spawn["z"], Server::getInstance()->getLevelByName($spawn['world.name'])), $spawn['yaw'], $spawn['pitch']);
            } else $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
        }
    }

    public function slotChange(InventoryTransactionEvent $event): void
    {
        $entity = $event->getTransaction()->getSource();

        if ($entity->getLevel()->getName() === SPAWN_OPTIONS['world.name'] || $entity->getLevel()->getName() === Server::getInstance()->getDefaultLevel()->getName()) {
            if (!$entity->isOp()) {
                $event->setCancelled();
            }
        }
    }

    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player->getLevel()->getName() === SPAWN_OPTIONS['world.name'] || $player->getLevel()->getName() === Server::getInstance()->getDefaultLevel()->getName()) {
            if (!$player->isOp()) {
                $event->setCancelled();
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player->getLevel()->getName() === SPAWN_OPTIONS['world.name'] || $player->getLevel()->getName() === Server::getInstance()->getDefaultLevel()->getName()) {
            if (!$player->isOp()) {
                $event->setCancelled();
            }
        }
    }

    public function onDmg(EntityDamageEvent $event): void
    {
        $event->setCancelled();
    }
}