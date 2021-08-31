<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 31/8/2021
 *
 *  Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\gui;

use greek\modules\invmenu\InvMenu;
use greek\modules\invmenu\MenuIds;
use greek\network\player\NetworkPlayer;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class PartyMembersGui
{
    private InvMenu $menu;

    public function __construct(string $title)
    {
        $this->menu = InvMenu::create(MenuIds::TYPE_CHEST)
            ->setName($title)
            ->setListener(InvMenu::readonly());
    }

    public function addPlayerToGui(string $name): void
    {
        $item = ItemFactory::get(ItemIds::MOB_HEAD, 3)->setCustomName($name);
        $this->menu->getInventory()->addItem($item);
    }

    public function sendTo(NetworkPlayer $player): void
    {
        $this->menu->send($player);
    }
}