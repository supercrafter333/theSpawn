<?php

namespace supercrafter333\theSpawn\events\other;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

class RemoveWarpEvent extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(protected string $warpName) {}

    /**
     * @return string
     */
    public function getWarpName(): string
    {
        return $this->warpName;
    }
}