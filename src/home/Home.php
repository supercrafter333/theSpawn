<?php

namespace supercrafter333\theSpawn\home;

use pocketmine\entity\Location;
use pocketmine\player\IPlayer;

class Home
{
    //TODO: finish this class

    public function __construct(private IPlayer $player, private string $homeName, private Location $location) {}

    /**
     * @return IPlayer
     */
    public function getPlayer(): IPlayer
    {
        return $this->player;
    }

    /**
     * @return string
     */
    public function getHomeName(): string
    {
        return $this->homeName;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @param Location $location
     */
    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }
}