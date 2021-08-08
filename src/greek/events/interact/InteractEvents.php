<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 6/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\events\interact;

use greek\duels\form\DuelsForm;
use greek\items\PluginItems;
use greek\network\config\SettingsForm;
use greek\network\player\NetworkPlayer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class InteractEvents implements Listener
{
    /** @var array */
    private array $itemCountDown;

    public function legacyInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $countdown = 1.5;

        if (!$player instanceof NetworkPlayer) return;

        if (!isset($this->itemCountDown[$player->getName()]) or time() - $this->itemCountDown[$player->getName()] >= $countdown) {
            switch (true) {
                case $item->equals(PluginItems::getItem("item.settings", $player)):
                    new SettingsForm($player);
                    break;
                case $item->equals(PluginItems::getItem("item.unranked", $player)):
                    DuelsForm::showDuelTypeForm($player);
                    break;
                case $item->equals(PluginItems::getItem("item.ranked", $player)):
                    DuelsForm::showDuelTypeForm($player, true);
                    break;
            }
            $this->itemCountDown[$player->getName()] = time();
        }
    }

}