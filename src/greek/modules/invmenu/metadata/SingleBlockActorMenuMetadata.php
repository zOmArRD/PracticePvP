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

use greek\modules\invmenu\session\MenuExtradata;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\Player;
use pocketmine\tile\Nameable;
use pocketmine\tile\Tile;

class SingleBlockActorMenuMetadata extends SingleBlockMenuMetadata
{

    /** @var NetworkLittleEndianNBTStream */
    protected static NetworkLittleEndianNBTStream $serializer;

    /** @var string */
    protected string $tile_id;

    public function __construct(string $identifier, int $size, int $window_type, Block $block, string $tile_id)
    {
        parent::__construct($identifier, $size, $window_type, $block);

        if (self::$serializer === null) {
            self::$serializer = new NetworkLittleEndianNBTStream();
        }

        $this->tile_id = $tile_id;
    }

    protected function sendGraphicAt(Vector3 $pos, Player $player, MenuExtradata $metadata): void
    {
        parent::sendGraphicAt($pos, $player, $metadata);
        $player->sendDataPacket($this->getBlockActorDataPacketAt($player, $pos, $metadata->getName()));
    }

    protected function getBlockActorDataPacketAt(Player $player, Vector3 $pos, ?string $name): BlockActorDataPacket
    {
        $packet = new BlockActorDataPacket();
        $packet->x = $pos->x;
        $packet->y = $pos->y;
        $packet->z = $pos->z;

        $namedtag = self::$serializer->write($this->getBlockActorDataAt($pos, $name));
        assert($namedtag !== false);

        $packet->namedtag = $namedtag;
        return $packet;
    }

    protected function getBlockActorDataAt(Vector3 $pos, ?string $name): CompoundTag
    {
        $tag = new CompoundTag();
        $tag->setString(Tile::TAG_ID, $this->tile_id);
        if ($name !== null) {
            $tag->setString(Nameable::TAG_CUSTOM_NAME, $name);
        }
        return $tag;
    }
}