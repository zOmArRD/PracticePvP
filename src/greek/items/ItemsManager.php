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
        return match ($itemId) {
            "item.unranked" => self::load(itemId: ItemIds::IRON_SWORD, customName: $player->getTranslatedMsg(idMsg: "item.unranked.name")),
            "item.ranked" => self::load(itemId: ItemIds::DIAMOND_SWORD, customName: $player->getTranslatedMsg(idMsg: "item.ranked.name")),
            "item.settings" => self::load(itemId: ItemIds::MOB_HEAD, customName: $player->getTranslatedMsg(idMsg: "item.settings.name")),
            "item.cosmetics" => self::load(itemId: BlockIds::ENDER_CHEST, customName: $player->getTranslatedMsg(idMsg: "item.cosmetics.name")),
            "item.ffa" => self::load(itemId: ItemIds::GOLD_AXE, customName: $player->getTranslatedMsg(idMsg: "item.ffa.name")),
            "item.party" => self::load(itemId: ItemIds::NAME_TAG, customName: $player->getTranslatedMsg(idMsg: "item.party.name")),
            "item.hostevent" => self::load(itemId: ItemIds::ENDER_EYE, customName: $player->getTranslatedMsg(idMsg: "item.hostevent.name")),
            "item.disband" => self::load(itemId: ItemIds::REDSTONE_DUST, customName: $player->getTranslatedMsg(idMsg: "item.disband.name")),
            "item.partyevent" => self::load(itemId: ItemIds::IRON_AXE, customName: $player->getTranslatedMsg(idMsg: "item.partyevent.name")),
            "item.partymember" => self::load(itemId: ItemIds::PAPER, customName: $player->getTranslatedMsg(idMsg: "item.partymember.name")),
            default => Item::get(id: BlockIds::AIR),
        };
    }

    /**
     * @param int $itemId
     * @param string $customName
     * @return Item
     */
    static public function load(int $itemId, string $customName): Item
    {
        return ItemFactory::get(id: $itemId)->setCustomName(name: $customName);
    }
}