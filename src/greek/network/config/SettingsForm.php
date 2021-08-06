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

use greek\modules\form\lib\SimpleForm;
use greek\network\player\NetworkPlayer;
use greek\network\scoreboard\Scoreboard;

class SettingsForm
{
    public function __construct(NetworkPlayer $player)
    {
        $this->showForm($player);
    }
    
    public function showForm(NetworkPlayer $player): void
    {
        $form = new SimpleForm(function (NetworkPlayer $player, $data){
            if (isset($data)) {
                if ($data === "close") return;
                
                switch ($data) {
                    case "changelanguage":
                        $player->getLangClass()->showForm();
                        break;
                    case "scoreboardsettings":
                        Scoreboard::showForm($player);
                        break;
                    default:
                        $player->sendMessage($player->getTranslatedMsg("message.error"));
                        break;
                }
            }
        });
        $images = [
            "language" => "textures/ui/language_glyph_color",
            "close" => "textures/gui/newgui/anvil-crossout"
        ];
        
        $form->setTitle($player->getTranslatedMsg("form.title.settingsform"));
        $form->addButton($player->getTranslatedMsg("form.button.settingsform.changelanguage"), 0, $images['language'], "changelanguage");
        $form->addButton($player->getTranslatedMsg("form.button.settingsform.scoreboard"), 1, "https://i.ibb.co/TY6MyrN/Hnet-com-image.png", "scoreboardsettings");
        $form->addButton($player->getTranslatedMsg("form.button.close"), 0, $images['close'], "close");
        
        $player->sendForm($form);
    }
}