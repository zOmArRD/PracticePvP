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
            case "item.unranked":
                return self::loadItem(ItemIds::IRON_SWORD, $player->getTranslatedMsg("item.unranked.name"));
            case "item.ranked":
                return self::loadItem(ItemIds::DIAMOND_SWORD, $player->getTranslatedMsg("item.ranked.name"));
            case "item.settings":
                return self::loadItem(ItemIds::BOOK, $player->getTranslatedMsg("item.settings.name"));
            case "item.cosmetics":
                return self::loadItem(BlockIds::ENDER_CHEST, $player->getTranslatedMsg("item.cosmetics.name"));
            case "item.ffa":
                return self::loadItem(ItemIds::GOLD_AXE, $player->getTranslatedMsg("item.ffa.name"));
            case "item.party":
                return self::loadItem(ItemIds::NAME_TAG, $player->getTranslatedMsg("item.party.name"));
            default:
                return Item::get(BlockIds::AIR);
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