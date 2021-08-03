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

namespace greek\modules\form\lib;

use pocketmine\form\Form as IForm;
use pocketmine\Player;

abstract class Form implements IForm
{
    protected array $data = [];

    private $callable;

    public function __construct(?callable $callable)
    {
        $this->callable = $callable;
    }

    public function getCallable(): ?callable
    {
        return $this->callable;
    }

    public function setCallable(?callable $callable): void
    {
        $this->callable = $callable;
    }

    public function handleResponse(Player $player, $data): void
    {
        $this->processData($data);
        $callable = $this->getCallable();
        if ($callable !== null) {
            $callable($player, $data);
        }
    }

    public function processData(&$data): void
    {
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }
}