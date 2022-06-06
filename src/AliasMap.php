<?php

namespace supercrafter333\theSpawn;

use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;

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