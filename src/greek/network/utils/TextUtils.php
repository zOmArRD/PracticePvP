<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 1/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\network\utils;

use Exception;
use pocketmine\utils\TextFormat;

class TextUtils extends TextFormat
{
    /**
     * This function is in charge of searching for the keys that are defined here below,
     * and replaces them with the colors of PocketMine-MP.
     *
     * @param string $text
     * @return string
     */
    static public function replaceColor(string $text): string
    {
        $m = $text;

        $colors = ["{black}" => self::BLACK,
            "{dark.blue}" => self::DARK_BLUE,
            "{dark.green}" => self::DARK_GREEN,
            "{dark.aqua}" => self::DARK_AQUA,
            "{dark.red}" => self::DARK_RED,
            "{dark.purple}" => self::DARK_PURPLE,
            "{gold}" => self::GOLD,
            "{gray}" => self::GRAY,
            "{dark.gray}" => self::DARK_GRAY,
            "{blue}" => self::BLUE,
            "{green}" => self::GREEN,
            "{aqua}" => self::AQUA,
            "{red}" => self::RED,
            "{light.purple}" => self::LIGHT_PURPLE,
            "{yellow}" => self::YELLOW,
            "{white}" => self::WHITE,
            "{obfuscated}" => self::OBFUSCATED,
            "{bold}" => self::BOLD,
            "{strikethrough}" => self::STRIKETHROUGH,
            "{underline}" => self::UNDERLINE,
            "{italic}" => self::ITALIC,
            "{reset}" => self::RESET,
            "{eol}" => self::EOL];

        $keys = array_keys($colors);
        $values = array_values($colors);

        for ($i = 0; $i < count($keys); $i++) $m = str_replace($keys[$i], (string)$values[$i], $m);

        return $m ?? "";
    }

    public function replaceVars(string $msg, array $array): string
    {
        $m = $msg;
        $keys = array_keys($array);
        $values = array_values($array);

        for ($i = 0; $i < count($keys); $i++) $m = str_replace($keys[$i], $values[$i], $m);
        return $m;
    }


    /**
     * @param string $id
     * @return bool|string
     */
    public static function uDecode(string $id): bool|string
    {
        try {
            return convert_uudecode($id);
        } catch (Exception) {
            return "error";
        }
    }
}