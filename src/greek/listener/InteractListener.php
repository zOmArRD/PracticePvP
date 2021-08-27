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

use greek\duels\form\DuelsForm;
use greek\duels\form\FFAForm;
use greek\items\ItemsManager;
use greek\network\config\SettingsForm;
use greek\network\player\NetworkPlayer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class InteractListener implements Listener
{
    /** @var array */
    private array $itemCountDown;

    /**
     * @param PlayerInteractEvent $event
     *
     * @todo End the event for each item.
     */
    public function legacyInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $countdown = 1.5;

        if (!$player instanceof NetworkPlayer) return;

        if (!isset($this->itemCountDown[$player->getName()]) or time() - $this->itemCountDown[$player->getName()] >= $countdown) {
            switch (true) {
                case $item->equals(item: ItemsManager::get(itemId: "item.settings", player: $player)):
                    new SettingsForm(player: $player);
                    break;
                case $item->equals(item: ItemsManager::get(itemId: "item.unranked", player: $player)):
                    new DuelsForm(player: $player);
                    break;
                case $item->equals(item: ItemsManager::get(itemId: "item.ranked", player: $player)):
                    new DuelsForm(player: $player, isRanked: true);
                    break;
                case $item->equals(item: ItemsManager::get(itemId: "item.party", player: $player)):
                    $player->getSession()->setPartyMode();
                    break;
                case $item->equals(item: ItemsManager::get(itemId: "item.ffa", player: $player)):
                    new FFAForm(player: $player);
                    break;
                    case $item->equals(item: ItemsManager::get(itemId: "item.disband", player: $player));
                    $player->getSession()->unSetPartyMode();
                    break;
            }
            $this->itemCountDown[$player->getName()] = time();
        }
    }
}