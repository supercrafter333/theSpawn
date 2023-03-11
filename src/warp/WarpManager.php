<?php

namespace supercrafter333\theSpawn\warp;

use JsonException;
use pocketmine\entity\Location;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use supercrafter333\theSpawn\LocationHelper;
use supercrafter333\theSpawn\theSpawn;
use function count;
use function microtime;

class WarpManager
{

    /**
     * This method converts old warps to the new warp-format.
     * This is an internal method.
     * @internal
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
     * Converts an old warp to the new warp-format.
     * This is an internal method.
     * @internal
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
            TextFormat::clean($warpName),
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
     * Checks if a warp is set.
     * @param string|Warp $warp
     * @return bool
     */
    public static function existsWarp(string|Warp $warp): bool
    {
        return self::getWarpConfig()->exists(TextFormat::clean(($warp instanceof Warp ? $warp->getName() : $warp)));
    }

    /**
     * Creates a warp if the warp wasn't already created.
     * Returns 'false' if the warp already exists.
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
        $warpArray = ["location" => LocationHelper::locationToString($loc)];
        if ($warp->isPermissionEnabled())
            $warpArray["perm"] = true;
        if ($warp->getIconPath() !== null && $warp->getIconPath() !== "")
            $warpArray["iconPath"] = $warp->getIconPath();

        $cfg->set(TextFormat::clean($warp->getName()), $warpArray);
        $cfg->save();
        return true;
    }

    /**
     * Returns the Warps-class if the warp-name exists and null if it doesn't.
     * @param string $warp
     * @return Warp|null
     */
    public static function getWarp(string $warp): Warp|null
    {
        if (!self::existsWarp($warp))
            return null;

        $warpInfos = self::getWarpConfig()->get($warp, []);
        $location = LocationHelper::stringToLocation($warpInfos["location"]);

        if (!$location->isValid()) {
            self::removeWarp($warp);
            return null;
        }

        return new Warp(
            $location,
            TextFormat::clean($warp),
            (isset($warpInfos["perm"]) ? (bool)$warpInfos["perm"] : false),
            ($warpInfos["iconPath"] ?? null)
        );
    }

    /**
     * Saves an existing warp to the config-file.
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
        $warpArray = ["location" => LocationHelper::locationToString($loc)];
        if ($warp->isPermissionEnabled())
            $warpArray["perm"] = true;
        if ($warp->getIconPath() !== null && $warp->getIconPath() !== "")
            $warpArray["iconPath"] = $warp->getIconPath();

        $cfg->set(TextFormat::clean($warp->getName()), $warpArray);
        $cfg->save();
        return true;
    }

    /**
     * Removes an existing warp.
     * Returns 'false' if the warp isn't created.
     * @param string|Warp $warp
     * @return bool
     * @throws JsonException
     */
    public static function removeWarp(string|Warp $warp): bool
    {
        if (!self::existsWarp($warp))
            return false;

        $cfg = self::getWarpConfig();
        $cfg->remove(TextFormat::clean(($warp instanceof Warp ? $warp->getName() : $warp)));
        $cfg->save();
        return true;
    }
}