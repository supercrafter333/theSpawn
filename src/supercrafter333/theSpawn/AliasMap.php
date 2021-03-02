<?php

namespace supercrafter333\theSpawn;

use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;

class AliasMap extends Command implements PluginIdentifiableCommand
{

    public function __construct(theSpawn $main, $cmdName, $cmdDescription)
    {
        parent::__construct($cmdName, $cmdDescription);
        $this->main = $main;
    }

    public function getPlugin(): theSpawn
    {
        return $this->getPlugin();
    }
}