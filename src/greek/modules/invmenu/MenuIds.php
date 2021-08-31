<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 31/8/2021
 *
 *  Copyright © 2021 - All Rights Reserved.
 */

declare(strict_types=1);

namespace greek\modules\invmenu;

interface MenuIds
{
    /** @var string  */
    public const TYPE_CHEST = "invmenu:chest", TYPE_DOUBLE_CHEST = "invmenu:double_chest", TYPE_HOPPER = "invmenu:hopper";
}