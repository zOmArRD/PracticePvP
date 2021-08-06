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
use greek\network\NetworkSession;
use greek\network\player\NetworkPlayer;
use greek\network\utils\TextUtils;
use pocketmine\utils\Config;

class Lang
{
    /** @var NetworkPlayer  */
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
        AsyncQueue::submitQuery(new InsertQuery("UPDATE settings SET $key='$value' WHERE name='{$playerName}'"));
    }

    public function setLanguage(string $language, bool $safe): void
    {
        $playerName = $this->getPlayer()->getName();
        self::$users[$playerName] = $language;
        if ($safe) {
            NetworkSession::$playerData[$playerName]["language"] = $language;
            $this->setStringValue("language", $language);
        }
    }

    public function applyLanguage(): void
    {
        $player = self::getPlayer();
        if (isset(NetworkSession::$playerData[$player->getName()])) {
            $data = NetworkSession::$playerData[$player->getName()];
            if ($data["language"] !== null && $data["language"] !== "null") {
                $this->setLanguage($data["language"], false);
            }
        }
    }

    public function getLanguage(): string
    {
        $player = $this->getPlayer();
        return self::$users[$player->getName()] ?? "en_ENG";
    }

    public function getString(string $id): string
    {
        $strings = self::$lang[$this->getLanguage()]->get("strings");
        try {
            return $strings["$id"]/* ?? TextUtils::replaceColor($strings["message.error"])*/;
        } catch (Exception $exception) {
            var_dump($exception->getMessage());
            return "error-402";
        }
    }

    public function replaceVars(string $msg, array $array): string
    {
        $m = $msg;
        $keys = array_keys($array);
        $values = array_values($array);

        for ($i = 0; $i < count($keys); $i++) $m = str_replace($keys[$i], $values[$i], $m);
        return $m;
    }

    public function showForm(): void
    {
        $player = $this->getPlayer();
        $form = new SimpleForm(function (NetworkPlayer $player, $data) {
            if (isset($data)) {
                try {
                    $languages = Lang::$config->get("languages");
                    $lang = $languages[$data];
                    $this->setLanguage($lang['ISOCode'], true);
                    $player->getInventory()->clearAll();
                    $player->giveLobbyItems();
                    $player->sendMessage($player->getTranslatedMsg("message.langselector.setlanguage"));
                } catch (Exception $exception) {
                    var_dump($exception->getMessage());
                }
            }
        });

        $form->setTitle($player->getTranslatedMsg("form.title.langselector"));

        try {
            foreach (Lang::$config->get("languages") as $lang) {
                $form->addButton("§a" . $lang['name'], 1, $lang['icon']);
            }
        } catch (Exception $exception) {
            var_dump($exception->getMessage());
        }

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