<?php

namespace supercrafter333\theSpawn\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;
use pocketmine\world\sound\PopSound;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\SpawnDelayManager;
use supercrafter333\theSpawn\theSpawn;

class SpawnDelayTask extends Task
{

    private readonly Position $startPosition;

    public function __construct(private readonly Player $player, private int $seconds)
    {
        $this->startPosition = $this->player->getPosition();
    }

    public function onRun(): void
    {
        $player = $this->player;
        if ($this->seconds > 3) $this->seconds--;

        if ($this->seconds <= 0) {
            if (!theSpawn::getInstance()->isPositionSafe(theSpawn::getInstance()->getSpawn($player->getWorld()))) {
                $player->sendMessage(theSpawn::$prefix . MsgMgr::getMsg("position-not-safe"));
                return;
            }
            $player->sendTip("§a§l✓");
            $player->teleport(theSpawn::getInstance()->getSpawn($player->getWorld()));
            $player->sendMessage(theSpawn::$prefix . str_replace(["{world}"], [$player->getWorld()->getFolderName()], MsgMgr::getMsg("spawn-tp")));
            $player->broadcastSound(new PopSound(), [$player]);
            SpawnDelayManager::stopSpawnDelay($player);
        }

        if ($this->seconds <= 3 && $this->seconds > 0) {
            $player->sendTip(str_replace("{secs}", (string)$this->seconds, MsgMgr::getMsg("delay-tip")));
            $this->seconds--;
        }
    }

    /**
     * @return Position
     */
    public function getStartPosition(): Position
    {
        return $this->startPosition;
    }
}