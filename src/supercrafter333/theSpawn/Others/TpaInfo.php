<?php

namespace supercrafter333\theSpawn\Others;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\sound\XpLevelUpSound;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\Tasks\TpaTask;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class TpaInfo
 * @package supercrafter333\theSpawn\Others
 */
class TpaInfo
{

    /**
     * @var string[]|null
     */
    private array|null $tpa;

    public function __construct(private string $source)
    {
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
     * @return null|Task|string
     */
    public function getVal(string $value): null|Task|string
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
     * @return Task|null
     */
    public function getTask(): ?Task
    {
        return $this->getVal("task");
    }

    /**
     * @return Player|null
     */
    public function getSourceAsPlayer(): ?Player
    {
        if ($this->getSource() === null) return null;
        return Server::getInstance()->getPlayerByPrefix($this->getSource());
    }

    /**
     * @return Player|null
     */
    public function getTargetAsPlayer(): ?Player
    {
        if ($this->getTarget() === null) return null;
        return Server::getInstance()->getPlayerByPrefix($this->getTarget());
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
        theSpawn::getInstance()->setTpaTask($this->getSource(), $task->getTask());
    }

    public function cancel(): void
    {
        theSpawn::getInstance()->removeTpa($this->getSource());
    }

    public function complete(): void
    {
        $this->cancel();
        if (!$this->isTpaHere()) {
            $targetPos = $this->getTargetAsPlayer()->getLocation();
            if (!theSpawn::getInstance()->isPositionSafe($targetPos)) {
                $this->getSourceAsPlayer()->sendMessage(theSpawn::$prefix . MsgMgr::getMsg("position-not-safe"));
                return;
            }
            $this->getSourceAsPlayer()->teleport($targetPos);
        } else {
            $sourcePos = $this->getSourceAsPlayer()->getLocation();
            if (!theSpawn::getInstance()->isPositionSafe($sourcePos)) {
                $this->getSourceAsPlayer()->sendMessage(theSpawn::$prefix . MsgMgr::getMsg("position-not-safe"));
                return;
            }
            $this->getTargetAsPlayer()->teleport($sourcePos);
        }
        $this->getSourceAsPlayer()->getWorld()->addSound($this->getSourceAsPlayer()->getPosition(), new XpLevelUpSound(mt_rand(1, 100)));
    }
}