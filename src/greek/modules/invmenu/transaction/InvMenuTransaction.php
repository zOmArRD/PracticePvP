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

namespace greek\modules\invmenu\transaction;

use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\Item;
use pocketmine\Player;

class InvMenuTransaction
{

    /** @var Player */
    private Player $player;

    /** @var Item */
    private Item $out;

    /** @var Item */
    private Item $in;

    /** @var SlotChangeAction */
    private SlotChangeAction $action;

    /** @var InventoryTransaction */
    private InventoryTransaction $transaction;

    public function __construct(Player $player, Item $out, Item $in, SlotChangeAction $action, InventoryTransaction $transaction)
    {
        $this->player = $player;
        $this->out = $out;
        $this->in = $in;
        $this->action = $action;
        $this->transaction = $transaction;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getOut(): Item
    {
        return $this->out;
    }

    public function getIn(): Item
    {
        return $this->in;
    }

    /**
     * Returns the item that was clicked / taken out of the inventory.
     *
     * @return Item
     * @link InvMenuTransaction::getOut()
     */
    public function getItemClicked(): Item
    {
        return $this->getOut();
    }

    /**
     * Returns the item that an item was clicked with / placed in the inventory.
     *
     * @return Item
     * @link InvMenuTransaction::getIn()
     */
    public function getItemClickedWith(): Item
    {
        return $this->getIn();
    }

    public function getAction(): SlotChangeAction
    {
        return $this->action;
    }

    public function getTransaction(): InventoryTransaction
    {
        return $this->transaction;
    }

    public function continue(): InvMenuTransactionResult
    {
        return new InvMenuTransactionResult(false);
    }

    public function discard(): InvMenuTransactionResult
    {
        return new InvMenuTransactionResult(true);
    }
}