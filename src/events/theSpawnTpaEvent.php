<?php

namespace supercrafter333\theSpawn\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use supercrafter333\theSpawn\tpa\Tpa;

abstract class theSpawnTpaEvent extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(private Tpa $tpa) {}

    /**
     * @return Tpa
     */
    final public function getTpa(): Tpa
    {
        return $this->tpa;
    }

    /**
     * @param Tpa $tpa
     */
    public function setTpa(Tpa $tpa): void
    {
        $this->tpa = $tpa;
    }
}