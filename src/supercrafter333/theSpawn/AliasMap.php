<?php

namespace supercrafter333\theSpawn;

use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;

abstract class AliasMap extends Command implements PluginIdentifiableCommand
{

    /**
     * @var theSpawn
     */
    private theSpawn $plugin;

    public function __construct(theSpawn $plugin, $cmdName, $cmdDescription)
    {
        parent::__construct($cmdName, $cmdDescription);
        $this->plugin = $plugin;
    }

    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}