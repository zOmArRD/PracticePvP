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

class ItemsManager
{
    /**
     * @param string $itemId
     * @param NetworkPlayer $player
     * @return Item
     */
    static public function get(string $itemId, NetworkPlayer $player): Item
    {
        switch ($itemId) {
            case "item.unranked":
                return self::load(ItemIds::IRON_SWORD, $player->getTranslatedMsg("item.unranked.name"));
            case "item.ranked":
                return self::load(ItemIds::DIAMOND_SWORD, $player->getTranslatedMsg("item.ranked.name"));
            case "item.settings":
                return self::load(ItemIds::MOB_HEAD, $player->getTranslatedMsg("item.settings.name"));
            case "item.cosmetics":
                return self::load(BlockIds::ENDER_CHEST, $player->getTranslatedMsg("item.cosmetics.name"));
            case "item.ffa":
                return self::load(ItemIds::GOLD_AXE, $player->getTranslatedMsg("item.ffa.name"));
            case "item.party":
                return self::load(ItemIds::NAME_TAG, $player->getTranslatedMsg("item.party.name"));
            case "item.hostevent":
                return self::load(ItemIds::ENDER_EYE, $player->getTranslatedMsg("item.hostevent.name"));
            case "item.disband":
                return self::load(ItemIds::REDSTONE_DUST, $player->getTranslatedMsg("item.disband.name"));
            case "item.partyevent":
                return self::load(ItemIds::IRON_AXE, $player->getTranslatedMsg("item.partyevent.name"));
            case "item.partymember":
                return self::load(ItemIds::PAPER, $player->getTranslatedMsg("item.partymember.name"));
            /*case "":
                return self::load(ItemIds::IRON_AXE, $player->getTranslatedMsg(""));*/
            default:
                return Item::get(BlockIds::AIR);
        }
    }

    /**
     * @param int $itemId
     * @param string $customName
     * @return Item
     */
    static public function load(int $itemId, string $customName): Item
    {
        return ItemFactory::get($itemId)->setCustomName($customName);
    }
}