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
use greek\duels\Manager;
use greek\Loader;
use greek\modules\form\lib\SimpleForm;
use greek\network\config\Settings;
use greek\network\player\NetworkPlayer;
use greek\network\server\ServerManager;
use greek\network\utils\TextUtils;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;
use const greek\PREFIX;

final class DuelsForm extends Manager
{
    /**
     * @param NetworkPlayer $player
     * @param bool          $isRanked
     */
    public function __construct(NetworkPlayer $player, bool $isRanked = false)
    {
        $this->showForm($player, $isRanked);
    }

    /**
     * @param NetworkPlayer $player
     * @param false         $isRanked
     */
    private function showForm(NetworkPlayer $player, bool $isRanked = false): void
    {
        $config = $this->getConfig();
        $getRanked = ($isRanked == true) ? "Ranked" : "UnRanked";

        $form = new SimpleForm(function (NetworkPlayer $player, $data) use ($config, $getRanked) {

            /* TODO: Make a queue method, and transfer to the server player, also upload the data to MySQL */
            if (isset($data)) {
                if ($data === "close") return;
                $this->updateDownStreamData($player->getName(), $getRanked, $data);
                $player->sendMessage(PREFIX . Loader::getInstance()->getNetwork()->getTextUtils()->replaceColor("{green}You have entered the queue ($data) $getRanked"));
                $player->setIsQueue(true, $data, $getRanked);
                Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $config): void {
                    $player->transferServer($config->get('downstream.server'));
                }), 60);
            }
        });

        $images = [
            "close" => "textures/gui/newgui/anvil-crossout"
        ];

        $form->setTitle("§l§7» §1Queue for $getRanked §l§7«");

        $imageType = $config->get("image.form.duel.type");

        try {
            foreach ($config->get("downstream.modes") as $kits) $form->addButton("§7§l» §r§9" . $kits["Kit"] . " §l§7«" . "\n§r§fJoin in the queue", $imageType, $kits['Icon'], $kits["Kit"]);
        } catch (Exception) {
        }

        $form->addButton($player->getTranslatedMsg("form.button.close"), $form::IMAGE_TYPE_PATH, $images['close'], "close");
        $player->sendForm($form);
    }

    /**
     * @return Config
     */
    private function getConfig(): Config
    {
        return Settings::getConfig("network.data.yml");
    }
}