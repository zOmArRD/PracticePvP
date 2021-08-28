<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 10/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\duels\form;

use Exception;
use greek\duels\Manager;
use greek\modules\form\lib\SimpleForm;
use greek\network\config\Settings;
use greek\network\player\NetworkPlayer;
use pocketmine\utils\Config;

class FFAForm extends Manager
{
    public function __construct(NetworkPlayer $player)
    {
        $this->show($player);
    }

    private function show(NetworkPlayer $player): void
    {
        $form = new SimpleForm(callable: function (NetworkPlayer $player, $data) {
            if ($data !== null) {
                if ($data == "close") return;
                try {
                    $config = $this->getConfig();

                    $this->changeFFAMode(mode: $data, playerName: $player->getName());
                    $player->getSession()->transfer(server: $config->get(k: 'FFA-Server-Name'));
                } catch (Exception) {
                    $player->sendMessage(message: $player->getTranslatedMsg(idMsg: "message.error"));
                }
            }
        });

        $images = [
            "close" => "textures/gui/newgui/anvil-crossout"
        ];

        $config = $this->getConfig();

        $imageType = $config->get("image.form.ffa.type");

        $form->setTitle(title: "§l§7» §1FFA Arenas §l§7«");

        try {
            foreach ($config->get(k: 'ffa.modes') as $kits) {
                $form->addButton(text: "§7§l» §r§9" . $kits["Kit"] . " §l§7«" . "\n§r§fJoin to ffa",
                    imageType: $imageType,
                    imagePath: $kits['Icon'],
                    label: $kits["Kit"]);
            }
        } catch (Exception) {
        }

        $form->addButton(text: $player->getTranslatedMsg(idMsg: "form.button.close"), imageType: $form::IMAGE_TYPE_PATH, imagePath: $images['close'], label: "close");
        $player->sendForm(form: $form);
    }

    private function getConfig(): Config
    {
        return Settings::getConfig(archive: "network.data.yml");
    }
}