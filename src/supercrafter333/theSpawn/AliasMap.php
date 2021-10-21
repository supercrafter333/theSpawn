<?php

namespace supercrafter333\theSpawn;

use pocketmine\command\Command;
use pocketmine\plugin\Plugin;

/**
 * Class AliasMap
 * @package supercrafter333\theSpawn
 */
abstract class AliasMap extends Command
{

    /**
     * AliasMap constructor.
     * @param theSpawn $plugin
     * @param $cmdName
     * @param $cmdDescription
     */
    public function __construct(private theSpawn $plugin, $cmdName, $cmdDescription)
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