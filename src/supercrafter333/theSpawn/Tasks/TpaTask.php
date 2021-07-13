<?php

namespace supercrafter333\theSpawn\Tasks;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\Others\Tpa;
use supercrafter333\theSpawn\theSpawn;

class TpaTask extends Task
{

    private $secs;

    private $tpa;

    public function __construct(int $seconds, Tpa $tpa)
    {
        $this->secs = $seconds;
        $this->tpa = $tpa;
    }

    public function onRun(int $currentTick)
    {
        if ($this->tpa->getSourceAsPlayer() == null && $this->tpa->getTargetAsPlayer() instanceof Player) {
            $this->tpa->getTargetAsPlayer()->sendMessage(str_replace("{source}", $this->tpa->getSource(), MsgMgr::getMsg("tpa-cancelled-by-source")));
            theSpawn::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        if ($this->tpa->getTargetAsPlayer() == null && $this->tpa->getSourceAsPlayer() instanceof Player) {
            $this->tpa->getTargetAsPlayer()->sendMessage(str_replace("{target}", $this->tpa->getTarget(), MsgMgr::getMsg("tpa-cancelled-by-target")));
            theSpawn::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        if ($this->tpa->getTargetAsPlayer() == null && $this->tpa->getSourceAsPlayer() == null) {
            theSpawn::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        if ($this->secs > 10) {
            $this->secs--;
            return;
        }
        if ($this->secs <= 10 && $this->secs > 0) {
            $this->tpa->getTargetAsPlayer()->sendMessage(str_replace("{secs}", $this->secs, MsgMgr::getMsg("tpa-secs")));
            return;
        } elseif ($this->secs <= 0) {
            $this->tpa->getTargetAsPlayer()->sendMessage(str_replace(["{target}", "{source}"], [$this->tpa->getTarget(), $this->tpa->getSource()], MsgMgr::getMsg("tpa-ended")));
            theSpawn::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
    }
}