<?php

namespace supercrafter333\theSpawn\events\other;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\player\Player;

class RemoveHomeEvent extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(protected string $homeName, protected Player $player) {}

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