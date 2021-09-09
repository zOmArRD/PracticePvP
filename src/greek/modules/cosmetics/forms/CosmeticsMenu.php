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

use greek\modules\form\lib\SimpleForm;
use greek\network\player\NetworkPlayer;
use greek\network\utils\TextUtils;

class CosmeticsMenu
{
    /** @var NetworkPlayer */
    public NetworkPlayer $player;

    public function __construct(NetworkPlayer $player)
    {
        $this->player = $player;
        $this->showForm();
    }

    /**
     * @return NetworkPlayer
     */
    public function getPlayer(): NetworkPlayer
    {
        return $this->player;
    }

    public function showForm(): void
    {
        $player = $this->getPlayer();

        $form = new SimpleForm(function (NetworkPlayer $player, $data) {
            if (isset($data)) {
                if ($data == "close") return;

                switch ($data) {
                    case "particles":
                        new ParticlesSelector($player);
                        break;
                }
            }
        });

        $images = [
            "particles" => "textures/ui/icon_staffpicks",
            "close" => "textures/gui/newgui/anvil-crossout",
        ];

        $form->setTitle(TextUtils::replaceColor("{bold}{gray}» {light.purple}Cosmetics Menu §7«"));
        $form->setContent(TextUtils::replaceColor("{yellow}Select which cosmetic you want"));

        $form->addButton("§cParticles", 0, $images['particles'], 'particles');

        $form->addButton($player->getTranslatedMsg("form.button.close"), 0, $images['close'], 'close');
        $player->sendForm($form);
    }
}