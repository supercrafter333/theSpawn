<?php

namespace supercrafter333\theSpawn\events\other;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

class RemoveHubEvent extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(protected int|null $hubCount = null) {}

    /**
     * @return int|null
     */
    public function getHubCount(): ?int
    {
        return $this->hubCount;
    }
}