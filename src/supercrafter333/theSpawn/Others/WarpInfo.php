<?php

namespace supercrafter333\theSpawn\Others;

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
    private $warpName;

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
    public static function getWarpInfo(string $warpName)
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
        if ($this->getWarpCfg()->exists($this->warpName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return float
     */
    public function getX(): float
    {
        if ($this->exists() == true) {
            $X = $this->getWarpCfg()->get($this->warpName)["X"];
            return $X;
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
            $Y = $this->getWarpCfg()->get($this->warpName)["Y"];
            return $Y;
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
            $Z = $this->getWarpCfg()->get($this->warpName)["Z"];
            return $Z;
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
            $lvlName = $this->getWarpCfg()->get($this->warpName)["level"];
            return $lvlName;
        } else {
            return false;
        }
    }

    /**
     * @return false|\pocketmine\level\Level|null
     */
    public function getLevel()
    {
        if ($this->exists() == true) {
            $lvlName = $this->getWarpCfg()->get($this->warpName)["level"];
            if (theSpawn::getInstance()->getServer()->isLevelGenerated($lvlName) && theSpawn::getInstance()->getServer()->isLevelLoaded($lvlName)) {
                return theSpawn::getInstance()->getServer()->getLevelByName($lvlName);
            } elseif (theSpawn::getInstance()->getServer()->isLevelGenerated($lvlName)) {
                theSpawn::getInstance()->getServer()->loadLevel($lvlName);
                return theSpawn::getInstance()->getServer()->getLevelByName($lvlName);
            }
            return false;
        }
        return false;
    }
}