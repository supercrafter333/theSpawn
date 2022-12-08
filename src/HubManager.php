<?php

namespace supercrafter333\theSpawn;

use JsonException;
use pocketmine\entity\Location;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;
use pocketmine\world\World;
use function implode;

class HubManager
{
    use SingletonTrait;

    public function __construct()
    { self::setInstance($this); }

    public function getRandomHubList(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "theRandHubs.yml", Config::YAML);
    }

    public function getHubConfig(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "theHub.yml", Config::YAML);
    }

    public function getRandomHubsConfig(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "theRandHubs.yml", Config::YAML);
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $z
     * @param World $world
     * @param float|null $yaw
     * @param float|null $pitch
     * @param int|null $count
     * @throws JsonException
     */
    public function setHub(float $x, float $y, float $z, World $world, float $yaw = null, float $pitch = null, int $count = null): void
    {
        $config = theSpawn::getInstance()->getConfig();
        $hub = $this->getHubConfig();
        $randHub = $this->getRandomHubsConfig();
        $hubcoords = ["hub", "X" => $x, "Y" => $y, "Z" => $z, "level" => $world->getFolderName()];
        if ($yaw !== null && $pitch !== null) {
            $hubcoords["yaw"] = $yaw;
            $hubcoords["pitch"] = $pitch;
        }
        if ($count !== null && theSpawn::getInstance()->getUseRandomHubs()) {
            $setRandHub = implode('|', [$x, $y, $z, $world->getFolderName()]);
            if ($yaw !== null && $pitch !== null) $setRandHub .= "|" . $yaw . "|" . $pitch;
            $randHub->set($count, $setRandHub);
            $randHub->save();
        } else {
            $hub->set("hub", $hubcoords);
            $hub->save();
        }
    }

    /**
     * @param int|null $count
     * @return Position|Location|null
     */
    public function getRandomHub(int $count = null): Position|Location|null
    {
        $randHubs = $this->getRandomHubList();
        if (!theSpawn::getInstance()->getUseRandomHubs()) return null;
        if ($count === null) {
            $matches = [];
            if (!$randHubs->exists(1)) return null;
            foreach ($randHubs->getAll() as $all) {
                $matches[] = $all;
            }
            $matchCount = count($matches);
            return $this->getRandomHub(mt_rand(1, $matchCount));
        } else {
            $i = explode('|', $randHubs->get($count));
            $worldName = $i[3];
            if (theSpawn::getInstance()->checkWorld($worldName) instanceof World) {
                if (!isset($i[4])) return new Position($i[0], $i[1], $i[2], theSpawn::getInstance()->checkWorld($worldName));
                return new Location($i[0], $i[1], $i[2], theSpawn::getInstance()->checkWorld($worldName), $i[4], $i[5]);
            } else {
                return Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn();
            }
        }
    }

    /**
     * @param int $count
     * @return bool
     */
    public function checkSetRandomHub(int $count): bool
    {
        return $this->getRandomHubList()->exists(($count - 1)) || $count == 1;
    }

    /**
     * @param int $count
     * @return bool
     */
    public function checkRemoveRandomHub(int $count): bool
    {
        return !$this->getRandomHubList()->exists(($count + 1)) || $count == 1;
    }


    /**
     * @param int|null $count
     * @return Position|Location|false|null
     */
    public function getHub(int $count = null): Position|Location|null|false
    {
        $hub = $this->getHubConfig();

        if ($count !== null && theSpawn::getInstance()->getUseRandomHubs())
            return $this->getRandomHub($count) === null ? false : $this->getRandomHub($count);

        if (theSpawn::getInstance()->getUseRandomHubs())
            return $this->getRandomHub() === null ? false : $this->getRandomHub();

        if ($hub->exists("hub")) {
            $hubArray = $hub->get("hub", []);
            return LocationHelper::legacyConvertArrayToPosition($hubArray);
        } else
            return Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn();
    }

    /**
     * @param int|null $count
     * @return bool
     * @throws JsonException
     */
    public function removeHub(int $count = null): bool
    {
        $hub = $this->getHubConfig();
        $randHubs = $this->getRandomHubList();
        if ($count !== null && theSpawn::getInstance()->getUseRandomHubs()) {
            if ($randHubs->exists($count)) {
                $hub->remove("hub");
                $hub->save();
                return true;
            } else return false;
        } elseif ($hub->exists("hub")) {
            $hub->remove("hub");
            $hub->save();
            return true;
        } else return false;
    }
}