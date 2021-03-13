<?php


namespace supercrafter333\theSpawn\Others;


use pocketmine\Player;
use pocketmine\utils\Config;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class HomeInfo
 * @package supercrafter333\theSpawn\Others
 */
class HomeInfo
{
    #####################################################
    ########All other functions in class theSpawn########
    #####################################################

    /**
     * @var Player
     */
    public $player;
    /**
     * @var string
     */
    public $homeName;

    /**
     * HomeInfo constructor.
     * @param Player $player
     * @param string $homeName
     */
    public function __construct(Player $player, string $homeName)
    {
        $this->player = $player;
        $this->homeName = $homeName;
    }

    /**
     * @return Config
     */
    public function getHomeCfg(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "homes/" . $this->player->getName() . ".yml", Config::YAML);
    }

    /**
     * @return bool
     */
    public function existsHome(): bool
    {
        return $this->getHomeCfg()->exists($this->homeName);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if ($this->existsHome() == true) {
            return $this->getHomeCfg()->get($this->homeName)["homeName"];
        }
        return "";
    }

    /**
     * @return false|mixed
     */
    public function getX()
    {
        if ($this->existsHome() == true) {
            return $this->getHomeCfg()->get($this->homeName)["X"];
        }
        return false;
    }

    /**
     * @return false|mixed
     */
    public function getY()
    {
        if ($this->existsHome() == true) {
            return $this->getHomeCfg()->get($this->homeName)["Y"];
        }
        return false;
    }

    /**
     * @return false|mixed
     */
    public function getZ()
    {
        if ($this->existsHome() == true) {
            return $this->getHomeCfg()->get($this->homeName)["Z"];
        }
        return false;
    }

    /**
     * @return false|mixed
     */
    public function getLevelName()
    {
        if ($this->existsHome() == true) {
            return $this->getHomeCfg()->get($this->homeName)["level"];
        }
        return false;
    }

    /**
     * @return false|\pocketmine\level\Level|null
     */
    public function getLevel()
    {
        if ($this->existsHome() == true) {
            $lvlName = $this->getHomeCfg()->get($this->homeName)["level"];
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
    #####################################################
    ########All other functions in class theSpawn########
    #####################################################
}