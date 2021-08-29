<?php

namespace supercrafter333\theSpawn\Others;

use pocketmine\world\World;
use pocketmine\utils\Config;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class WarpInfo
 * @package supercrafter333\theSpawn\Others
 */
class WarpInfo
{

    /**
     * @var string
     */
    private string $warpName;

    /**
     * WarpInfo constructor.
     * @param string $warpName
     */
    public function __construct(string $warpName)
    {
        $this->warpName = $warpName;
    }

    /**
     * @param string $warpName
     * @return WarpInfo
     */
    public static function getWarpInfo(string $warpName): WarpInfo
    {
        return new WarpInfo($warpName);
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
    public function exists(): bool
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
    public function getLevel()
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
}