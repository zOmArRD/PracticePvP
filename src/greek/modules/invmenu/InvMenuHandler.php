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

namespace greek\modules\invmenu;

use greek\Loader;
use greek\modules\invmenu\metadata\DoubleBlockActorMenuMetadata;
use greek\modules\invmenu\metadata\MenuMetadata;
use greek\modules\invmenu\metadata\SingleBlockActorMenuMetadata;
use greek\modules\invmenu\session\network\handler\PlayerNetworkHandlerRegistry;
use InvalidArgumentException;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\tile\Tile;

final class InvMenuHandler
{

    /** @var Loader|null  */
    private static ?Loader $registrant = null;

    /** @var MenuMetadata[] */
    private static array $menu_types = [];

    public static function getRegistrant(): ?Loader
    {
        return self::$registrant;
    }

    public static function register(Loader $plugin): void
    {
        self::$registrant = $plugin;
        self::registerDefaultMenuTypes();
        PlayerNetworkHandlerRegistry::init();
        $plugin->getServer()->getPluginManager()->registerEvents(new InvMenuEventHandler($plugin), $plugin);
    }

    private static function registerDefaultMenuTypes(): void
    {
        self::registerMenuType(new SingleBlockActorMenuMetadata(MenuIds::TYPE_CHEST, 27, WindowTypes::CONTAINER, BlockFactory::get(BlockIds::CHEST), Tile::CHEST));
        self::registerMenuType(new DoubleBlockActorMenuMetadata(MenuIds::TYPE_DOUBLE_CHEST, 54, WindowTypes::CONTAINER, BlockFactory::get(BlockIds::CHEST), Tile::CHEST));
        self::registerMenuType(new SingleBlockActorMenuMetadata(MenuIds::TYPE_HOPPER, 5, WindowTypes::HOPPER, BlockFactory::get(BlockIds::HOPPER_BLOCK), "Hopper"));
    }

    public static function registerMenuType(MenuMetadata $type, bool $override = false): void
    {
        if (isset(self::$menu_types[$identifier = $type->getIdentifier()]) && !$override) {
            throw new InvalidArgumentException("A menu type with the identifier \"{$identifier}\" is already registered as " . get_class(self::$menu_types[$identifier]));
        }

        self::$menu_types[$identifier] = $type;
    }

    public static function getMenuType(string $identifier): ?MenuMetadata
    {
        return self::$menu_types[$identifier] ?? null;
    }
}