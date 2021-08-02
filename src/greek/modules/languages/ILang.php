<?php
/*
 * Created by PhpStorm
 *
 * User: zOmArRD
 * Date: 1/8/2021
 *
 * Copyright © 2021 - All Rights Reserved.
 */
declare(strict_types=1);

namespace greek\modules\languages;

use pocketmine\Player;

abstract class ILang
{
    static abstract public function setStringValue(string $key, string $value): void;

    static abstract public function setLanguage(string $language, bool $safe): void;

    static abstract public function applyPlayerLanguage(): void;

    static abstract public function getPlayerLanguage(): string;

    static abstract public function getString(string $id): string;

    static abstract public function replaceVars(string $msg, array $array): string;

    static abstract public function showForm(): void;
}