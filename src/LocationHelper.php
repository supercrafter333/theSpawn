<?php

namespace supercrafter333\theSpawn;

use pocketmine\entity\Location;
use pocketmine\world\Position;

class LocationHelper
{

    /**
     * Converts an array with the format 'x|y|z|worldName|yaw|pitch' to a location-class.
     * @param string $locationString
     * @return Location
     */
    public static function stringToLocation(string $locationString): Location
    {
        $locArr = explode('|', $locationString);
        return Location::fromObject(new Position(
            $locArr[0], $locArr[1], $locArr[2],
            theSpawn::getInstance()->checkWorld($locArr[3])),
            theSpawn::getInstance()->checkWorld($locArr[3]),
            $locArr[4], $locArr[5]);
    }

    /**
     * Converts a location-class to a string that can be saved in a config file.
     * @param Location $location
     * @return string
     */
    public static function locationToString(Location $location): string
    {
        return implode('|', [$location->getX(), $location->getY(), $location->getZ(), $location->getWorld()->getFolderName(),
            $location->getYaw(), $location->getPitch()]);
    }
}