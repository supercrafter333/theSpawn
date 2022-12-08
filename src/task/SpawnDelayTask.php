<?php

namespace supercrafter333\theSpawn\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\sound\PopSound;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

class SpawnDelayTask extends Task
{

    public function __construct(private Player $player, private int $seconds) {}

    public function onRun(): void
    {
        $player = $this->player;
        if ($this->seconds > 3) $this->seconds--;

        if ($this->seconds <= 3 && $this->seconds > 0) {
            $player->sendTip(str_replace("{secs}", (string)$this->seconds, MsgMgr::getMsg("delay-tip")));
            $this->seconds--;
        }
        if ($this->seconds <= 0) {
            if (!theSpawn::getInstance()->isPositionSafe(theSpawn::getInstance()->getSpawn($player->getWorld()))) {
                $player->sendMessage(theSpawn::$prefix . MsgMgr::getMsg("position-not-safe"));
                return;
            }
            $player->teleport(theSpawn::getInstance()->getSpawn($player->getWorld()));
            $player->sendMessage(theSpawn::$prefix . str_replace(["{world}"], [$player->getWorld()->getFolderName()], MsgMgr::getMsg("spawn-tp")));
            $player->broadcastSound(new PopSound(), [$player]);
            theSpawn::getInstance()->stopSpawnDelay($player);
        }
    }
}