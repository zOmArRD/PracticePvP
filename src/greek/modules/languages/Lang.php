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
use greek\modules\database\mysql\InsertQuery;
use greek\modules\form\lib\SimpleForm;
use greek\network\player\NetworkPlayer;
use greek\network\utils\TextUtils;
use pocketmine\Player;
use pocketmine\utils\Config;

class Lang extends ILang
{
    /** @var Player */
    static private Player $player;

    /** @var array */
    public static array $lang = [], $users = [];

    /** @var Config */
    public static Config $config;

    public function __construct(Player $player)
    {
        self::setPlayer($player);
    }

    static public function setStringValue(string $key, string $value): void
    {
        $playerName = self::getPlayer()->getName();
        AsyncQueue::submitQuery(new InsertQuery("UPDATE settings SET $key='$value' WHERE ign='{$playerName}'"));
    }

    static public function setLanguage(string $language, bool $safe): void
    {
        $playerName = self::getPlayer()->getName();
        self::$users[$playerName] = $language;
        if ($safe) {
            NetworkPlayer::$playerData[$playerName]["language"] = $language;
            self::setStringValue("language", $language);
        }
    }

    static public function applyPlayerLanguage(): void
    {
        $player = self::getPlayer();
        if (isset(NetworkPlayer::$playerData[$player->getName()])) {
            $data = NetworkPlayer::$playerData[$player->getName()];
            if ($data["language"] !== null && $data["language"] !== "null") {
                self::setLanguage($data["language"], false);
            }
        }
    }

    static public function getPlayerLanguage(): string
    {
        $player = self::getPlayer();
        return self::$users[$player->getName()] ?? "en_ENG";
    }

    static public function getString(string $id): string
    {
        $strings = self::$lang[self::getPlayerLanguage()]->get("strings");
        try {
            return $strings["$id"] ?? TextUtils::replaceColor($strings["message.error"]);
        } catch (Exception $exception) {
            return "error-402";
        }
    }

    static public function replaceVars(string $msg, array $array): string
    {
        $m = $msg;
        $keys = array_keys($array);
        $values = array_values($array);

        for ($i = 0; $i < count($keys); $i++) $m = str_replace($keys[$i], $values[$i], $m);
        return $m;
    }

    /**
     * TODO: Finalize the Language selector.
     */
    static public function showForm(): void
    {
        $player = self::getPlayer();
        $form = new SimpleForm(function ($data) {

        });

        $player->sendForm($form);
    }

    /**
     * @param Player $player
     */
    public static function setPlayer(Player $player): void
    {
        self::$player = $player;
    }

    /**
     * @return Player
     */
    public static function getPlayer(): Player
    {
        return self::$player;
    }
}