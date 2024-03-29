<?php

namespace supercrafter333\theSpawn\pwarp;

use JsonException;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionAttachmentInfo;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use supercrafter333\theSpawn\ConfigManager;
use supercrafter333\theSpawn\LocationHelper;
use supercrafter333\theSpawn\theSpawn;
use function strtolower;

class PlayerWarpManager
{

    /**
     * @return Config
     */
    public static function getConfig(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "playerwarpList.json", Config::JSON);
    }

    /**
     * @param string $warpName
     * @return bool
     */
    public static function exists(string $warpName): bool
    {
        return self::getConfig()->exists(TextFormat::clean($warpName));
    }

    /**
     * @param PlayerWarp $warp
     * @return bool
     * @throws JsonException
     */
    public static function savePlayerWarp(PlayerWarp $warp): bool
    {
        if (!self::exists($warp->getName())) return false;

        $cfg = self::getConfig();
        $cfg->set(TextFormat::clean($warp->getName()), [
            "location" => LocationHelper::locationToString($warp->getLocation()),
            "owner" => $warp->getOwnerName(),
            "iconPath" => $warp->getIconPath()
        ]);
        $cfg->save();
        return true;
    }

    /**
     * @param PlayerWarp $warp
     * @return bool
     * @throws JsonException
     */
    public static function createPlayerWarp(PlayerWarp $warp): bool
    {
        if (self::exists($warp->getName())) return false;

        $warpArray = [
            "location" => LocationHelper::locationToString($warp->getLocation()),
            "owner" => $warp->getOwnerName()
        ];

        if ($warp->getIconPath() !== null && $warp->getIconPath() !== "")
            $warpArray["iconPath"] = $warp->getIconPath();

        $cfg = self::getConfig();
        $cfg->set(TextFormat::clean($warp->getName()), $warpArray);
        $cfg->save();
        return true;
    }

    /**
     * @param string $warpName
     * @return bool
     * @throws JsonException
     */
    public static function removePlayerWarp(string $warpName): bool
    {
        if (!self::exists($warpName)) return false;

        $cfg = self::getConfig();
        $cfg->remove(TextFormat::clean($warpName));
        $cfg->save();
        return true;
    }

    /**
     * @param string $warpName
     * @return PlayerWarp|null
     */
    public static function getPlayerWarp(string $warpName): PlayerWarp|null
    {
        if (!self::exists($warpName)) return null;

        $warpInfos = self::getConfig()->get(TextFormat::clean($warpName), []);
        $location = LocationHelper::stringToLocation($warpInfos["location"]);

        if (!$location->isValid()) {
            self::removePlayerWarp($warpName);
            return null;
        }

        return new PlayerWarp($location,
            TextFormat::clean($warpName),
            $warpInfos["owner"],
            ($warpInfos["iconPath"] ?? null));
    }

    /**
     * @param string $playerName
     * @return PlayerWarp[]|empty[]
     */
    public static function getPlayerWarpsOf(string $playerName): array
    {
        $warps = [];

        foreach (self::getConfig()->getAll() as $warpName => $val)
            if (strtolower($val["owner"]) === strtolower($playerName) && ($warp = self::getPlayerWarp($warpName)) instanceof PlayerWarp)
                $warps[] = $warp;

        return $warps;
    }

    /**
     * @param Player $player
     * @return int
     */
    public static function getMaxPlayerWarpCount(Player $player): int
    {
        if($player->hasPermission("theSpawn.pwarps.unlimited") || !ConfigManager::getInstance()->useMaxPlayerWarpPermissions()) return PHP_INT_MAX;

		$perms = array_map(fn(PermissionAttachmentInfo $attachment) => [$attachment->getPermission(), $attachment->getValue()], $player->getEffectivePermissions());
		$perms = array_merge(PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_USER)->getChildren(), $perms);
		$perms = array_filter($perms, function(string $name) : bool {
			return (str_starts_with($name, "theSpawn.pwarps."));
		}, ARRAY_FILTER_USE_KEY);
		if(count($perms) === 0)
			return 0;
		krsort($perms, SORT_FLAG_CASE | SORT_NATURAL);
		/**
		 * @var string $name
		 * @var Permission $perm
		 */
		foreach($perms as $name => $perm) {
			$maxHomes = mb_substr($name, 16);
			if(is_numeric($maxHomes)) {
				return (int) $maxHomes;
			}
		}
		return 0;
    }
}