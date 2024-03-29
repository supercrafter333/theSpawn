<?php

namespace supercrafter333\theSpawn\events;

use pocketmine\entity\Location;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\world\Position;

abstract class theSpawnPlayerTeleportEvent extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(protected readonly Player $player, protected Position|Location $origin, protected Position|Location $target) {}

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return Position|Location
     */
    public function getOrigin(): Position|Location
    {
        return $this->origin;
    }

    /**
     * @return Position|Location
     */
    public function getTarget(): Position|Location
    {
        return $this->target;
    }

    /**
     * @param Location|Position $target
     */
    public function setTarget(Position|Location $target): void
    {
        $this->target = $target;
    }
}