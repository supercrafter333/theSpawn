<?php

namespace supercrafter333\theSpawn\commands\warp;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\sound\DoorBumpSound;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\events\position\SetWarpEvent;
use supercrafter333\theSpawn\form\WarpForms;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use supercrafter333\theSpawn\warp\Warp;
use supercrafter333\theSpawn\warp\WarpManager;

/**
 * Class SetwarpCommand
 * @package supercrafter333\theSpawn\commands
 */
class SetwarpCommand extends theSpawnOwnedCommand
{

    /**
     * SetwarpCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct("setwarp", "theSpawn.setwarp.cmd", "Set a new warp!", "§4Use: §r/setwarp <warpname> [permission: true|false] [iconPath | iconUrl]", $aliases);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();

        if (!$this->canUse($s)) return;

        if (count($args) < 1) {
            if ($this->useForms())
                $s->sendForm((new WarpForms())->openSetWarp($s));
            else
                $s->sendMessage($this->usageMessage);
            return;
        }

        self::simpleExecute($s, $args);
    }

    public static function simpleExecute(Player $s, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();

        if (!self::testPermissionX($s, "theSpawn.setwarp.cmd", "setwarp")) return;

        if (!WarpManager::existsWarp($args[0])) {
            $perm = false;
            $icon = null;
            if (isset($args[1]) && $args[1] !== 'null' && $args[1] !== 'false' && $args[1] !== "") $perm = true;
            if (isset($args[2]) && $args[2] !== 'null' && $args[2] !== "") $icon = $args[2];

            $ev = new SetWarpEvent($s->getLocation(), $args[0]);
            $ev->call();
            if ($ev->isCancelled()) return;

            WarpManager::createWarp(new Warp($s->getLocation(), $args[0], $perm, $icon));
            $posMsg = $s->getPosition()->getFloorX() . ' | ' . $s->getPosition()->getFloorY() . ' | ' . $s->getPosition()->getFloorZ();
            $s->sendMessage($prefix . MsgMgr::getMsg("warp-set", ["{warpname}" => (string)$args[0], "{position}" => $posMsg, "{world}" => $s->getWorld()->getFolderName()]));
        } else
            $s->sendMessage($prefix . MsgMgr::getMsg("warp-already-set", ["{warpname}" => (string)$args[0]]));
        $s->broadcastSound(new DoorBumpSound(), [$s]);
    }
}