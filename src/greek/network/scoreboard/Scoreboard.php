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
use greek\modules\database\mysql\query\UpdateRowQuery;
use greek\modules\form\lib\SimpleForm;
use greek\network\config\Settings;
use greek\network\config\SettingsForm;
use greek\network\player\NetworkPlayer;
use greek\network\server\ServerManager;
use greek\network\session\Session;
use greek\network\session\SessionFactory;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Scoreboard extends ScoreboardAPI
{
    /** @var string[] */
    private const EMPTY_CACHE = ["§0\e", "§1\e", "§2\e", "§3\e", "§4\e", "§5\e", "§6\e", "§7\e", "§8\e", "§9\e", "§a\e", "§b\e", "§c\e", "§d\e", "§e\e"];

    public function __construct(NetworkPlayer $player)
    {
        $this->setPlayer($player);
    }

    public function setScore(): void
    {
        if (isset(Session::$playerData[$this->player->getName()]["scoreboard"])) {
            $scData = Session::$playerData[$this->player->getName()];

            if (!$scData["scoreboard"]) {
                return;
            }
        }
        $configSC = new Config(Loader::getInstance()->getDataFolder() . "scoreboard.yml", Config::YAML);

        $this->new("greek.practice", $configSC->get("display.name", "§6§lGreek §8Network"));
        $this->updateLine();
    }

    public function updateLine(): void
    {
        $player = $this->player;
        $configSC = new Config(Loader::getInstance()->getDataFolder() . "scoreboard.yml", Config::YAML);
        $session = SessionFactory::getSession($player);

        if ($session->hasParty()) {
            $strings = $configSC->get($player->getLangSession()->getLanguage())['party'];
        } else {
            $strings = $configSC->get($player->getLangSession()->getLanguage())['normal'];
        }

        if ($player->isPerformanceViewer() and !$session->hasParty()) {
            $strings = null;
            $strings = $configSC->get($player->getLangSession()->getLanguage())['performance'];
        } elseif ($player->isQueue()) {
            $strings = null;
            $strings = $configSC->get($player->getLangSession()->getLanguage())['queue'];
        }

        $data = [];

        foreach ($strings as $string => $message) {
            $line = $string + 1;
            $msg = $this->replaceData($line, $message);

            $data[] = $msg;
        }

        foreach ($data as $scLine => $message) {
            $line = $scLine + 1;
            $this->setLine($line, $message);
        }
    }

    private function getTpsType(string $type): string
    {
        $ticks = Server::getInstance()->getTicksPerSecond();
        $ticksUsage = Server::getInstance()->getTickUsage();

        $average = Server::getInstance()->getTicksPerSecondAverage();
        $averageUsage = Server::getInstance()->getTickUsageAverage();

        $color = TextFormat::GREEN;
        if ($ticks < 12) {
            $color = TextFormat::RED;
        } elseif ($ticks < 17) {
            $color = TextFormat::GOLD;
        }

        return match ($type) {
            "current" => $color . $ticks . " (" . $ticksUsage . "%)",
            "average" => $color . $average . " (" . $averageUsage . "%)",
            default => "",
        };
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
            "{player.get.name}" => $this->getPlayer()->getName(),
            "{date}" => date("d/m/Y"),
            "{lobby.get.players}" => count(Server::getInstance()->getOnlinePlayers()),
            "{lobby.get.maxplayers}" => Server::getInstance()->getMaxPlayers(),
            "{practice.get.playing}" => ServerManager::getPracticePlayers(),
            "{party.get.members}" => $this->getPartyData("members"),
            "{party.get.maxmembers}" => $this->getPartyData("slots"),
            "{party.get.leader}" => $this->getPartyData("leader"),
            "{tps.get.current}" => $this->getTpsType("current"),
            "{tps.get.average}" => $this->getTpsType("average"),
            "{player.get.queue.kit}" => $this->getQueueData("kit"),
            "{player.get.queue.type}" => $this->getQueueData("type"),
        ];

        $keys = array_keys($data);
        $values = array_values($data);

        for ($i = 0; $i < count($keys); $i++) {
            $msg = str_replace($keys[$i], (string)$values[$i], $msg);
        }

        return $msg;
    }

    public function showForm(): void
    {
        $player = $this->getPlayer();
        $form = new SimpleForm(function (NetworkPlayer $player, $data) {
            if (isset($data)) {
                try {
                    $scData = Session::$playerData[$player->getName()];
                    switch ($data) {
                        case "enable":
                            if ($scData["scoreboard"] == true) {
                                $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.cantupdate"));
                            } else {
                                $scData["scoreboard"] = true;
                                $this->setMysqlScore($this->getPlayer()->getName());
                                $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.scoreboard.updated"));
                            }
                            break;
                        case "disable":
                            if ($scData["scoreboard"] == false) {
                                $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.cantupdate"));
                            } else {
                                $scData["scoreboard"] = false;
                                $this->setMysqlScore($this->getPlayer()->getName(), 0);
                                $this->remove();
                                $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.scoreboard.updated"));
                            }
                            break;
                        default:
                            new SettingsForm($player);
                            break;
                    }
                } catch (Exception $exception) {
                    var_dump($exception->getMessage() . "\n" . $exception->getLine() . "\n" . $exception->getCode());
                }
            }
        });
        $images = [
            "enable" => "textures/ui/check",
            "disable" => "textures/ui/cancel",
            "performance" => ""
        ];

        $form->setTitle($player->getTranslatedMsg("form.title.scoreboard"));
        $form->addButton($player->getTranslatedMsg("form.button.enable"), $form::IMAGE_TYPE_PATH, $images['enable'], "enable");
        $form->addButton($player->getTranslatedMsg("form.button.disable"), $form::IMAGE_TYPE_PATH, $images['disable'], "disable");
        $form->addButton($player->getTranslatedMsg("form.button.back"));
        $player->sendForm($form);
    }

    public function setMysqlScore($ign, int $bool = 1)
    {
        AsyncQueue::submitQuery(new UpdateRowQuery(['scoreboard' => $bool], 'ign', $ign, 'settings'));
    }

    /**
     * @param string $type
     *
     * @return int|string
     */
    private function getPartyData(string $type): int|string
    {
        $session = SessionFactory::getSession($this->player);
        if ($session->hasParty()) {
            $party = $session->getParty();
            return match ($type) {
                "slots" => $party->getSlots(),
                "members" => count($party->getMembers()),
                "leader" => $party->getLeaderName(),
                default => "",
            };
        }
        return "";
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getQueueData(string $type): string
    {
        $player = $this->player;
        if ($player->isQueue()) {
            return $player->queueData[$type];
        }
        return "";
    }
}