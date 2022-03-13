<?php

namespace supercrafter333\theSpawn\Others;

use pocketmine\permission\DefaultPermissionNames;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionAttachmentInfo;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\utils\Config;
use supercrafter333\theSpawn\theSpawn;
use function in_array;

/**
 * Class WarpInfo
 * @package supercrafter333\theSpawn\Others
 */
class WarpInfo
{

    /**
     * WarpInfo constructor.
     * @param string $warpName
     */
    public function __construct(private string $warpName) {}

    /**
     * @param string $warpName
     * @return WarpInfo|null
     */
    public static function getWarpInfo(string $warpName): ?WarpInfo
    {
        return theSpawn::getInstance()->existsWarp($warpName) ? new WarpInfo($warpName) : null;
    }

    /**
     * @return Config
     */
    public function getWarpCfg(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "warps.yml", Config::YAML);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->warpName;
    }

    /**
     * @return bool
     */
    private function exists(): bool
    {
        return $this->getWarpCfg()->exists($this->warpName);
    }

    /**
     * @return float
     */
    public function getX(): float
    {
        if ($this->exists() == true) {
            return $this->getWarpCfg()->get($this->warpName)["X"];
        } else {
            return false;
        }
    }

    /**
     * @return float
     */
    public function getY(): float
    {
        if ($this->exists() == true) {
            return $this->getWarpCfg()->get($this->warpName)["Y"];
        } else {
            return false;
        }
    }

    /**
     * @return float
     */
    public function getZ(): float
    {
        if ($this->exists() == true) {
            return $this->getWarpCfg()->get($this->warpName)["Z"];
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getLevelName(): string
    {
        if ($this->exists() == true) {
            return $this->getWarpCfg()->get($this->warpName)["level"];
        } else {
            return false;
        }
    }

    /**
     * @return false|World|null
     */
    public function getWorld(): false|null|World
    {
        if ($this->exists() == true) {
            $lvlName = $this->getWarpCfg()->get($this->warpName)["level"];
            if (theSpawn::getInstance()->getServer()->getWorldManager()->isWorldGenerated($lvlName) && theSpawn::getInstance()->getServer()->getWorldManager()->isWorldLoaded($lvlName)) {
                return theSpawn::getInstance()->getServer()->getWorldManager()->getWorldByName($lvlName);
            } elseif (theSpawn::getInstance()->getServer()->getWorldManager()->isWorldGenerated($lvlName)) {
                theSpawn::getInstance()->getServer()->getWorldManager()->loadWorld($lvlName);
                return theSpawn::getInstance()->getServer()->getWorldManager()->getWorldByName($lvlName);
            }
            return false;
        }
        return false;
    }

    public function getIconPath(): ?string
    {
        $path = $this->getWarpCfg()->getNested($this->warpName . ".iconPath");
        return ($path === null || $path === "") ? null : $path;
    }

    public function getPermission(): ?string
    {

        $perm = $this->getWarpCfg()->getNested($this->warpName . ".perm");

        if ($perm === null || $perm === "" || $perm === false) return null;

        if ($perm) {

            $perm = "theSpawn.warp." . $this->getName();

            if (PermissionManager::getInstance()->getPermission($perm) instanceof Permission) return $perm;

            $op = PermissionManager::getInstance()->getPermission(DefaultPermissionNames::GROUP_OPERATOR);
            $console = PermissionManager::getInstance()->getPermission(DefaultPermissionNames::GROUP_CONSOLE);

            DefaultPermissions::registerPermission(new Permission($perm, "Warp permission"), [$op, $console]);
            PermissionManager::getInstance()->getPermission("theSpawn.warp.admin")->addChild($perm, true);
            return $perm;
        }
        return null;
    }

    /*public function hasPermission(Player $player): bool //Code implementation from MyPlot (by jasonwynn10)
    {
        if ($this->getPermission() === null || $player->hasPermission("theSpawn.warp.admin")) return true;

		$perms = array_map(fn(PermissionAttachmentInfo $attachment) => [$attachment->getPermission(), $attachment->getValue()], $player->getEffectivePermissions());
		$perms = array_merge(PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_USER)->getChildren(), $perms);
		$perms = array_filter($perms, function(string $name) : bool {
			return (str_starts_with($name, "theSpawn.warp."));
		}, ARRAY_FILTER_USE_KEY);
		if(count($perms) === 0)
			return 0;
		krsort($perms, SORT_FLAG_CASE | SORT_NATURAL);

        if (in_array($this->getPermission(), $perms)) return true;

		return false;
    }*/

    public function hasPermission(Player $player): bool
    {
        if (($permission = $this->getPermission()) === null || $player->hasPermission("theSpawn.warp.admin")) return true;

        $perms = array_map(fn(PermissionAttachmentInfo $attachment) => [$attachment->getPermission(), $attachment->getValue()], $player->getEffectivePermissions());
		$perms = array_merge(PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_USER)->getChildren(), $perms);
		if(count($perms) === 0)
			return false;
		/**
		 * @var string $name
		 * @var Permission $perm
		 */
		foreach($perms as $name => $perm) {
			if ($name == $permission) return true;
		}
        return false;
    }
}