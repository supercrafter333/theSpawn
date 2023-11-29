<?php

namespace supercrafter333\theSpawn\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\sound\XpCollectSound;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\tpa\Tpa;
use supercrafter333\theSpawn\tpa\TpaManager;

class TpaTask extends Task
{

    /**
     * @param int $seconds
     * @param Tpa $tpa
     */
    public function __construct(private int $seconds, private readonly Tpa $tpa) {}

    public function onRun(): void
    {
        if (TpaManager::getTpa($this->tpa->getSource()) === null) {
            $this->getHandler()->cancel();
            return;
        }

        if ($this->tpa->getSourceAsPlayer() == null && $this->tpa->getTargetAsPlayer() instanceof Player) {
            $this->tpa->getTargetAsPlayer()->sendMessage(str_replace("{source}", $this->tpa->getSource(), MsgMgr::getMsg("tpa-cancelled-by-source")));
            TpaManager::removeTpa($this->tpa->getSource());
            $this->getHandler()->cancel();
            return;
        }
        if ($this->tpa->getTargetAsPlayer() == null && $this->tpa->getSourceAsPlayer() instanceof Player) {
            $this->tpa->getSourceAsPlayer()->sendMessage(str_replace("{target}", $this->tpa->getTarget(), MsgMgr::getMsg("tpa-cancelled-by-target")));
            TpaManager::removeTpa($this->tpa->getSource());
            $this->getHandler()->cancel();
            return;
        }
        if ($this->tpa->getTargetAsPlayer() == null && $this->tpa->getSourceAsPlayer() == null) {
            TpaManager::removeTpa($this->tpa->getSource());
            $this->getHandler()->cancel();
            return;
        }
        if ($this->seconds > 10) {
            $this->seconds--;
            return;
        }
        if ($this->seconds <= 10 && $this->seconds > 0) {
            $this->tpa->getTargetAsPlayer()->sendMessage(str_replace("{secs}", (string)$this->seconds, MsgMgr::getMsg("tpa-secs")));
            $this->tpa->getTargetAsPlayer()->broadcastSound(new XpCollectSound(), [$this->tpa->getTargetAsPlayer()]);
            $this->seconds--;
        } elseif ($this->seconds <= 0) {
            $this->tpa->getTargetAsPlayer()?->sendMessage(str_replace(["{target}", "{source}"], [$this->tpa->getTarget(), $this->tpa->getSource()], MsgMgr::getMsg("tpa-ended")));
            $this->tpa->getSourceAsPlayer()?->sendMessage(str_replace(["{target}", "{source}"], [$this->tpa->getTarget(), $this->tpa->getSource()], MsgMgr::getMsg("tpa-ended")));
            TpaManager::removeTpa($this->tpa->getSource());
            $this->getHandler()->cancel();
        }
    }

    /**
     * @return Tpa
     */
    public function getTpa(): Tpa
    {
        return $this->tpa;
    }
}