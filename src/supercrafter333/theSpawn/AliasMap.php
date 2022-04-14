<?php

namespace supercrafter333\theSpawn;

use supercrafter333\theSpawn\Commands\theSpawnOwnedCommand;
use pocketmine\plugin\Plugin;

/**
 * Class AliasMap
 * @package supercrafter333\theSpawn
 */
abstract class AliasMap extends theSpawnOwnedCommand
{

    /**
     * AliasMap constructor.
     * @param theSpawn $plugin
     * @param $cmdName
     * @param $cmdDescription
     */
    public function __construct(protected theSpawn $plugin, $cmdName, $cmdDescription)
    {
        parent::__construct($cmdName, $cmdDescription);
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}