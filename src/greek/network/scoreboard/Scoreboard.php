<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 22/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\network\scoreboard;

use Exception;
use greek\Loader;
use greek\modules\database\mysql\AsyncQueue;
use greek\modules\database\mysql\query\InsertQuery;
use greek\modules\form\lib\SimpleForm;
use greek\network\config\Settings;
use greek\network\config\SettingsForm;
use greek\network\player\NetworkPlayer;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use const pocketmine\START_TIME;

class Scoreboard extends ScoreboardAPI
{
    private const EMPTY_CACHE = ["§0\e", "§1\e", "§2\e", "§3\e", "§4\e", "§5\e", "§6\e", "§7\e", "§8\e", "§9\e", "§a\e", "§b\e", "§c\e", "§d\e", "§e\e"];

    public function __construct(NetworkPlayer $player)
    {
        $this->setPlayer($player);
    }

    public function setScore(): void
    {
        if (isset(NetworkPlayer::$data[$this->player->getName()])) {
            $scData = NetworkPlayer::$data[$this->player->getName()];

            if ($scData["ShowScoreboard"] == false) {
                return;
            }
        }
        $configSC = new Config(file: Loader::getInstance()->getDataFolder() . "scoreboard.yml", type: Config::YAML);

        $this->new(objectiveName: "greek.practice", displayName: $configSC->get(k: "display.name", default: "§6§lGreek §8Network"));
        $this->updateLine();
    }

    public function updateLine(): void
    {
        $configSC = new Config(file: Loader::getInstance()->getDataFolder() . "scoreboard.yml", type: Config::YAML);

        if ($this->getPlayer()->isPartyMode()) {
            $strings = $configSC->get(k: $this->player->getLangSession()->getLanguage())["party"];
        } else {
            $strings = $configSC->get(k: $this->player->getLangSession()->getLanguage())["normal"];
        }

        $data = [];

        foreach ($strings as $string => $message) {
            $line = $string + 1;
            $msg = $this->replaceData(line: $line, message: $message);

            $data[] = $msg;
        }

        foreach ($data as $scLine => $message) {
            $line = $scLine +1;
            $this->setLine(score: $line, message: $message);
        }
    }

    public function replaceData(int $line, string $message): string
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
            "{player.name}" => $this->getPlayer()->getName(),
            "{date}" => date(format: "d/m/Y"),
            "{practice.players}" => count(Server::getInstance()->getOnlinePlayers()),
            "{practice.maxplayers}" => Server::getInstance()->getMaxPlayers(),
            "{practice.playing}" => 0, /* TODO: Get Down-Stream Server Players */
            "{party.members}" => 0, /* TODO: Return the current players of the party. */
            "{party.maxmembers}" => 0, /* TODO: Return the maximum players allowed in a party. */
            "{tps}" => Server::getInstance()->getTicksPerSecond(),
            "{days}" => $this->getUptime(),
            "{hours}" => $this->getUptime(type: "hours"),
            "{minutes}" => $this->getUptime(type: "minutes"),
        ];

        $keys = array_keys(array: $data);
        $values = array_values(array:  $data);

        for ($i = 0; $i < count(value: $keys); $i++) {
            $msg = str_replace(search: $keys[$i], replace: (string)$values[$i], subject: $msg);
        }

        return $msg;
    }

    public function getUptime(string $type = "days"): string
    {
        $time = (int) (microtime(as_float: true) - START_TIME);
        $minutes = null;
        $hours = null;
        $days = null;

        if($time >= 60){
            $minutes = floor(num: ($time % 3600) / 60);
            if($time >= 3600){
                $hours = floor(num: ($time % (3600 * 24)) / 3600);
                if($time >= 3600 * 24){
                    $days = floor(num: $time / (3600 * 24));
                }
            }
        }

        return match ($type) {
            "days" => ($days !== null ? "$days" : "?"),
            "hours" => ($days !== null ? "$hours" : "?"),
            "minutes" => ($days !== null ? "$minutes" : "?"),
            default => "?",
        };
    }

    public function showForm(): void
    {
        $player = $this->getPlayer();
        $form = new SimpleForm(function (NetworkPlayer $player, $data) {
            if (isset($data)) {
                try {
                    $scData = NetworkPlayer::$data[$player->getName()];
                    switch ($data) {
                        case "enable":
                            if ($scData["ShowScoreboard"] == true) {
                                $player->sendMessage(message: Settings::$prefix . $player->getTranslatedMsg(idMsg: "message.cantupdate"));
                            } else {
                                $scData["ShowScoreboard"] = true;
                                $this->setMysqlScore(bool: 1, ign: $this->getPlayer()->getName());
                                $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg(idMsg: "message.scoreboard.updated"));
                            }
                            break;
                        case "disable":
                            if ($scData["ShowScoreboard"] == false) {
                                $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg(idMsg: "message.scoreboard.cantupdate"));
                            } else {
                                $scData["ShowScoreboard"] = false;
                                $this->setMysqlScore(bool:0, ign: $this->getPlayer()->getName());
                                $this->remove();
                                $player->sendMessage(message: Settings::$prefix . $player->getTranslatedMsg(idMsg: "message.scoreboard.updated"));
                            }
                            break;
                        default:
                            new SettingsForm(player: $player);
                            var_dump(value: $scData);
                            break;
                    }
                } catch (Exception $exception) {
                    var_dump(value: $exception->getMessage() . "\n" . $exception->getLine() . "\n" . $exception->getCode());
                }
            }
        });
        $images = [
            "enable" => "textures/ui/check",
            "disable" => "textures/ui/cancel"
        ];

        $form->setTitle(title: $player->getTranslatedMsg(idMsg: "form.title.scoreboard"));
        $form->addButton(text: $player->getTranslatedMsg(idMsg: "form.button.enable"), imageType: $form::IMAGE_TYPE_PATH, imagePath: $images['enable'], label: "enable");
        $form->addButton(text: $player->getTranslatedMsg(idMsg: "form.button.disable"), imageType: $form::IMAGE_TYPE_PATH, imagePath: $images['disable'], label: "disable");
        $form->addButton(text: $player->getTranslatedMsg(idMsg: "form.button.back"));
        $player->sendForm(form: $form);
    }

    public function setMysqlScore(int $bool, $ign)
    {
        AsyncQueue::submitQuery(asyncQuery: new InsertQuery(sqlQuery: "UPDATE settings SET ShowScoreboard = $bool WHERE ign = '$ign'"));
    }
}