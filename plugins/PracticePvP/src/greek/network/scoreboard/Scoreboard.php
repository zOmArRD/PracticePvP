<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 4/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\network\scoreboard;

use greek\Loader;
use greek\modules\form\lib\SimpleForm;
use greek\network\config\SettingsForm;
use greek\network\NetworkSession;
use greek\network\player\NetworkPlayer;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use TypeError;

class Scoreboard extends ScoreboardAPI
{
    /** @var string[]  */
    private const EMPTY_CACHE = ["§0\e", "§1\e", "§2\e", "§3\e", "§4\e", "§5\e", "§6\e", "§7\e", "§8\e", "§9\e", "§a\e", "§b\e", "§c\e", "§d\e", "§e\e"];

    public function sendScore(NetworkPlayer $player, string $language): void
    {
        if (isset(NetworkSession::$playerData[$player->getName()])) {
            $scData = NetworkSession::$playerData[$player->getName()];

            if ($scData["ShowScoreboard"] == 0) {
                $this->remove($player);
                return;
            }
        }

        $configSC = new Config(Loader::getInstance()->getDataFolder() . "scoreboard.yml", Config::YAML);

        $this->new($player, "greek.practice", $configSC->get("display.name", "§6§lGreek §8Network"));

        $data  = $configSC->get($language);

        if (!is_array($data)) return;

        foreach ($data as $scLine => $message) {
            $line = $scLine + 1;
            $msg = $this->replaceData($player, $line, $message);

            $this->setLine($player, $line, $msg);
        }
    }

    public function replaceData(NetworkPlayer $player, int $line, string $message): string
    {
        if (empty($message)) return self::EMPTY_CACHE[$line] ?? "";
        
        $msg = $message;
        
        $data = [
            "{black}" => TextFormat::BLACK,
            "{dark.blue}" => TextFormat::DARK_BLUE,
            "{dark.green}" => TextFormat::DARK_GREEN,
            "{dark.aqua}" => TextFormat::DARK_AQUA,
            "{dark.red}" => TextFormat::DARK_RED,
            "{dark.purple}" => TextFormat::DARK_PURPLE,
            "{gold}" => TextFormat::GOLD,
            "{gray}" => TextFormat::GRAY,
            "{dark.gray}" => TextFormat::DARK_GRAY,
            "{blue}" => TextFormat::BLUE,
            "{green}" => TextFormat::GREEN,
            "{aqua}" => TextFormat::AQUA,
            "{red}" => TextFormat::RED,
            "{light.purple}" => TextFormat::LIGHT_PURPLE,
            "{yellow}" => TextFormat::YELLOW,
            "{white}" => TextFormat::WHITE,
            "{obfuscated}" => TextFormat::OBFUSCATED,
            "{bold}" => TextFormat::BOLD,
            "{strikethrough}" => TextFormat::STRIKETHROUGH,
            "{underline}" => TextFormat::UNDERLINE,
            "{italic}" => TextFormat::ITALIC,
            "{reset}" => TextFormat::RESET,
            "{eol}" => TextFormat::EOL,
            "{player.name}" => $player->getName(),
            "{date}" => date("d/m/Y"),
            "{practice.online}" => count(Server::getInstance()->getOnlinePlayers())
        ];

        $keys = array_keys($data);
        $values = array_values($data);

        for ($i = 0; $i < count($keys); $i++) {
            $msg = str_replace($keys[$i], (string)$values[$i], $msg);
        }

        return $msg;
        /* END. */
    }

    public static function showForm(NetworkPlayer $player): void
    {
        $form = new SimpleForm(function (NetworkPlayer $player, $data){
            if (isset($data)) {
                $scData = NetworkSession::$playerData[$player->getName()];

                switch ($data) {
                    case "enable":
                        $scData["ShowScoreboard"] = true;
                        break;
                    case "disable":
                        $scData["ShowScoreboard"] = false;
                        break;
                    default:
                        new SettingsForm($player);
                        break;
                }
            }
        });
        $images = [
            "enable" => "textures/ui/check",
            "disable" => "textures/ui/cancel"
        ];

        $form->setTitle($player->getTranslatedMsg("form.title.scoreboard"));
        $form->addButton($player->getTranslatedMsg("form.button.enable"), 0, $images['enable'], "enable");
        $form->addButton($player->getTranslatedMsg("form.button.disable"), 0, $images['disable'], "disable");

        $form->addButton($player->getTranslatedMsg("form.button.back"));

        $player->sendForm($form);
    }
}