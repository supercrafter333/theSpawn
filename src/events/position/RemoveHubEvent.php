<?php

namespace supercrafter333\theSpawn\events\position;

use pocketmine\world\Position;
use supercrafter333\theSpawn\events\theSpawnPositionEvent;

class RemoveHubEvent extends theSpawnPositionEvent
{

    public function __construct(Position $position, protected int|null $hubCount = null)
    {
        parent::__construct($position);
    }

    /**
     * @return int|null
     */
    public function getHubCount(): ?int
    {
        return $this->hubCount;
    }
}