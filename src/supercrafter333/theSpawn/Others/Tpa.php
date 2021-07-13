<?php

namespace supercrafter333\theSpawn\Others;

use pocketmine\Player;
use pocketmine\Server;
use supercrafter333\theSpawn\Tasks\TpaTask;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class Tpa
 * @package supercrafter333\theSpawn\Others
 */
class Tpa
{

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Tpa constructor.
     * @param string $sourceName
     * @param string $targetName
     */
    public function __construct(string $sourceName, string $targetName)
    {
        $this->source = $sourceName;
        $this->target = $targetName;
        $this->setArray();
    }

    /**
     *
     */
    protected function setArray()
    {
        $this->cache[] = [$this->source, $this->target];
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @return Player|null
     */
    public function getSourceAsPlayer(): ?Player
    {
        $server = Server::getInstance();
        $player = $server->getPlayer($this->source);
        if ($player instanceof Player) return $player;
        return null;
    }

    /**
     * @return Player|null
     */
    public function getTargetAsPlayer(): ?Player
    {
        $server = Server::getInstance();
        $player = $server->getPlayer($this->target);
        if ($player instanceof Player) return $player;
        return null;
    }

    /**
     * @param int $seconds
     * @return \pocketmine\scheduler\TaskHandler
     */
    public function run(int $seconds)
    {
        return theSpawn::getInstance()->getScheduler()->scheduleRepeatingTask(new TpaTask($seconds, $this), 20);
    }

    /**
     * @var bool
     */
    private $tpaHere = false;

    /**
     * @param bool $tpaHere
     * @return bool
     */
    public function setTpaHere(bool $tpaHere = false): bool
    {
        if ($tpaHere == true) {
            $this->tpaHere = true;
            return true;
        }
        $this->tpaHere = false;
        return false;
    }

    /**
     * @return bool
     */
    public function isTpaHere(): bool
    {
        return $this->tpaHere;
    }

    public function completeTpa(): bool
    {
        $target = $this->getTargetAsPlayer();
        $source = $this->getSourceAsPlayer();
        if (!$target instanceof Player || !$source instanceof Player) return false;
        if ($this->isTpaHere()) {
            $target->teleport($source->getPosition());
            return true;
        } else {
            $source->teleport($target->getPosition());
            return true;
        }
    }
}