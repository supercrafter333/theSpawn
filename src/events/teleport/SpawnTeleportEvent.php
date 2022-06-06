<?php

namespace supercrafter333\theSpawn\events\teleport;

use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;
use supercrafter333\theSpawn\events\theSpawnPlayerTeleportEvent;

class SpawnTeleportEvent extends theSpawnPlayerTeleportEvent
{

    public function __construct(Player $player, Position|Location $origin, protected Position|Location $spawnPosition, private World $world)
    {
        parent::__construct($player, $origin, $spawnPosition);
    }

    /**
     * @return Location|Position
     */
    public function getSpawnPosition(): Position|Location
    {
        return $this->spawnPosition;
    }

    /**
     * @return World
     */
    public function getWorld(): World
    {
        return $this->world;
    }
}