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

use Closure;
use greek\modules\invmenu\InvMenu;
use greek\modules\invmenu\MenuIds;
use pocketmine\item\Item;

class PartyMembersGui
{
    private InvMenu $menu;

    public function __construct(string $title)
    {
        $this->menu = InvMenu::create(MenuIds::TYPE_CHEST)
            ->setName($title)
            ->setListener(InvMenu::readonly(Closure::fromCallable([$this, "onTransaction"])));
    }

    public function addPlayersToGui(Item $item, string $playerName): void
    {
        $nbt = $item->getNamedTag();
        $nbt->setString("Player", $playerName);
        $item->setNamedTag($nbt);
        $item->setCustomName($playerName);

    }
}