<?php

namespace supercrafter333\theSpawn\Tasks;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\Others\TpaInfo;
use supercrafter333\theSpawn\theSpawn;

/**
 *
 */
class TpaTask extends Task
{

    /**
     * @var int
     */
    private int $secs;

    /**
     * @var TpaInfo
     */
    private TpaInfo $tpa;

    /**
     * @param int $seconds
     * @param TpaInfo $tpa
     */
    public function __construct(int $seconds, TpaInfo $tpa)
    {
        $this->secs = $seconds;
        $this->tpa = $tpa;
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick)
    {
        if ($this->tpa->getSourceAsPlayer() == null && $this->tpa->getTargetAsPlayer() instanceof Player) {
            $this->tpa->getTargetAsPlayer()->sendMessage(str_replace("{source}", $this->tpa->getSource(), MsgMgr::getMsg("tpa-cancelled-by-source")));
            theSpawn::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            theSpawn::getInstance()->removeTpa($this->tpa->getSource());
            return;
        }
        if ($this->tpa->getTargetAsPlayer() == null && $this->tpa->getSourceAsPlayer() instanceof Player) {
            $this->tpa->getSourceAsPlayer()->sendMessage(str_replace("{target}", $this->tpa->getTarget(), MsgMgr::getMsg("tpa-cancelled-by-target")));
            theSpawn::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            theSpawn::getInstance()->removeTpa($this->tpa->getSource());
            return;
        }
        if ($this->tpa->getTargetAsPlayer() == null && $this->tpa->getSourceAsPlayer() == null) {
            theSpawn::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            theSpawn::getInstance()->removeTpa($this->tpa->getSource());
            return;
        }
        if ($this->secs > 10) {
            $this->secs--;
            return;
        }
        if ($this->secs <= 10 && $this->secs > 0) {
            $this->tpa->getTargetAsPlayer()->sendMessage(str_replace("{secs}", (string)$this->secs, MsgMgr::getMsg("tpa-secs")));
            $this->secs--;
        } elseif ($this->secs <= 0) {
            $this->tpa->getTargetAsPlayer()->sendMessage(str_replace(["{target}", "{source}"], [$this->tpa->getTarget(), $this->tpa->getSource()], MsgMgr::getMsg("tpa-ended")));
            theSpawn::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}