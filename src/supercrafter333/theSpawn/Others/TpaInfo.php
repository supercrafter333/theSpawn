<?php

namespace supercrafter333\theSpawn\Others;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\Server;
use supercrafter333\theSpawn\Tasks\TpaTask;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class TpaInfo
 * @package supercrafter333\theSpawn\Others
 */
class TpaInfo
{

    private $source;

    /**
     * @var array
     */
    private $tpa;

    public function __construct(string $source)
    {
        $this->source = $source;
        $this->tpa = theSpawn::getInstance()->getTpaOf($source);
    }

    /**
     * @param string $value
     * @return bool
     */
    private function isValSet(string $value): bool
    {
        return isset($this->tpa[$value]);
    }

    /**
     * @param string $value
     * @return mixed|null
     */
    public function getVal(string $value)
    {
        if (!$this->isValSet($value)) return null;
        return $this->tpa[$value];
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string|null
     */
    public function getTarget(): ?string
    {
        return $this->getVal("target");
    }

    /**
     * @return int|null
     */
    public function getTaskId(): ?int
    {
        return $this->getVal("taskId");
    }

    /**
     * @return Player|null
     */
    public function getSourceAsPlayer(): ?Player
    {
        if ($this->getSource() === null) return null;
        return Server::getInstance()->getPlayer($this->getSource());
    }

    /**
     * @return Player|null
     */
    public function getTargetAsPlayer(): ?Player
    {
        if ($this->getTarget() === null) return null;
        return Server::getInstance()->getPlayer($this->getTarget());
    }

    /**
     * @return bool|null
     */
    public function isTpaHere(): ?bool
    {
        return $this->getVal("isTpaHere");
    }

    /**
     * @param int $seconds
     */
    public function runTask(int $seconds): void
    {
        $tpaTask = new TpaTask($seconds, $this);
        $task = theSpawn::getInstance()->getScheduler()->scheduleRepeatingTask($tpaTask, 20);
        theSpawn::getInstance()->setTpaTaskId($this->getSource(), $task->getTaskId());
    }

    public function cancel(): void
    {
        theSpawn::getInstance()->removeTpa($this->getSource());
        theSpawn::getInstance()->getScheduler()->cancelTask($this->getTaskId());
    }

    public function complete(): void
    {
        $this->cancel();
        if (!$this->isTpaHere()) {
            $this->getSourceAsPlayer()->teleport($this->getTargetAsPlayer());
        } else {
            $this->getTargetAsPlayer()->teleport($this->getSourceAsPlayer());
        }
        $this->getSourceAsPlayer()->getLevel()->broadcastLevelSoundEvent($this->getSourceAsPlayer(), LevelSoundEventPacket::SOUND_LEVELUP, mt_rand());
    }
}