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

use pocketmine\math\Vector3;

class MenuExtradata
{

    /** @var Vector3|null */
    protected ?Vector3 $position = null;

    /** @var string|null */
    protected ?string $name = null;

    public function getPosition(): ?Vector3
    {
        return $this->position;
    }

    public function getPositionNotNull(): Vector3
    {
        return $this->position;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setPosition(?Vector3 $pos): void
    {
        $this->position = $pos;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function reset(): void
    {
        $this->position = null;
        $this->name = null;
    }
}