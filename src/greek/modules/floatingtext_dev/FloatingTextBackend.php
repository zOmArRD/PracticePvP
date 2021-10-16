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

use greek\network\player\NetworkPlayer;
use pocketmine\entity\Skin;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Server;
use const greek\SPAWN_OPTIONS;

abstract class FloatingTextBackend
{
    /**
     * @param string        $id
     * @param NetworkPlayer $player
     * @param Vector3       $vector3
     */
    function create(string $id, NetworkPlayer $player, Vector3 $vector3): void
    {
        $level = $player->getLevel();
        $plSkinData = $player->getSkin();

        /* Check if the entity is already created, this avoids the duplication of entities. */
        foreach ($level->getEntities() as $entity) if ($entity instanceof FloatingTextEntity) {
            if ($entity->getSkin()->getSkinId() === $id) {
                $entity->kill();
            }
        }

        $nbt = new CompoundTag("", [
            new ListTag("Pos", [
                new DoubleTag("", $vector3->getX()),
                new DoubleTag("", $vector3->getY()),
                new DoubleTag("", $vector3->getZ())
            ]),
            new ListTag("Motion", [
                new DoubleTag("", 0),
                new DoubleTag("", 0),
                new DoubleTag("", 0)
            ]),
        ]);

        $textE = new FloatingTextEntity($level, $nbt);
        $textE->setSkin(new Skin($id, $plSkinData->getSkinData()));
        $textE->setNameTagVisible(true);
        $textE->spawnToAll();
    }

    /**
     * @param string $id
     * @param string $text
     */
    function updateText(string $id, string $text = ""): void
    {
        if (SPAWN_OPTIONS['enabled']) {
            $spawn = SPAWN_OPTIONS;
            $level = Server::getInstance()->getLevelByName($spawn['world.name']);
        } else {
            $level = Server::getInstance()->getDefaultLevel();
        }

        foreach ($level->getEntities() as $entity) {
            if ($entity instanceof FloatingTextEntity) {
                if ($entity->getSkin()->getSkinId() === $id) $entity->setNameTag($text);
            }
        }
    }
}