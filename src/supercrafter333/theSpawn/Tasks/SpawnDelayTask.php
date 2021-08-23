<?php

namespace supercrafter333\theSpawn\Tasks;

use pocketmine\level\sound\PopSound;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

class SpawnDelayTask extends Task
{

    /**
     * @var Player
     */
    private $player;

    /**
     * @var int
     */
    private $secs;

    public function __construct(Player $player, int $seconds)
    {
        $this->player = $player;
        $this->secs = $seconds;
    }

    public function onRun(int $currentTick)
    {
        $player = $this->player;
        if ($this->secs > 3) {
            $this->secs--;
        }
        if ($this->secs <= 3 && $this->secs > 0) {
            $player->sendTip(str_replace("{secs}", $this->secs, MsgMgr::getMsg("delay-tip")));
            $this->secs--;
        }
        if ($this->secs <= 0) {
            $player->teleport(theSpawn::getInstance()->getSpawn($player->getLevel()));
            $player->sendMessage(theSpawn::$prefix . str_replace(["{world}"], [$player->getLevel()->getName()], MsgMgr::getMsg("spawn-tp")));
            $player->getLevel()->addSound(new PopSound($player));
            theSpawn::getInstance()->stopSpawnDelay($player);
        }
    }
}