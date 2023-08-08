<?php

namespace supercrafter333\theSpawn\tpa;

use pocketmine\scheduler\Task;

class TpaManager
{

    public static array $tpas = [];

    /**
     * Returns all tpas.
     * @return array
     */
    public static function getTPAs(): array
    {
        return self::$tpas;
    }

    /**
     * Returns the Tpa-class of a tpa exists and null if it doesn't.
     * @param string $sourcePlayer
     * @return string[]|null
     */
    public static function getTpa(string $sourcePlayer): array|null
    {
        return self::$tpas[$sourcePlayer] ?? null;
    }

    /**
     * Adds a tpa to list.
     * @param string $sourcePlayer
     * @param string $targetPlayer
     * @param bool $isTpaHere
     * @return bool
     */
    public static function addTpa(string $sourcePlayer, string $targetPlayer, bool $isTpaHere): bool
    {
        if (isset(self::$tpas[$sourcePlayer])) return false;
        $arr = ["target" => $targetPlayer, "isTpaHere" => $isTpaHere];
        self::$tpas[$sourcePlayer] = $arr;
        return true;
    }

    /**
     * @param string $sourcePlayer
     * @param Task $task
     * @return bool
     */
    public static function setTpaTask(string $sourcePlayer, Task $task): bool
    {
        if (self::getTpa($sourcePlayer) === null) return false;

        $tpa = new Tpa($sourcePlayer);
        $target = $tpa->getTarget();
        $isTpaHere = $tpa->isTpaHere();
        $arr = ["target" => $target, "isTpaHere" => $isTpaHere, "task" => $task];
        unset(self::$tpas[$sourcePlayer]);
        self::$tpas[$sourcePlayer] = $arr;
        return true;
    }

    /**
     * Removes a tpa.
     * @param string $sourcePlayer
     * @return bool
     */
    public static function removeTpa(string $sourcePlayer): bool
    {
        if (!isset(self::$tpas[$sourcePlayer])) return false;

        unset(self::$tpas[$sourcePlayer]);
        return true;
    }

    /**
     * Checks if a player has a tpa of another player.
     * @param string $targetPlayer
     * @param string $sourcePlayer
     * @return bool
     */
    public static function hasTpaOf(string $targetPlayer, string $sourcePlayer): bool
    {
        if (!isset(self::$tpas[$sourcePlayer])) return false;
        return (new Tpa($sourcePlayer))->getTarget() === $targetPlayer;
    }

    /**
     * Returns the tpas of a player.
     * @param string $targetPlayer
     * @return string[]|null
     */
    public static function getTPAsOf(string $targetPlayer): ?array
    {
        $TPAs = self::getTPAs();
        $newTPAs = [];
        foreach ($TPAs as $source => $tpaArray)
            if (self::hasTpaOf($targetPlayer, $source))
                $newTPAs[$source] = self::getTpa($source);

        if (count($newTPAs, COUNT_RECURSIVE) <= 0) return null;
        return $newTPAs;
    }
}