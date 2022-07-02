<?php

namespace supercrafter333\theSpawn\warp;

use JsonException;
use pocketmine\entity\Location;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use supercrafter333\theSpawn\theSpawn;
use function count;
use function explode;
use function implode;
use function microtime;

class WarpManager
{

    /**
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
     * @param Location $location
     * @return string
     */
    public static function locationToString(Location $location): string
    {
        return implode('|', [$location->getX(), $location->getY(), $location->getZ(), $location->getWorld()->getFolderName(),
            $location->getYaw(), $location->getPitch()]);
    }

    /**
     * @return void
     * @throws JsonException
     */
    public static function migrateOldWarps(): void
    {
        $warps = [];

        foreach (self::getWarpConfig()->getAll() as $warp => $values)
            if (isset($values["warpName"]) || isset($values["level"]))
                $warps[$warp] = $values;

        if (count($warps, COUNT_RECURSIVE) >= 1) {
            $logger = theSpawn::getInstance()->getLogger();
            $mt = microtime(true);

            $logger->warning("Migrating warps...");

            foreach ($warps as $warp => $values) {
                $logger->debug("Migrating warp: §b" . $warp . "§r   ...");
                if (self::migrateWarp($warp, $values))
                    $logger->debug("Successfully migrated warp: §b" . $warp);
                else
                    $logger->error("§cFailed to migrate warp:§b" .  $warp);
            }

            $logger->warning("Successfully migrated warps in §b" . round((microtime(true) - $mt), 3) . "sec.");
        }
    }

    /**
     * @param string $warpName
     * @param array $warpInfos
     * @return bool
     * @throws JsonException
     */
    protected static function migrateWarp(string $warpName, array $warpInfos): bool
    {
        if (isset($warpInfos["location"]))
            return false;

        $cfg = self::getWarpConfig();
        $cfg->remove($warpName);
        $cfg->save();

        $x = $warpInfos["X"];
        $y = $warpInfos["Y"];
        $z = $warpInfos["Z"];
        $worldName = $warpInfos["level"];
        $yaw = $warpInfos["yaw"] ?? 0.0;
        $pitch = $warpInfos["pitch"] ?? 0.0;
        if (!($world = theSpawn::getInstance()->checkWorld($worldName)) instanceof World)
            return false;
        $perm = isset($warpInfos["perm"]) ? (bool)$warpInfos["perm"] : false;
        $iconPath = $warpInfos["iconPath"] ?? null;

        self::createWarp(new Warp(
            Location::fromObject(new Position($x, $y, $z, $world), $world, $yaw, $pitch),
            $warpName,
            $perm,
            $iconPath
        ));
        return true;
    }

    /**
     * @return Config
     */
    public static function getWarpConfig(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "warps.yml", Config::YAML);
    }

    /**
     * @param string|Warp $warp
     * @return bool
     */
    public static function existsWarp(string|Warp $warp): bool
    {
        return self::getWarpConfig()->exists(($warp instanceof Warp ? $warp->getName() : $warp));
    }

    /**
     * @param Warp $warp
     * @return bool
     * @throws JsonException
     */
    public static function createWarp(Warp $warp): bool
    {
        if (self::existsWarp($warp))
            return false;

        $cfg = self::getWarpConfig();

        $loc = $warp->getLocation();
        $warpArray = ["location" => self::locationToString($loc)];
        if ($warp->isPermissionEnabled())
            $warpArray["perm"] = true;
        if ($warp->getIconPath() !== null && $warp->getIconPath() !== "")
            $warpArray["iconPath"] = $warp->getIconPath();

        $cfg->set($warp->getName(), $warpArray);
        $cfg->save();
        return true;
    }

    /**
     * @param string $warp
     * @return Warp|null
     */
    public static function getWarp(string $warp): Warp|null
    {
        if (!self::existsWarp($warp))
            return null;

        $warpInfos = self::getWarpConfig()->get($warp, []);

        return new Warp(
            self::stringToLocation($warpInfos["location"]),
            $warp,
            (isset($warpInfos["perm"]) ? (bool)$warpInfos["perm"] : false),
            ($warpInfos["iconPath"] ?? null)
        );
    }

    /**
     * @param Warp $warp
     * @return bool
     * @throws JsonException
     */
    public static function saveWarp(Warp $warp): bool
    {
        if (!self::existsWarp($warp))
            return false;

        $cfg = self::getWarpConfig();

        $loc = $warp->getLocation();
        $warpArray = ["location" => self::locationToString($loc)];
        if ($warp->isPermissionEnabled())
            $warpArray["perm"] = true;
        if ($warp->getIconPath() !== null && $warp->getIconPath() !== "")
            $warpArray["iconPath"] = $warp->getIconPath();

        $cfg->set($warp->getName(), $warpArray);
        $cfg->save();
        return true;
    }

    /**
     * @param string|Warp $warp
     * @return bool
     * @throws JsonException
     */
    public static function removeWarp(string|Warp $warp): bool
    {
        if (!self::existsWarp($warp))
            return false;

        $cfg = self::getWarpConfig();
        $cfg->remove(($warp instanceof Warp ? $warp->getName() : $warp));
        $cfg->save();
        return true;
    }
}