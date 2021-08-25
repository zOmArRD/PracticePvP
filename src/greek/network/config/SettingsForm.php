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
    /** @var NetworkPlayer */
    private NetworkPlayer $player;

    public function __construct(NetworkPlayer $player)
    {
        $this->setPlayer(player: $player);
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
                        $player->sendMessage(message: $player->getTranslatedMsg(idMsg: "message.error"));
                        break;
                }
            }
        });
        $images = [
            "language" => "textures/ui/language_glyph_color",
            "close" => "textures/gui/newgui/anvil-crossout"
        ];
        
        $form->setTitle(title: $player->getTranslatedMsg(idMsg: "form.title.settingsform"));
        $form->addButton(text: $player->getTranslatedMsg(idMsg: "form.button.settingsform.changelanguage"), imageType: $form::IMAGE_TYPE_PATH, imagePath: $images['language'], label: "changelanguage");
        $form->addButton(text: $player->getTranslatedMsg(idMsg: "form.button.settingsform.scoreboard"), imageType: $form::IMAGE_TYPE_URL, imagePath: "https://i.ibb.co/TY6MyrN/Hnet-com-image.png", label: "scoreboardsettings");
        $form->addButton(text: "§9Server Settings", imageType: $form::IMAGE_TYPE_URL, imagePath: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT1ZvlkYtyFsEfNG5Cl-Zh3O32hwir7J3LNXA&usqp=CAU", label: "serversettings");
        $form->addButton(text: $player->getTranslatedMsg(idMsg: "form.button.close"), imageType: $form::IMAGE_TYPE_PATH, imagePath:  $images['close'], label: "close");
        $player->sendForm(form: $form);
    }

    public function showServerSettingsForm(): void
    {
        $player = $this->getPlayer();
        $form = new SimpleForm(function (NetworkPlayer $player, $data){
            /* TODO: Finalize */
        });

        $player->sendForm(form: $form);
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