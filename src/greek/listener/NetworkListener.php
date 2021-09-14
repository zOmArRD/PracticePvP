<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 27/8/2021
 *
 *  Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\listener;

use Closure;
use greek\Loader;
use greek\network\config\Settings;
use pocketmine\entity\Attribute;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;

class NetworkListener implements Listener
{
    /** @var Closure[][] */
    private array $callbacks = [];

    /** @var int */
    private int $times_to_request = 5;

    /**
     * @param Player $player
     * @param Closure $callback
     */
    private function onPacketSend(Player $player, Closure $callback): void
    {
        $ts = mt_rand() * 1000;
        $pk = new NetworkStackLatencyPacket();
        $pk->timestamp = $ts;
        $pk->needResponse = true;
        $player->sendDataPacket($pk);
        $this->callbacks[$player->getId()][$ts] = $callback;
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @priority MONITOR
     * @ignoreCancelled true
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        if ($packet instanceof NetworkStackLatencyPacket && isset($this->callbacks[$id = $event->getPlayer()->getId()][$ts = $packet->timestamp])) {
            $cb = $this->callbacks[$id][$ts];
            unset($this->callbacks[$id][$ts]);
            if (count($this->callbacks[$id]) === 0) {
                unset($this->callbacks[$id]);
            }
            $cb();
        }
    }

    /**
     * @param Player $player
     */
    private function requestUpdate(Player $player): void
    {
        $pk = new UpdateAttributesPacket();
        $pk->entityRuntimeId = $player->getId();
        $pk->entries[] = $player->getAttributeMap()->getAttribute(Attribute::EXPERIENCE_LEVEL);
        $player->sendDataPacket($pk);
    }

    /**
     * @param DataPacketSendEvent $event
     * @priority MONITOR
     * @ignoreCancelled true
     */
    public function onDataPacketSend(DataPacketSendEvent $event): void
    {
        if ($event->getPacket() instanceof ModalFormRequestPacket) {
            $player = $event->getPlayer();
            Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                if ($player->isOnline()) {
                    $this->onPacketSend($player, function () use ($player): void {
                        if ($player->isOnline()) {
                            $this->requestUpdate($player);
                            if ($this->times_to_request > 1) {
                                $times = $this->times_to_request - 1;
                                /** @var TaskHandler|null $handler */
                                $handler = null;
                                $handler = Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () use ($player, $times, &$handler): void {
                                    if (--$times >= 0 && $player->isOnline()) {
                                        $this->requestUpdate($player);
                                    } else {
                                        $handler->cancel();
                                        $handler = null;
                                    }
                                }), 20);
                            }
                        }
                    });
                }
            }), 1);
        }
    }

    /**
     * @param PlayerQuitEvent $event
     * @priority MONITOR
     */
    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        unset($this->callbacks[$event->getPlayer()->getId()]);
    }
}