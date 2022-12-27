<?php

namespace supercrafter333\theSpawn\commands\warp;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\world\sound\XpCollectSound;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\events\teleport\WarpTeleportEvent;
use supercrafter333\theSpawn\form\WarpForms;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use supercrafter333\theSpawn\warp\WarpManager;
use function count;

/**
 * Class WarpCommand
 * @package supercrafter333\theSpawn\commands
 */
class WarpCommand extends theSpawnOwnedCommand
{

    
    /**
     * WarpCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->setPermission("theSpawn.warp.cmd");
        parent::__construct("warp", "Teleport you to a warp!", "§4Use: §r/warp [name]", $aliases);
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

        if (count($args) < 1 && $pl->listWarps() !== null)
                if ($this->useForms())
                    $s->sendForm((new WarpForms())->open($s));
                else {
                    $s->sendMessage($prefix . str_replace(["{warplist}"], [$pl->listWarps()], MsgMgr::getMsg("warplist")));
                    $s->broadcastSound(new XpCollectSound(), [$s]);
                }
        elseif (count($args) < 1) {
            $s->sendMessage($prefix . MsgMgr::getMsg("no-warps-set"));
        }
        else
            self::simpleExecute($s, $args);
    }

    public static function simpleExecute(Player $s, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();

        if (!self::testPermissionX($s, "theSpawn.warp.cmd", "warp")) return;

        if (!WarpManager::existsWarp($args[0])) {
            $s->sendMessage($prefix . str_replace(["{warpname}"], [(string)$args[0]], MsgMgr::getMsg("warp-not-exists")));
            return;
        }

        $warp = WarpManager::getWarp($args[0]);

        if (!$warp->hasPermission($s)) {
            $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
            return;
        }

        $loc = $warp->getLocation();
        $posMsg = $loc->getFloorX() . ' | ' . $loc->getFloorY() . ' | ' . $loc->getFloorZ();
        $worldName = $loc->getWorld()->getFolderName();
        if (!$pl->isPositionSafe($loc)) {
            $s->sendMessage($prefix . MsgMgr::getMsg("position-not-safe"));
            return;
        }

        $ev = new WarpTeleportEvent($s, $s->getLocation(), $loc, $warp->getName());
        $ev->call();
        if ($ev->isCancelled()) return;

        $s->teleport($ev->getTarget());
        $s->sendMessage($prefix . MsgMgr::getMsg("warp-teleport", ['{warpname}' => $warp->getName(), '{world}' => $worldName, '{position}' => $posMsg]));
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}