<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 2/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\network\config;

use greek\modules\form\lib\CustomForm;
use greek\modules\form\lib\SimpleForm;
use greek\network\player\NetworkPlayer;
use greek\network\server\ServerManager;
use pocketmine\level\Level;

class SettingsForm
{
    /** @var NetworkPlayer */
    private NetworkPlayer $player;

    public function __construct(NetworkPlayer $player)
    {
        $this->setPlayer($player);
        $this->showForm();
    }
    
    public function showForm(): void
    {
        $player = $this->getPlayer();

        $form = new SimpleForm(function (NetworkPlayer $player, $data){
            if (isset($data)) {
                if ($data === "close") return;

                switch ($data) {
                    case "changelanguage":
                        $player->getLangSession()->showForm();
                        break;
                    case "scoreboardsettings":
                        $player->getScoreboardSession()->showForm();
                        break;
                    case "serversettings":
                        $this->showServerSettingsForm();
                        break;
                    case "timechanger":
                        $this->showTimeChanger();
                        break;
                    default:
                        $player->sendMessage($player->getTranslatedMsg("message.error"));
                        break;
                }
            }
        });
        $images = [
            "language" => "textures/ui/language_glyph_color",
            "close" => "textures/gui/newgui/anvil-crossout",
            "time" => "textures/ui/icon_timer"
        ];
        
        $form->setTitle($player->getTranslatedMsg("form.title.settingsform"));
        $form->addButton($player->getTranslatedMsg("form.button.settingsform.changelanguage"), $form::IMAGE_TYPE_PATH, $images['language'], "changelanguage");
        $form->addButton($player->getTranslatedMsg("form.button.settingsform.scoreboard"), $form::IMAGE_TYPE_URL, "https://i.ibb.co/TY6MyrN/Hnet-com-image.png", "scoreboardsettings");
        $form->addButton($player->getTranslatedMsg("form.button.settingsform.timechanger"), $form::IMAGE_TYPE_PATH, $images['time'], "timechanger");
        if ($player->isOp()) {
            $form->addButton("§l§9Server Settings", $form::IMAGE_TYPE_URL, "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT1ZvlkYtyFsEfNG5Cl-Zh3O32hwir7J3LNXA&usqp=CAU", "serversettings");
        }
        $form->addButton($player->getTranslatedMsg("form.button.close"), $form::IMAGE_TYPE_PATH, $images['close'], "close");
        $player->sendForm($form);
    }

    public function showServerSettingsForm(): void
    {
        $player = $this->getPlayer();
        $form = new SimpleForm(function (NetworkPlayer $player, $data){
            if (isset($data)) {
                switch ($data) {
                    case "scoreboard":
                        $this->showFormScoreboard();
                        break;
                    case "reloadservers":
                        ServerManager::reloadServers();
                        break;
                    default:
                        $this->showForm();
                        break;
                }
            }
        });

        $form->setTitle("§l§6Server Settings");
        $form->setContent("In this menu you can configure certain settings.");

        //$form->addButton("§bSet Hub" . "\n" . "§7In your current position", $form::IMAGE_TYPE_PATH, "", "sethub");
        $form->addButton("§bScoreboard Viewer" . "\n" . "§7Look at the performance.", $form::IMAGE_TYPE_PATH, "", "scoreboard");
        $form->addButton("§bReload Servers" . "\n" . "§7from database", $form::IMAGE_TYPE_PATH, "", "reloadservers");
        $form->addButton($player->getTranslatedMsg("form.button.back"));

        $player->sendForm($form);
    }

    private function showFormScoreboard()
    {
        $player = $this->getPlayer();
        $form = new CustomForm(function (NetworkPlayer $player, array $data = null) {

            if (isset($data)) {
                if ($data[0] == true) {
                    if (!$player->isPerformanceViewer()) {
                        $player->setIsPerformanceViewer(true);
                        $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.scoreboard.updated"));
                    } else {
                        $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.cantupdate"));
                    }
                } else {
                    if ($player->isPerformanceViewer()) {
                        $player->setIsPerformanceViewer(false);
                        $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.scoreboard.updated"));
                    } else {
                        $player->sendMessage(Settings::$prefix . $player->getTranslatedMsg("message.cantupdate"));
                    }
                }
            }
        });

        $value = $player->isPerformanceViewer();

        $form->setTitle("§6Scoreboard Viewer");
        $form->addToggle("Performance Viewer", $value);

        $player->sendForm($form);
    }
    
    private function showTimeChanger()
    {
        $player = $this->getPlayer();
        $form = new SimpleForm(function (NetworkPlayer $player, $data) {
            if (isset($data)) {
                switch ($data) {
                    case "back":
                        $this->showForm();
                        break;
                    case "day":
                        $player->changeTime(Level::TIME_DAY);
                        break;
                    case "night":
                        $player->changeTime(Level::TIME_NIGHT);
                        break;
                }
            }
        });

        $images = ["day" => "textures/ui/time_2day", "night" => "textures/ui/time_6midnight"];

        $form->setTitle($player->getTranslatedMsg("form.title.timechanger"));
        $form->setContent($player->getTranslatedMsg("form.content.timechanger"));

        $form->addButton($player->getTranslatedMsg("form.button.day") . "\n" . $player->getTranslatedMsg("form.button.day.subtitle"), 0, $images['day'], 'day');
        $form->addButton($player->getTranslatedMsg("form.button.night") . "\n" . $player->getTranslatedMsg("form.button.night.subtitle"), 0, $images['day'], 'night');

        $form->addButton($player->getTranslatedMsg("form.button.back"), 0,"", "back");
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