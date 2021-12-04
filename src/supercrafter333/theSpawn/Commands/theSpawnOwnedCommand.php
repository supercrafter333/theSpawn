<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use supercrafter333\theSpawn\theSpawn;

/**
 * Custom Command class of theSpawn to add PluginOwned smarter.
 */
abstract class theSpawnOwnedCommand extends Command implements PluginOwned
{

    /**
     * @return Plugin
     */
    public function getOwningPlugin(): Plugin
    {
        return theSpawn::getInstance();
    }
}