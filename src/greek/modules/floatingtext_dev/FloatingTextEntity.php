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

namespace greek\modules\floatingtext;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;

final class FloatingTextEntity extends Human
{
    public function __construct(Level $level, CompoundTag $nbt)
    {
        parent::__construct($level, $nbt);
        $this->propertyManager->setFloat(self::DATA_SCALE, 0);
        $this->propertyManager->setLong(self::DATA_FLAG_ACTION, 1 << Entity::DATA_FLAG_IMMOBILE);
    }
}