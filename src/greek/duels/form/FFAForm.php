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

final class FFAForm extends Manager
{
    public function __construct(NetworkPlayer $player)
    {
        $this->show($player);
    }

    private function show(NetworkPlayer $player): void
    {
        $form = new SimpleForm(function (NetworkPlayer $player, $data) {
            if ($data !== null) {
                if ($data == "close") return;
                try {
                    $config = $this->getConfig();
                    /*TODO*/
                    //$this->changeFFAMode($data, $player->getName());
                    //$player->getSession()->transfer( $config->get('FFA-Server-Name'));
                } catch (Exception) {
                    $player->sendMessage($player->getTranslatedMsg("message.error"));
                }
            }
        });

        $images = [
            "close" => "textures/gui/newgui/anvil-crossout"
        ];

        $config = $this->getConfig();

        $imageType = $config->get("image.form.ffa.type");

        $form->setTitle("§l§7» §1FFA Arenas §l§7«");

        try {
            foreach ($config->get('ffa.modes') as $kits) {
                $form->addButton("§7§l» §r§9" . $kits["Kit"] . " §l§7«" . "\n§r§fJoin to ffa",
                    $imageType,
                    $kits['Icon'],
                    $kits["Kit"]);
            }
        } catch (Exception) {
        }

        $form->addButton($player->getTranslatedMsg("form.button.close"), $form::IMAGE_TYPE_PATH, $images['close'], "close");
        $player->sendForm($form);
    }

    private function getConfig(): Config
    {
        return Settings::getConfig("network.data.yml");
    }
}