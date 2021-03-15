<?php

namespace supercrafter333\theSpawn;

use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;

/**
 * Class AliasMap
 * @package supercrafter333\theSpawn
 */
abstract class AliasMap extends Command implements PluginIdentifiableCommand
{

    /**
     * @var theSpawn
     */
    private $plugin;

    /**
     * AliasMap constructor.
     * @param theSpawn $plugin
     * @param $cmdName
     * @param $cmdDescription
     */
    public function __construct(theSpawn $plugin, $cmdName, $cmdDescription)
    {
        parent::__construct($cmdName, $cmdDescription);
        $this->plugin = $plugin;
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}