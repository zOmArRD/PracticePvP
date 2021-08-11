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
        $this->showForm($player);
    }

    function showForm(NetworkPlayer $player): void
    {
        $form = new SimpleForm(function (NetworkPlayer $player, $data){
            if ($data !== null) {
                if ($data == "close") return;
                try {
                    $config = $this->getFFAConfig();

                    $this->changeFFAMode($data, $player->getName());
                    $player->getSession()->transfer($config->get('FFA-Server-Name'));
                } catch (Exception $exception) {
                    $player->sendMessage($player->getTranslatedMsg("message.error"));
                }
            }
        });

        $images = [
            "close" => "textures/gui/newgui/anvil-crossout"
        ];

        $config = $this->getFFAConfig();

        $form->setTitle("§l§7» §1FFA Arenas §l§7«");

        try {
            foreach ($config->get('ffa-available') as $kits) {
                $form->addButton("§7§l» §r§9" . $kits["Kit"] . " §l§7«" . "\n§r§fJoin to ffa", 0, "textures/items/{$kits['Icon']}", $kits["Kit"]);
            }
        } catch (Exception $exception) {
        }

        $form->addButton($player->getTranslatedMsg("form.button.close"), 0, $images['close'], "close");
        $player->sendForm($form);
    }

    function getFFAConfig(): Config
    {
        return Settings::getConfig("ffa-available.yml");
    }
}