<?php

namespace supercrafter333\theSpawn\events\other;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use supercrafter333\theSpawn\warp\Warp;

class EditWarpEvent extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(private Warp $warp) {}

    /**
     * @return Warp
     */
    public function getWarp(): Warp
    {
        return $this->warp;
    }

    /**
     * @param Warp $warp
     */
    public function setWarp(Warp $warp): void
    {
        $this->warp = $warp;
    }
}