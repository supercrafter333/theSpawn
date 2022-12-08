<?php

namespace supercrafter333\theSpawn\events\playerwarp;

use pocketmine\player\Player;
use supercrafter333\theSpawn\events\theSpawnPlayerWarpEvent;
use supercrafter333\theSpawn\pwarp\PlayerWarp;

class PlayerWarpTeleportEvent extends theSpawnPlayerWarpEvent
{

    public function __construct(PlayerWarp $playerWarp, private readonly Player $player)
    {
        parent::__construct($playerWarp);
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }
}