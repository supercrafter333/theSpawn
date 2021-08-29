<?php

namespace supercrafter333\theSpawn\Tasks;

use pocketmine\world\sound\PopSound;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

class SpawnDelayTask extends Task
{

    /**
     * @var Player
     */
    private Player $player;

    /**
     * @var int
     */
    private int $secs;

    public function __construct(Player $player, int $seconds)
    {
        $this->player = $player;
        $this->secs = $seconds;
    }

    public function onRun(): void
    {
        $player = $this->player;
        if ($this->secs > 3) {
            $this->secs--;
        }
        if ($this->secs <= 3 && $this->secs > 0) {
            $player->sendTip(str_replace("{secs}", (string)$this->secs, MsgMgr::getMsg("delay-tip")));
            $this->secs--;
        }
        if ($this->secs <= 0) {
            $player->teleport(theSpawn::getInstance()->getSpawn($player->getWorld()));
            $player->sendMessage(theSpawn::$prefix . str_replace(["{world}"], [$player->getWorld()->getDisplayName()], MsgMgr::getMsg("spawn-tp")));
            $player->getWorld()->addSound($player->getPosition(), new PopSound());
            theSpawn::getInstance()->stopSpawnDelay($player);
        }
    }
}