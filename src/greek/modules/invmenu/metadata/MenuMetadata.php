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

namespace greek\modules\invmenu\metadata;

use greek\modules\invmenu\inventory\InvMenuInventory;
use greek\modules\invmenu\session\MenuExtradata;
use greek\network\player\NetworkPlayer;
use pocketmine\math\Vector3;

abstract class MenuMetadata
{

    /** @var string */
    protected string $identifier;

    /** @var int */
    protected int $size;

    /** @var int */
    protected int $window_type;

    public function __construct(string $identifier, int $size, int $window_type)
    {
        $this->identifier = $identifier;
        $this->size = $size;
        $this->window_type = $window_type;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getWindowType(): int
    {
        return $this->window_type;
    }

    public function createInventory(): InvMenuInventory
    {
        return new InvMenuInventory($this);
    }

    protected function calculateGraphicOffset(NetworkPlayer $player): Vector3
    {
        $offset = $player->getDirectionVector();
        $offset->x *= -(1 + $player->width);
        $offset->y *= -(1 + $player->height);
        $offset->z *= -(1 + $player->width);
        return $offset;
    }

    public function calculateGraphicPosition(NetworkPlayer $player): Vector3
    {
        return $player->getPosition()->add($this->calculateGraphicOffset($player))->floor();
    }

    abstract public function sendGraphic(NetworkPlayer $player, MenuExtradata $metadata): bool;

    abstract public function removeGraphic(NetworkPlayer $player, MenuExtradata $extradata): void;
}