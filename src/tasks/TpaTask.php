<?php

namespace supercrafter333\theSpawn\tasks;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\sound\XpCollectSound;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use supercrafter333\theSpawn\tpa\TpaInfo;

/**
 *
 */
class TpaTask extends Task
{

    /**
     * @param int $seconds
     * @param TpaInfo $tpa
     */
    public function __construct(private int $seconds, private TpaInfo $tpa) {}

    /**
     * Run function xD
     */
    public function onRun(): void
    {
        if ($this->tpa->getSourceAsPlayer() == null && $this->tpa->getTargetAsPlayer() instanceof Player) {
            $this->tpa->getTargetAsPlayer()->sendMessage(str_replace("{source}", $this->tpa->getSource(), MsgMgr::getMsg("tpa-cancelled-by-source")));
            theSpawn::getInstance()->removeTpa($this->tpa->getSource());
            $this->getHandler()->cancel();
            return;
        }
        if ($this->tpa->getTargetAsPlayer() == null && $this->tpa->getSourceAsPlayer() instanceof Player) {
            $this->tpa->getSourceAsPlayer()->sendMessage(str_replace("{target}", $this->tpa->getTarget(), MsgMgr::getMsg("tpa-cancelled-by-target")));
            theSpawn::getInstance()->removeTpa($this->tpa->getSource());
            $this->getHandler()->cancel();
            return;
        }
        if ($this->tpa->getTargetAsPlayer() == null && $this->tpa->getSourceAsPlayer() == null) {
            theSpawn::getInstance()->removeTpa($this->tpa->getSource());
            $this->getHandler()->cancel();
            return;
        }
        if ($this->seconds > 10) {
            $this->seconds--;
            return;
        }
        if ($this->seconds < 10 && $this->seconds > 0) {
            $this->tpa->getTargetAsPlayer()->sendMessage(str_replace("{secs}", (string)$this->seconds, MsgMgr::getMsg("tpa-secs")));
            $this->tpa->getTargetAsPlayer()->broadcastSound(new XpCollectSound(), [$this->tpa->getTargetAsPlayer()]);
            $this->seconds--;
        } elseif ($this->seconds <= 0) {
            $this->tpa->getTargetAsPlayer()?->sendMessage(str_replace(["{target}", "{source}"], [$this->tpa->getTarget(), $this->tpa->getSource()], MsgMgr::getMsg("tpa-ended")));
            $this->getHandler()->cancel();
        }
    }
}