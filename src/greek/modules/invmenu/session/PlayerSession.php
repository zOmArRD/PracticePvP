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

namespace greek\modules\invmenu\session;

use Closure;
use greek\modules\invmenu\InvMenu;
use greek\modules\invmenu\InvMenuHandler;
use greek\network\player\NetworkPlayer;
use InvalidArgumentException;
use InvalidStateException;
use pocketmine\network\mcpe\protocol\types\ContainerIds;

class PlayerSession
{

    /** @var NetworkPlayer */
    protected NetworkPlayer $player;

    /** @var PlayerNetwork */
    protected PlayerNetwork $network;

    /** @var MenuExtradata */
    protected MenuExtradata $menu_extradata;

    /** @var InvMenu|null */
    protected ?InvMenu $current_menu;

    /** @var int */
    protected int $current_window_id = ContainerIds::NONE;

    public function __construct(NetworkPlayer $player, PlayerNetwork $network)
    {
        $this->player = $player;
        $this->network = $network;
        $this->menu_extradata = new MenuExtradata();
    }

    /**
     * @internal
     */
    public function finalize(): void
    {
        if ($this->current_menu !== null) {
            $this->removeWindow();
        }
        $this->network->dropPending();
    }

    public function removeWindow(): void
    {
        $window = $this->player->getWindow($this->current_window_id);
        if ($window !== null) {
            $this->player->removeWindow($window);
            $this->network->wait(static function (bool $success): void {
            });
        }
        $this->current_window_id = ContainerIds::NONE;
    }

    public function getMenuExtradata(): MenuExtradata
    {
        return $this->menu_extradata;
    }

    private function sendWindow(): bool
    {
        $this->removeWindow();

        try {
            $position = $this->menu_extradata->getPosition();
            $inventory = $this->current_menu->getInventory();
            /** @noinspection NullPointerExceptionInspection */
            $inventory->moveTo($position->x, $position->y, $position->z);
            $this->current_window_id = $this->player->addWindow($inventory);
        } catch (InvalidStateException | InvalidArgumentException $e) {
            InvMenuHandler::getRegistrant()->getLogger()->debug("InvMenu failed to send inventory to {$this->player->getName()} due to: {$e->getMessage()}");
            $this->removeWindow();
        }

        return $this->current_window_id !== ContainerIds::NONE;
    }

    /**
     * @param InvMenu|null $menu
     * @param Closure|null $callback
     * @internal use InvMenu::send() instead.
     *
     */
    public function setCurrentMenu(?InvMenu $menu, ?Closure $callback = null): void
    {
        $this->current_menu = $menu;

        if ($this->current_menu !== null) {
            $this->network->waitUntil($this->network->getGraphicWaitDuration(), function (bool $success) use ($callback): void {
                if ($this->current_menu !== null) {
                    if ($success && $this->sendWindow()) {
                        if ($callback !== null) {
                            $callback(true);
                        }
                        return;
                    }
                    $this->removeCurrentMenu();
                }
                if ($callback !== null) {
                    $callback(false);
                }
            });
        } else {
            $this->network->wait($callback ?? static function (bool $success): void {
                });
        }
    }

    public function getNetwork(): PlayerNetwork
    {
        return $this->network;
    }

    public function getCurrentMenu(): ?InvMenu
    {
        return $this->current_menu;
    }

    /**
     * @return bool
     * @internal use Player::removeWindow() instead
     */
    public function removeCurrentMenu(): bool
    {
        if ($this->current_menu !== null) {
            $this->current_menu->getType()->removeGraphic($this->player, $this->menu_extradata);
            $this->menu_extradata->reset();
            $this->setCurrentMenu(null);
            return true;
        }
        return false;
    }
}
