<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 20/7/2021
 *
 * Copyright © 2021 Greek Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\modules\cosmetics\forms;

use greek\modules\cosmetics\MCosmetic;
use greek\modules\form\lib\SimpleForm;
use greek\network\player\NetworkPlayer;
use greek\network\utils\TextUtils;
use const greek\PREFIX;

class ParticlesSelector extends MCosmetic
{

    public function __construct(NetworkPlayer $player)
    {
        parent::__construct($player);
        $this->showForm();
    }

    public function showForm(): void
    {
        $player = $this->getPlayer();
        $form = new SimpleForm(function (NetworkPlayer $player, $data) {
            if (isset($data)) {
                if ($data === 'back') {
                    new CosmeticsMenu($this->getPlayer());
                } elseif ($data === "disable") {
                    $this->removeParticles();
                    $player->sendMessage(PREFIX . $this->getMessageUpdated('Particles', 'desactivate'));
                } else {
                    if ($player->hasPermission("cosmetics.particles.$data")) {
                        $this->setParticles($data);
                        $player->sendMessage(PREFIX . $this->getMessageUpdated('particles', 'activate', $data));
                    } else {
                        $player->sendMessage(PREFIX . $this->getMessageUpdated('', 'noperms'));
                    }
                }
            }
        });
        $images = [
            "remove" => "textures/ui/book_trash_default",
            "lava" => "textures/ui/refresh_hover",
            "rain" => "textures/ui/levitation_effect",
            "flame" => "textures/ui/icon_trending",
            "heart" => "textures/ui/health_boost_effect",
            "Witch" => "textures/ui/wither_effect",
            "blood" => "textures/items/redstone_dust",
            "flamerings" => "textures/blocks/fire_0_placeholder"
        ];

        $form->setTitle(TextUtils::replaceColor("{bold}{gray}» {gold}Particles Menu {gray}«"));
        $form->setContent(TextUtils::replaceColor("{yellow}Select which particles you want"));

        $form->addButton(TextUtils::replaceColor("{red}Remove particles"), 0, $images['remove'], 'disable');

        $this->addButton($form, "Heart", [0, $images['heart']], 'heart', 'particles');
        $this->addButton($form, "Flame", [0, $images['flame']], 'flame', 'particles');
        $this->addButton($form, "Dark Flame", [0, $images['flame']], 'darkflame', 'particles');
        $this->addButton($form, "Rain", [0, $images['rain']], 'rain', 'particles');
        $this->addButton($form, "Lava", [0, $images['lava']], 'lava', 'particles');
        $this->addButton($form, "Flame Rings", [0, $images['flamerings']], 'flamerings', 'particles');
        $this->addButton($form, "Blood Helix", [0, $images['blood']], 'bloodhelix', 'particles');
        $this->addButton($form, "Witch Curse", [0, $images['Witch']], 'witchcurse', 'particles');

        $form->addButton($player->getTranslatedMsg("form.button.back"), 0, "", 'back');
        $player->sendForm($form);
    }

}