<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 20/7/2021
 *
 * Copyright © 2021 Greek Network - All Rights Reserved.
 */

namespace greek\gui;

use greek\modules\invmenu\InvMenu;
use greek\modules\invmenu\MenuIds;
use greek\network\player\NetworkPlayer;

abstract class BaseGui
{
    /** @var InvMenu */
    private InvMenu $menu;

    public function __construct(string $name = "Greek Network", string $identifier = MenuIds::TYPE_CHEST)
    {
        $this->menu = InvMenu::create($identifier)
            ->setName($name);
    }

    /**
     * @return InvMenu
     */
    public function getMenu(): InvMenu
    {
        return $this->menu;
    }

    /**
     * Do the Listener methods here.
     */
    abstract public function onTransaction(): void;

    /**
     * It is responsible for sending the inventory to the player.
     *
     * @param NetworkPlayer $player
     */
    public function sendTo(NetworkPlayer $player): void
    {
        $this->getMenu()->send($player);
    }
}