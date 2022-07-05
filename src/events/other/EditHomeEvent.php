<?php

namespace supercrafter333\theSpawn\events\other;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use supercrafter333\theSpawn\home\Home;

class EditHomeEvent extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(private Home $home) {}

    /**
     * @return Home
     */
    public function getHome(): Home
    {
        return $this->home;
    }

    /**
     * @param Home $home
     */
    public function setHome(Home $home): void
    {
        $this->home = $home;
    }
}