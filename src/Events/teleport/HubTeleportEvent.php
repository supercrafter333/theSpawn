<?php

namespace supercrafter333\theSpawn\Events\teleport;

use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\world\Position;
use supercrafter333\theSpawn\Events\theSpawnPlayerTeleportEvent;

class HubTeleportEvent extends theSpawnPlayerTeleportEvent
{

    public function __construct(Player $player, Position|Location $origin, protected Position|Location $hubPosition)
    {
        parent::__construct($player, $origin, $hubPosition);
    }

    /**
     * @return Position|Location
     */
    public function getHubPosition(): Position|Location
    {
        return $this->hubPosition;
    }
}