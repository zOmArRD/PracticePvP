<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 5/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\network\player\skin;

use Exception;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\protocol\types\LegacySkinAdapter;
use pocketmine\network\mcpe\protocol\types\SkinData;

class PersonaSkinAdapter extends LegacySkinAdapter
{
    /** @var array  */
    private array $personaSkins = [];

    /**
     * @param SkinData $data
     * @return Skin
     * @throws Exception
     */
    public function fromSkinData(SkinData $data): Skin
    {
        if ($data->isPersona()) {
            $id = $data->getSkinId();
            $this->personaSkins[$id] = $data;
            return new Skin($id, str_repeat(random_bytes(3) . "\xff", 2048));
        }
        return parent::fromSkinData($data);
    }

    /**
     * @param Skin $skin
     * @return SkinData
     */
    public function toSkinData(Skin $skin): SkinData
    {
        return $this->personaSkins[$skin->getSkinId()] ?? parent::toSkinData($skin);
    }
}