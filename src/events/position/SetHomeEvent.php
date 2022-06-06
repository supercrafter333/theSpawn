<?php

namespace supercrafter333\theSpawn\events\position;

use pocketmine\player\Player;
use pocketmine\world\Position;
use supercrafter333\theSpawn\events\theSpawnPositionEvent;

class SetHomeEvent extends theSpawnPositionEvent
{

    public function __construct(Position $position, protected string $homeName, protected Player $player)
    {
        parent::__construct($position);
    }

    /**
     * @return string
     */
    public function getHomeName(): string
    {
        return $this->homeName;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }
}