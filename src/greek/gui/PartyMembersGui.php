<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 20/7/2021
 *
 * Copyright © 2021 Greek Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\gui;

use greek\modules\invmenu\InvMenu;
use greek\modules\invmenu\MenuIds;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class PartyMembersGui extends BaseGui
{
    public function __construct(string $name = "Greek Network", string $identifier = MenuIds::TYPE_CHEST)
    {
        parent::__construct($name, $identifier);
        $this->onTransaction();
    }

    public function onTransaction(): void
    {
        $menu = $this->getMenu();

        $menu->setListener(InvMenu::readonly());
    }

    public function addPlayerToGui(string $name): void
    {
        $item = ItemFactory::get(ItemIds::MOB_HEAD, 3)->setCustomName($name);
        $this->getMenu()->getInventory()->addItem($item);
    }
}