<?php

namespace supercrafter333\theSpawn\pwarp;

use JsonException;
use pocketmine\entity\Location;

class PlayerWarp
{

    /**
     * @param Location $location
     * @param string $warpName
     * @param string $ownerName
     * @param string|null $iconPath
     */
    public function __construct(private Location $location,
                                private readonly string $warpName, private readonly string $ownerName,
                                private string|null $iconPath = null)
    {}

    /**
     * @return string
     */
    public function getWarpName(): string
    {
        return $this->warpName;
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->warpName;
    }

    /**
     * @return string
     */
    public function getOwnerName(): string
    {
        return $this->ownerName;
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
     * @return string|null
     */
    public function getIconPath(): ?string
    {
        return $this->iconPath;
    }

    /**
     * @param string|null $iconPath
     */
    public function setIconPath(?string $iconPath): void
    {
        $this->iconPath = $iconPath;
    }

    /**
     * @throws JsonException
     */
    public function save(): void
    {
        PlayerWarpManager::savePlayerWarp($this);
    }
}