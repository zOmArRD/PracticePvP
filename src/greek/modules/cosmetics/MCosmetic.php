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

namespace greek\modules\cosmetics;

use greek\modules\database\mysql\AsyncQueue;
use greek\modules\form\lib\SimpleForm;
use greek\network\player\NetworkPlayer;
use greek\network\session\Session;
use greek\network\utils\TextUtils;
use pocketmine\utils\TextFormat;
use const greek\PREFIX;

class MCosmetic
{
    /** @var NetworkPlayer  */
    public NetworkPlayer $player;

    /** @var array */
    public static array $walkTrails = [], $particles = [];

    public static array $cosmeticsData = [];

    public function __construct(NetworkPlayer $player)
    {
        $this->player = $player;
    }

    /**
     * @return NetworkPlayer
     */
    public function getPlayer(): NetworkPlayer
    {
        return $this->player;
    }

    /**
     * @param SimpleForm  $form
     * @param string      $buttonName
     * @param array       $imageData
     * @param string|null $label
     * @param string      $type
     */
    public function addButton(SimpleForm $form, string $buttonName, array $imageData, ?string $label, string $type): void
    {
        $name = $buttonName;
        if (!$this->player->hasPermission("cosmetics.$type" . ".$label") or !$this->player->isOp()) {
            $locked = $this->player->getTranslatedMsg("form.button.locked");
            $name .= TextFormat::EOL . TextUtils::replaceColor($locked);
        } else {
            $unLocked = $this->player->getTranslatedMsg("form.button.locked");
            $name .= TextFormat::EOL . TextUtils::replaceColor($unLocked);
        }
        $form->addButton($name, $imageData[0], $imageData[1], $label);
    }

    /**
     * @param string $cosmetic
     * @param string $type
     *
     * @return string
     */
    public function getMessageUpdated(string $cosmetic, string $type = "activate"): string
    {
        $msg = $this->getPlayer()->getTranslatedMsg("message.$type");
        return TextUtils::replaceVars($msg, ["{cosmetic}" => $cosmetic]);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function setStringValue(string $key, string $value): void
    {
        AsyncQueue::insertQuery("UPDATE cosmetics SET $key='$value' WHERE ign='{$this->player->getName()}';");
    }

    /**
     * @param string $particleId
     * @param bool   $safe
     */
    public function setParticles(string $particleId, bool $safe = true): void
    {
        $player = $this->getPlayer();
        self::$particles[$player->getName()] = $particleId;

        if ($safe) {
            Session::$playerData[$player->getName()]["particles"] = $particleId;
            $this->setStringValue("particles", $particleId);
        }
    }

    /**
     * @param bool $save
     */
    public function removeParticles(bool $save= true): void
    {
        $player = $this->getPlayer();
        if (isset(self::$particles[$player->getName()])) {
            unset(self::$particles[$player->getName()]);
        }
        if ($save) {
            $this->setStringValue("particles", "null");
        }
    }

    public function applyCosmetics(): void
    {
        $player = $this->getPlayer();

        if (isset(Session::$playerData[$player->getName()])) {
            $data = Session::$playerData[$player->getName()];

            if ($data["particles"] !== null && $data["particles"] !== "null") {
                $this->setParticles($data["particles"], false);
            }
        } else {
            $player->sendMessage(PREFIX . $player->getTranslatedMsg("message.cosmetics.error"));
        }
    }
}