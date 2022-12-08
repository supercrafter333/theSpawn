<?php

namespace supercrafter333\theSpawn\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use supercrafter333\theSpawn\pwarp\PlayerWarp;

class theSpawnPlayerWarpEvent extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(private PlayerWarp $playerWarp) {}

    /**
     * @return PlayerWarp
     */
    public function getPlayerWarp(): PlayerWarp
    {
        return $this->playerWarp;
    }

    /**
     * @param PlayerWarp $playerWarp
     */
    public function setPlayerWarp(PlayerWarp $playerWarp): void
    {
        $this->playerWarp = $playerWarp;
    }
}