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
        $form->addButton($player->getTranslatedMsg("form.button.settingsform.changelanguage"), $form::IMAGE_TYPE_PATH, $images['language'], "changelanguage");
        $form->addButton($player->getTranslatedMsg("form.button.settingsform.scoreboard"), $form::IMAGE_TYPE_URL, "https://i.ibb.co/TY6MyrN/Hnet-com-image.png", "scoreboardsettings");
        $form->addButton("§9Server Settings", $form::IMAGE_TYPE_URL, "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT1ZvlkYtyFsEfNG5Cl-Zh3O32hwir7J3LNXA&usqp=CAU", "serversettings");
        $form->addButton($player->getTranslatedMsg("form.button.close"), $form::IMAGE_TYPE_PATH, $images['close'], "close");
        $player->sendForm($form);
    }

    public function showServerSettingsForm(): void
    {
        $player = $this->getPlayer();
        $form = new SimpleForm(function (NetworkPlayer $player, $data){
            /* TODO: Finalize */
        });

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