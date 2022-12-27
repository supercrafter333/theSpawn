<?php

namespace supercrafter333\theSpawn\tpa;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\sound\XpLevelUpSound;
use supercrafter333\theSpawn\events\tpa\TpaAnswerEvent;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\task\TpaTask;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class Tpa
 * @package supercrafter333\theSpawn\tpa
 */
class Tpa
{

    /**
     * @var array|null
     */
    private array|null $tpa;

    public function __construct(private readonly string $source)
    {
        $this->tpa = TpaManager::$tpas[$this->source];
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
     * @return Task|string|bool|null
     */
    public function getVal(string $value): null|Task|string|bool
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
     * @return Player|null
     */
    public function getSourceAsPlayer(): ?Player
    {
        if ($this->getSource() === null) return null;
        return Server::getInstance()->getPlayerExact($this->getSource());
    }

    /**
     * @return Player|null
     */
    public function getTargetAsPlayer(): ?Player
    {
        if ($this->getTarget() === null) return null;
        return Server::getInstance()->getPlayerExact($this->getTarget());
    }

    /**
     * @return bool
     */
    public function isTpaHere(): bool
    {
        return (bool)$this->getVal("isTpaHere");
    }

    /**
     * @param int $seconds
     */
    public function runTask(int $seconds): void
    {
        $tpaTask = new TpaTask($seconds, $this);
        $task = theSpawn::getInstance()->getScheduler()->scheduleRepeatingTask($tpaTask, 20);
        TpaManager::setTpaTask($this->getSource(), $task->getTask());
    }

    public function cancel(): void
    {
        TpaManager::removeTpa($this->getSource());
    }

    public function complete(): bool
    {
        $this->cancel();
        $ev = new TpaAnswerEvent($this, true);
        if ($ev->isCancelled()) return false;
        if (!$this->isTpaHere()) {
            $targetPos = $this->getTargetAsPlayer()->getLocation();
            if (!theSpawn::getInstance()->isPositionSafe($targetPos)) {
                $this->getSourceAsPlayer()->sendMessage(theSpawn::$prefix . MsgMgr::getMsg("position-not-safe"));
                $ev->cancel();
                $ev->call();
                return false;
            }
            $ev->call();
            if (!$ev->isCancelled())
                $this->getSourceAsPlayer()->teleport($targetPos);
        } else {
            $sourcePos = $this->getSourceAsPlayer()->getLocation();
            if (!theSpawn::getInstance()->isPositionSafe($sourcePos)) {
                $this->getSourceAsPlayer()->sendMessage(theSpawn::$prefix . MsgMgr::getMsg("position-not-safe"));
                $ev->cancel();
                $ev->call();
                return false;
            }
            $ev->call();
            if (!$ev->isCancelled())
                $this->getTargetAsPlayer()->teleport($sourcePos);
        }

        $this->getSourceAsPlayer()->broadcastSound(new XpLevelUpSound(mt_rand(1, 100)), [$this->getSourceAsPlayer()]);
        return $ev->isCancelled();
    }
}