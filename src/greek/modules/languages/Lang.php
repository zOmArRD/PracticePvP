<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 1/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */

namespace greek\modules\languages;

use Exception;
use greek\modules\database\mysql\AsyncQueue;
use greek\modules\database\mysql\query\InsertQuery;
use greek\modules\form\lib\SimpleForm;
use greek\network\config\Settings;
use greek\network\config\SettingsForm;
use greek\network\player\NetworkPlayer;
use greek\network\utils\TextUtils;
use JetBrains\PhpStorm\Pure;
use pocketmine\utils\Config;

class Lang
{
    /** @var NetworkPlayer */
    private NetworkPlayer $player;

    /** @var array */
    public static array $lang = [], $users = [];

    /** @var Config */
    public static Config $config;

    public function __construct(NetworkPlayer $player)
    {
        $this->setPlayer($player);
    }

    public function setStringValue(string $key, string $value): void
    {
        $playerName = $this->getPlayer()->getName();
        AsyncQueue::submitQuery(new InsertQuery("UPDATE settings SET $key='$value' WHERE ign='{$playerName}'"));
    }

    public function setLanguage(string $language, bool $safe): void
    {
        $playerName = $this->getPlayer()->getName();
        self::$users[$playerName] = $language;
        if ($safe) {
            NetworkPlayer::$data[$playerName]["language"] = $language;
            $this->setStringValue("language", $language);
        }
    }

    public function applyLanguage(): void
    {
        $player = self::getPlayer();
        if (isset(NetworkPlayer::$data[$player->getName()])) {
            $data = NetworkPlayer::$data[$player->getName()];
            if ($data["language"] !== null && $data["language"] !== "null") {
                $this->setLanguage($data["language"], false);
            }
        }
    }

    #[Pure]
    public function getLanguage(): string
    {
        $player = $this->getPlayer();
        return self::$users[$player->getName()] ?? "en_ENG";
    }

    public function getString(string $id): string
    {
        $strings = self::$lang[$this->getLanguage()]->get("strings");
        return $strings["$id"] ?? TextUtils::replaceColor($strings["message.error"]);
    }


    public function showForm(): void
    {
        $player = $this->getPlayer();
        $form = new SimpleForm(function (NetworkPlayer $player, $data) {
            if (isset($data)) {
                if ($data == "back") {
                    new SettingsForm($player);
                    return;
                }

                if ($this->getLanguage() !== $data) {
                    $this->setLanguage($data, true);
                    $player->getInventory()->clearAll();
                    $player->giveLobbyItems();
                    $player->sendMessage($player->getTranslatedMsg("message.langselector.setlanguage"));
                } else {
                    $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.cantupdate"));
                }
            }
        });

        $form->setTitle($player->getTranslatedMsg("form.title.langselector"));

        try {
            foreach (Lang::$config->get("languages") as $lang) {
                $form->addButton("§a" . $lang['name'], $form::IMAGE_TYPE_URL, $lang['icon'], $lang['ISOCode']);
            }
        } catch (Exception $exception) {
            var_dump($exception->getMessage());
        }

        $form->addButton($player->getTranslatedMsg("form.button.back"), $form::IMAGE_TYPE_PATH, "", "back");
        $player->sendForm($form);
    }

    /**
     * @param NetworkPlayer $player
     */
    public function setPlayer(NetworkPlayer $player): void
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
}