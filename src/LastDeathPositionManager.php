<?php

namespace supercrafter333\theSpawn;

use DateTime;
use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\world\Position;

class LastDeathPositionManager
{

    protected static array $lastDeathPositions = [];

    /**
     * @param Player $player
     * @param Location|Position $position
     * @return void
     */
    public static function setLastDeathPosition(Player $player, Location|Position $position): void
    {
        $date = (new DateTime('now'))->modify('+' . theSpawn::getInstance()->getConfig()->get("back-time") . ' minutes');
        self::$lastDeathPositions[$player->getName()] = [$position, $date];
    }

    /**
     * @param Player $player
     * @return Location|Position|null
     */
    public static function getLastDeathPosition(Player $player): Location|Position|null
    {
        if (!isset(self::$lastDeathPositions[$player->getName()])) return null;

        $dp = self::$lastDeathPositions[$player->getName()];
        $now = new DateTime('now');

        if ($now > $dp[1]) {
            unset(self::$lastDeathPositions[$player->getName()]);
            return null;
        }

        $loc = $dp[0];
        unset(self::$lastDeathPositions[$player->getName()]);

        return $loc;
    }
}