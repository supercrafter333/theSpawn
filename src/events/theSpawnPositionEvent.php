<?php

namespace supercrafter333\theSpawn\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\world\Position;

abstract class theSpawnPositionEvent extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(protected Position $position) {}

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position;
    }
}