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
use pocketmine\utils\Config;

class DuelsForm
{
    /**
     * @param NetworkPlayer $player
     * @param bool $isRanked
     */
    public function __construct(NetworkPlayer $player, bool $isRanked = false)
    {
        $this->showForm($player, $isRanked);
    }

    /**
     * @param NetworkPlayer $player
     * @param false $isRanked
     */
    public function showForm(NetworkPlayer $player, bool $isRanked = false): void
    {
        $form = new SimpleForm(function (NetworkPlayer $player, $data){

            /* TODO: Make a queue method, and transfer to the server player, also upload the data to MySQL */
            if (isset($data) && $data == "close") return;
        });

        $images = [
            "close" => "textures/gui/newgui/anvil-crossout"
        ];

        $getRanked = ($isRanked == true) ? "Ranked" : "UnRanked";

        $form->setTitle("§l§7» §1Queue for $getRanked §l§7«");

        $config = $this->getConfig();

        $imageType = $config->get("image.form.duel.type");

        try {
            foreach ($config->get("downstream.modes") as $kits) {
                $form->addButton("§7§l» §r§9" . $kits["Kit"] . " §l§7«" . "\n§r§fJoin in the queue",
                    $imageType,
                    $kits['Icon'],
                    $kits["Kit"]);
            }
        } catch (Exception) {
        }

        $form->addButton($player->getTranslatedMsg("form.button.close"),
            $form::IMAGE_TYPE_PATH,
            $images['close'],
            "close");
        $player->sendForm($form);
    }

    private function getConfig(): Config
    {
        return Settings::getConfig("network.data.yml");
    }
}