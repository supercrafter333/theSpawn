<?php

namespace supercrafter333\theSpawn\home;

use JsonException;
use pocketmine\entity\Location;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionAttachmentInfo;
use pocketmine\permission\PermissionManager;
use pocketmine\player\IPlayer;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use supercrafter333\theSpawn\ConfigManager;
use supercrafter333\theSpawn\LocationHelper;
use supercrafter333\theSpawn\theSpawn;

class HomeManager
{
    /**
     * @param IPlayer $player
     * @return Config
     */
    public static function getHomeConfig(IPlayer $player): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "homes/" . $player->getName() . ".yml", Config::YAML);
    }

    /**
     * Checks if a home exists.
     * @param string|Home $home
     * @param IPlayer|null $player
     * @return bool
     */
    public static function existsHome(string|Home $home, IPlayer $player = null): bool
    {
        return self::getHomeConfig(($player instanceof IPlayer ? $player : $home->getPlayer()))->exists(TextFormat::clean(($home instanceof Home ? $home->getName() : $home)));
    }
    
    /**
     * Migrates old homes to the new home-format.
     * This is an internal method.
     * @internal
     * @return void
     * @throws JsonException
     */
    public static function migrateOldHomes(): void
    {
        $mt = microtime(true);
        $logger = theSpawn::getInstance()->getLogger();

        $logger->warning("Migrating homes...");

        $homePlayers = [];

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(theSpawn::getInstance()->getDataFolder() . "homes/"), RecursiveIteratorIterator::CHILD_FIRST);
		/** @var SplFileInfo $fileInfo */
		foreach($files as $fileInfo) {
            if ($fileInfo->isFile() && $fileInfo->getExtension() === "yml"
                && ($offlinePlayer = theSpawn::getInstance()->getServer()->getOfflinePlayer($fileInfo->getBasename('.yml'))) instanceof IPlayer)
                $homePlayers[] = $offlinePlayer;
        }

        foreach ($homePlayers as $homePlayer)
            foreach (self::getHomeConfig($homePlayer)->getAll() as $home => $values)
                if (isset($values["homeName"]) || isset($values["level"])) {
                    $logger->debug("Migrating home: §b" . $home . "§r of player §b" .  $homePlayer->getName(). "§r   ...");
                    if (self::migrateHome($home, $homePlayer, $values))
                        $logger->debug("Successfully migrated home: §b" . $home . "§r (Player: §b" .  $homePlayer->getName(). "§r)");
                    else
                        $logger->error("Failed to migrate home: §b" . $home . "§r (Player: §b" .  $homePlayer->getName(). "§r)");
                }

        $logger->warning("Successfully migrated homes in §b" . round((microtime(true) - $mt), 3) . "sec.");
    }

    /**
     * Migrates an old home to the new home-format.
     * @internal
     * @param string $homeName
     * @param IPlayer $player
     * @param array $homeInfos
     * @return bool
     * @throws JsonException
     */
    protected static function migrateHome(string $homeName, IPlayer $player, array $homeInfos): bool
    {
        if (isset($homeInfos["location"]))
            return false;

        $cfg = self::getHomeConfig($player);
        $cfg->remove($homeName);
        $cfg->save();

        $x = $homeInfos["X"];
        $y = $homeInfos["Y"];
        $z = $homeInfos["Z"];
        $worldName = $homeInfos["level"];
        $yaw = $homeInfos["yaw"] ?? 0.0;
        $pitch = $homeInfos["pitch"] ?? 0.0;
        if (!($world = theSpawn::getInstance()->checkWorld($worldName)) instanceof World)
            return false;

        self::createHome(new Home(
            $player,
            TextFormat::clean($homeName),
            Location::fromObject(new Position($x, $y, $z, $world), $world, $yaw, $pitch)
        ));
        return true;
    }

    /**
     * Creates a new home.
     * This method will return 'false' if the home already exists.
     * @param Home $home
     * @return bool
     * @throws JsonException
     */
    public static function createHome(Home $home): bool
    {
        if (self::existsHome($home))
            return false;

        $cfg = self::getHomeConfig($home->getPlayer());
        $cfg->set(TextFormat::clean($home->getName()), LocationHelper::locationToString($home->getLocation()));
        $cfg->save();
        return true;
    }

    /**
     * Removes an existing home.
     * Returns 'false' if the home doesn't exist.
     * @param string|Home $home
     * @param IPlayer|null $player
     * @return bool
     * @throws JsonException
     */
    public static function removeHome(string|Home $home, IPlayer $player = null): bool
    {
        if ($home instanceof Home && !$player instanceof IPlayer)
            $player = $home->getPlayer();
        if (!$home instanceof Home && !$player instanceof IPlayer)
            return false;

        if (!self::existsHome($home, $player))
            return false;

        $cfg = self::getHomeConfig($player);

        $cfg->remove(TextFormat::clean(($home instanceof Home ? $home->getName() : $home)));
        $cfg->save();
        return true;
    }

    /**
     * Returns the home-class if the home exists and null if it doesn't.
     * @param string $homeName
     * @param IPlayer $player
     * @return Home|null
     */
    public static function getHome(string $homeName, IPlayer $player): Home|null
    {
        $homeName = TextFormat::clean($homeName);
        if (self::existsHome($homeName, $player)) {
            $location = LocationHelper::stringToLocation(self::getHomeConfig($player)->get($homeName));

            if (!$location->isValid()) {
                self::removeHome($homeName);
                return null;
            }
            return new Home($player, $homeName, $location);
        }

        return null;
    }

    /**
     * Saves an existing home to the config-file.
     * @param Home $home
     * @return bool
     * @throws JsonException
     */
    public static function saveHome(Home $home): bool
    {
        if (!self::existsHome($home))
            return false;

        $cfg = self::getHomeConfig($home->getPlayer());
        
        $cfg->set($home->getName(), LocationHelper::locationToString($home->getLocation()));
        $cfg->save();
        return true;
    }

    /**
     * Return an array of all homes by a player.
     * @param IPlayer $player
     * @return string[]
     */
    public static function getHomesOfPlayer(IPlayer $player): array
    {
        return self::getHomeConfig($player)->getAll(true);
    }

    /**
     * Returns the maximal amount of homes that a player can have.
     * @param Player $player
     * @return int
     */
    public static function getMaxHomesOfPlayer(Player $player): int //copied from MyPlot (by jasonwynn10)
    {
		if($player->hasPermission("theSpawn.homes.unlimited") || !ConfigManager::getInstance()->useMaxHomePermissions()) return PHP_INT_MAX;

		$perms = array_map(fn(PermissionAttachmentInfo $attachment) => [$attachment->getPermission(), $attachment->getValue()], $player->getEffectivePermissions());
		$perms = array_merge(PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_USER)->getChildren(), $perms);
		$perms = array_filter($perms, function(string $name) : bool {
			return (str_starts_with($name, "theSpawn.homes."));
		}, ARRAY_FILTER_USE_KEY);
		if(count($perms) === 0)
			return 0;
		krsort($perms, SORT_FLAG_CASE | SORT_NATURAL);
		/**
		 * @var string $name
		 * @var Permission $perm
		 */
		foreach($perms as $name => $perm) {
			$maxHomes = mb_substr($name, 15);
			if(is_numeric($maxHomes)) {
				return (int) $maxHomes;
			}
		}
		return 0;
	}

}