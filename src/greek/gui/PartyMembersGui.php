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

use greek\modules\invmenu\inventory\InvMenuInventory;
use greek\modules\invmenu\InvMenu;
use greek\modules\invmenu\MenuIds;
use greek\modules\invmenu\transaction\DeterministicInvMenuTransaction;
use greek\modules\invmenu\transaction\InvMenuTransaction;
use greek\modules\invmenu\transaction\InvMenuTransactionResult;
use greek\network\player\NetworkPlayer;
use greek\network\session\SessionFactory;
use pocketmine\inventory\Inventory;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class PartyMembersGui extends BaseGui
{
    private ?string $target = null;

    public function __construct(string $name = "Greek Network", string $identifier = MenuIds::TYPE_CHEST)
    {
        parent::__construct($name, $identifier);
        $this->onTransaction();
    }

    public function onTransaction(): void
    {
        $menu = $this->getMenu();

        $menu->setListener(InvMenu::readonly());

        /*$menu->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction): void {
            $item = $transaction->getItemClicked();
            $itemName = $item->getName();
            $playerName = str_replace("§7[§cLeader§7] §a", "", $itemName);
            $this->target = $playerName;
            var_dump($this->target);
            $transaction->then(function (NetworkPlayer $player): void {
                $player->getPartyManager()->openPartyPlayerForm($this->target);
            });
        }));

        $menu->setInventoryCloseListener(function (NetworkPlayer $player) {
            if ($this->target !== null) $player->getPartyManager()->openPartyPlayerForm($this->target);
        });*/
    }

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