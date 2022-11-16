<?php

namespace supercrafter333\theSpawn;

use pocketmine\entity\Location;
use pocketmine\world\Position;
use function floatval;

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
            floatval($locArr[0]), floatval($locArr[1]), floatval($locArr[2]),
            theSpawn::getInstance()->checkWorld($locArr[3])),
            theSpawn::getInstance()->checkWorld($locArr[3]),
            floatval($locArr[4]), floatval($locArr[5]));
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

    /**
     * @param array $posArray
     * @return Position|Location|null
     */
    public static function legacyConvertArrayToPosition(array $posArray): Position|Location|null
    {
        if (!isset($posArray["level"])) return null;

        if (isset($posArray["yaw"]) && isset($posArray["pitch"])) return new Location(
            $posArray["X"],
            $posArray["Y"],
            $posArray["Z"],
            theSpawn::getInstance()->checkWorld($posArray["level"]),
            $posArray["yaw"],
            $posArray["pitch"]
        );

        return new Position(
            $posArray["X"],
            $posArray["Y"],
            $posArray["Z"],
            theSpawn::getInstance()->checkWorld($posArray["level"])
        );
    }
}