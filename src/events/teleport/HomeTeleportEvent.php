<?php

namespace supercrafter333\theSpawn\events\teleport;

use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\world\Position;
use supercrafter333\theSpawn\events\theSpawnPlayerTeleportEvent;

class HomeTeleportEvent extends theSpawnPlayerTeleportEvent
{

    public function __construct(Player $player, Position|Location $origin, Position|Location $target, protected string $homeName)
    {
        parent::__construct($player, $origin, $target);
    }

    /**
     * @return string
     */
    public function getHomeName(): string
    {
        return $this->homeName;
    }
}