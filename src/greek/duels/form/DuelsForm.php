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

namespace greek\duels\form;

use Exception;
use greek\modules\form\lib\SimpleForm;
use greek\network\config\Settings;
use greek\network\player\NetworkPlayer;

class DuelsForm
{

    public function __construct(NetworkPlayer $player, bool $ranked = false)
    {
        $this->showForm($player, $ranked);
    }

    /**
     * @param NetworkPlayer $player
     * @param false $ranked
     */
    public function showForm(NetworkPlayer $player, bool $ranked = false): void
    {
        $form = new SimpleForm(function (NetworkPlayer $player, $data){

            if (isset($data)) {
                if ($data == "close") return;

                /* TODO: Make a queue method, and transfer to the server player, also upload the data to MySQL */
            }
        });

        $images = [
            "close" => "textures/gui/newgui/anvil-crossout"
        ];

        $getRanked = ($ranked == true) ? "Ranked" : "UnRanked";

        $form->setTitle("§l§7» §1Queue for $getRanked §l§7«");

        $config = Settings::getConfig("duels-available.yml");

        try {
            foreach ($config->get("duels-available") as $kits) {
                $form->addButton("§7§l» §r§9" . $kits["Kit"] . " §l§7«" . "\n§r§fJoin in the queue", 0, "textures/items/{$kits['Icon']}", $kits["Kit"]);
            }
        } catch (Exception $exception) {
        }

        $form->addButton($player->getTranslatedMsg("form.button.close"), 0, $images['close'], "close");
        $player->sendForm($form);
    }
}