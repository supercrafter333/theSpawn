<?php

namespace supercrafter333\theSpawn\Events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\world\World;

abstract class theSpawnWorldEvent extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(protected World $world) {}

    /**
     * @return World
     */
    public function getWorld(): World
    {
        return $this->world;
    }
}