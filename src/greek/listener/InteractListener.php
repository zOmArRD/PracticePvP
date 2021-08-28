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
                case $item->equals(ItemsManager::get("item.settings", $player)):
                    new SettingsForm($player);
                    break;
                case $item->equals(ItemsManager::get("item.unranked", $player)):
                    new DuelsForm($player);
                    break;
                case $item->equals(ItemsManager::get("item.ranked", $player)):
                    new DuelsForm($player, true);
                    break;
                case $item->equals(ItemsManager::get("item.party", $player)):
                    $player->getPartyManager()->createParty();
                    break;
                case $item->equals(ItemsManager::get("item.ffa", $player)):
                    new FFAForm($player);
                    break;
                case $item->equals(ItemsManager::get("item.disband", $player)):
                    $player->getPartyManager()->disbandParty();
                    break;
                case $item->equals(ItemsManager::get("item.partymember", $player)):
                    $player->getPartyManager()->sendPartyMembersMsg();
                    break;
            }
            $this->itemCountDown[$player->getName()] = time();
        }
    }
}