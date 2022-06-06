<?php

namespace supercrafter333\theSpawn\events\world;

use pocketmine\entity\Location;
use pocketmine\world\Position;
use pocketmine\world\World;
use supercrafter333\theSpawn\events\theSpawnWorldEvent;

class RemoveSpawnEvent extends theSpawnWorldEvent
{

    public function __construct(World $world, protected Location|Position $position)
    {
        parent::__construct($world);
    }

    /**
     * @return Location|Position
     */
    public function getPosition(): Position|Location
    {
        return $this->position;
    }
}