<?php

namespace supercrafter333\theSpawn\Tasks;

use pocketmine\world\sound\PopSound;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

class SpawnDelayTask extends Task
{

    public function __construct(private Player $player, private int $seconds) {}

    public function onRun(): void
    {
        $player = $this->player;
        if ($this->seconds > 3) {
            $this->seconds--;
        }
        if ($this->seconds <= 3 && $this->seconds > 0) {
            $player->sendTip(str_replace("{secs}", (string)$this->seconds, MsgMgr::getMsg("delay-tip")));
            $this->seconds--;
        }
        if ($this->seconds <= 0) {
            $player->teleport(theSpawn::getInstance()->getSpawn($player->getWorld()));
            $player->sendMessage(theSpawn::$prefix . str_replace(["{world}"], [$player->getWorld()->getFolderName()], MsgMgr::getMsg("spawn-tp")));
            $player->getWorld()->addSound($player->getPosition(), new PopSound());
            theSpawn::getInstance()->stopSpawnDelay($player);
        }
    }
}