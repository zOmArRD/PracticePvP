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
use greek\network\session\SessionFactory;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

final class PartyMembersGui extends BaseGui
{
    /**
     * @param string $name
     * @param string $identifier
     */
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

    /**
     * Add a head to the menu with the player's name.
     * @param string $name
     */
    public function addPlayerToGui(string $name): void
    {
        $session = SessionFactory::getSessionByName($name);
        $item = ItemFactory::get(ItemIds::MOB_HEAD, 3);

        if ($session->getParty()->getLeaderName() == $name) {
            $item->setCustomName("§7[§cLeader§7] §a$name");
        } else {
            $item->setCustomName("§7[§aMember§7] §a$name");
        }

        $this->getMenu()->getInventory()->addItem($item);
    }
}