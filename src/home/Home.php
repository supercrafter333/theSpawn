<?php

namespace supercrafter333\theSpawn\home;

use JsonException;
use pocketmine\entity\Location;
use pocketmine\player\IPlayer;

class Home
{

    /**
     * @param IPlayer $player
     * @param string $homeName
     * @param Location $location
     */
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
     * @return string
     */
    public function getName(): string
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

    /**
     * @return void
     * @throws JsonException
     */
    public function save(): void
    {
        HomeManager::saveHome($this);
    }
}