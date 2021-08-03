<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 3/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\items;

use greek\network\player\NetworkPlayer;
use pocketmine\block\BlockIds;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class PluginItems
{
    /**
     * @param string $itemId
     * @param NetworkPlayer $player
     * @return Item
     */
    static public function getItem(string $itemId, NetworkPlayer $player): Item
    {
        switch ($itemId) {
            case "selector.duel":
                return self::loadItem(ItemIds::DIAMOND_SWORD, $player->getTranslatedMsg("item.selectorduel.name"));
            case "selector.ffa":
                return self::loadItem(ItemIds::DIAMOND_SWORD, $player->getTranslatedMsg("item.selectorffa.name"));
                /* TODO: Add more items. */
            default:
                return ItemFactory::get(BlockIds::AIR);
        }
    }

    /**
     * @param int $itemId
     * @param string $customName
     * @return Item
     */
    static public function loadItem(int $itemId, string $customName): Item
    {
        return ItemFactory::get($itemId)->setCustomName($customName);
    }
}