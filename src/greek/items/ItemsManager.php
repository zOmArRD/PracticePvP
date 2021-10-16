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

final class ItemsManager
{
    /**
     * @param string        $itemId
     * @param NetworkPlayer $player
     *
     * @return Item
     */
    static public function get(string $itemId, NetworkPlayer $player): Item
    {
        return match ($itemId) {
            "item.unranked" => self::load(ItemIds::IRON_SWORD, $player->getTranslatedMsg("item.unranked.name")),
            "item.ranked" => self::load(ItemIds::DIAMOND_SWORD, $player->getTranslatedMsg("item.ranked.name")),
            "item.settings" => self::load(ItemIds::MOB_HEAD, $player->getTranslatedMsg("item.settings.name")),
            "item.cosmetics" => self::load(BlockIds::ENDER_CHEST, $player->getTranslatedMsg("item.cosmetics.name")),
            "item.ffa" => self::load(ItemIds::GOLD_AXE, $player->getTranslatedMsg("item.ffa.name")),
            "item.party" => self::load(ItemIds::NAME_TAG, $player->getTranslatedMsg("item.party.name")),
            "item.hostevent" => self::load(ItemIds::ENDER_EYE, $player->getTranslatedMsg("item.hostevent.name")),
            "item.disband" => self::load(ItemIds::NETHERSTAR, $player->getTranslatedMsg("item.disband.name")),
            "item.partyevent" => self::load(ItemIds::IRON_AXE, $player->getTranslatedMsg("item.partyevent.name")),
            "item.partymember" => self::load(ItemIds::PAPER, $player->getTranslatedMsg("item.partymember.name")),
            default => Item::get(BlockIds::AIR),
        };
    }

    /**
     * @param int    $itemId
     * @param string $customName
     *
     * @return Item
     */
    static public function load(int $itemId, string $customName): Item
    {
        return ItemFactory::get($itemId)->setCustomName($customName);
    }
}