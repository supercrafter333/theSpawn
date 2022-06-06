<?php

namespace supercrafter333\theSpawn\events\position;

use pocketmine\world\Position;
use supercrafter333\theSpawn\events\theSpawnPositionEvent;

class RemoveWarpEvent extends theSpawnPositionEvent
{

    public function __construct(Position $position, protected string $warpName)
    {
        parent::__construct($position);
    }

    /**
     * @return string
     */
    public function getWarpName(): string
    {
        return $this->warpName;
    }
}