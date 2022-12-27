<?php

namespace supercrafter333\theSpawn;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use supercrafter333\theSpawn\task\SpawnDelayTask;

class SpawnDelayManager
{

    /**
     * @var SpawnDelayTask[]
    */
    protected static array $spawnDelays;
    
    /**
     * @param Player $player
     */
    public static function startSpawnDelay(Player $player): void
    {
        $task = theSpawn::getInstance()->getScheduler()->scheduleRepeatingTask(new SpawnDelayTask($player, theSpawn::getInstance()->getConfig()->get("spawn-delay-seconds")), 20);
        self::$spawnDelays[$player->getName()] = $task->getTask();
    }

    /**
     * @param Player $player
     * @return SpawnDelayTask|null
     */
    public static function getSpawnDelayTaskOf(Player $player): SpawnDelayTask|null
    {
        return self::hasSpawnDelay($player) ? self::$spawnDelays[$player->getName()] : null;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function hasSpawnDelay(Player $player): bool
    {
        return isset(self::$spawnDelays[$player->getName()]);
    }

    /**
     * @param Player $player
     * @return bool
     */
    public static function stopSpawnDelay(Player $player): bool
    {
        if (!isset(self::$spawnDelays[$player->getName()])) return false;

        $task = self::$spawnDelays[$player->getName()];
        if ($task instanceof Task)
            $task->getHandler()->cancel();
        unset(self::$spawnDelays[$player->getName()]);
        return true;
    }
}