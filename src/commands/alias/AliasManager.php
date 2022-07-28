<?php

namespace supercrafter333\theSpawn\commands\alias;

use JsonException;
use pocketmine\utils\Config;
use pocketmine\world\World;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

class AliasManager
{

    /**
     * @return Config
     */
    public static function getAliasConfig(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "aliaslist.yml", Config::YAML);
    }

    /**
     * Check if an alias is set.
     * @param string $aliasName
     * @return bool
     */
    public static function existsAlias(string $aliasName): bool
    {
        return self::getAliasConfig()->exists($aliasName);
    }

    /**
     * Returns the world of an alias.
     * @param string $aliasName
     * @return World|null
     */
    public static function getAliasWorld(string $aliasName): World|null
    {
        if (!self::existsAlias($aliasName))
            return null;

        return theSpawn::getInstance()->checkWorld(self::getAliasConfig()->get($aliasName));
    }

    /**
     * Used to set/create world-aliases.
     * @param string $aliasName
     * @param World $world
     * @return void
     * @throws JsonException
     */
    public static function setAlias(string $aliasName, World $world): void
    {
        $cfg = self::getAliasConfig();
        $cfg->set($aliasName, $world->getFolderName());
        $cfg->save();

        theSpawn::getInstance()->getServer()->getCommandMap()->register("theSpawn", self::getAliasAsCommand($aliasName));
    }

    /**
     * Remove an alias.
     * @param string $aliasName
     * @return bool
     * @throws JsonException
     */
    public static function removeAlias(string $aliasName): bool
    {
        if (!self::existsAlias($aliasName))
            return false;

        $cfg = self::getAliasConfig();
        $cfg->remove($aliasName);
        $cfg->save();

        $cmdMap = theSpawn::getInstance()->getServer()->getCommandMap();
        if (($cmd = $cmdMap->getCommand($aliasName)))
            $cmdMap->unregister($cmd);
        return true;
    }

    /**
     * Reactivates all aliases.
     * This is used on theSpawn's enable method.
     * This is an internal method.
     * @internal
    */
    public static function reactivateAliases(): void
    {
        foreach (self::getAliasConfig()->getAll(true) as $alias)
            if (($cmd = self::getAliasAsCommand($alias)) instanceof AliasCommand)
                theSpawn::getInstance()->getServer()->getCommandMap()->register("theSpawn", $cmd);
    }

    /**
     * Used to get the command of an alias.
     * @param string $aliasName
     * @return AliasCommand|null
     */
    public static function getAliasAsCommand(string $aliasName): AliasCommand|null
    {
        if (!($world = self::getAliasWorld($aliasName)) instanceof World)
            return null;

        return new AliasCommand($aliasName, MsgMgr::getMsg("alias-command-description", ["{alias}" => $aliasName, "{world}" => $world->getFolderName()]));
    }
}