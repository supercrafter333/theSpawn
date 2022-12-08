<?php

namespace supercrafter333\theSpawn\commands;

use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\permission\Permission;
use pocketmine\player\Player;
use supercrafter333\theSpawn\events\playerwarp\PlayerWarpCreateEvent;
use supercrafter333\theSpawn\events\playerwarp\PlayerWarpRemoveEvent;
use supercrafter333\theSpawn\events\playerwarp\PlayerWarpTeleportEvent;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\pwarp\PlayerWarp;
use supercrafter333\theSpawn\pwarp\PlayerWarpManager;
use function array_shift;
use function count;
use function implode;
use function strtolower;

class PlayerWarpCommand extends theSpawnOwnedCommand
{

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = [], Permission|string|null $permission = null)
    {
        parent::__construct($name, $description, $usageMessage, $aliases, $permission);
    }

    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $pl = $this->getOwningPlugin();
        if (!$this->canUse($s)) return;
        /**@var Player $s*/

        if (count($args) <= 0) {
            /*if ($pl->useForms())
                //TODO: open form
            else*/
                $s->sendMessage($this->usageMessage);
        }

        $subCmd = array_shift($args);
        switch ($subCmd) {
            case "set":
            case "add":
            case "create":
                if (!isset($args[0])) {
                    $s->sendMessage($this->usageMessage);
                    return;
                }
                $warpName = implode(" ", $args);

                if (PlayerWarpManager::exists($warpName)) {
                    $s->sendMessage(MsgMgr::getMsg("pwarp-already-exists", ["{warp}" => $warpName], true));
                    return;
                }

                if (($max = PlayerWarpManager::getMaxPlayerWarpCount($s)) >= PlayerWarpManager::getPlayerWarpsOf($s->getName())) {
                    $s->sendMessage(MsgMgr::getMsg("pwarp-maximum-reached", ["{max}" => $max], true));
                    return;
                }

                $warp = new PlayerWarp($s->getLocation(), $warpName, $s->getName());

                $ev = new PlayerWarpCreateEvent($warp);
                $ev->call();
                if ($ev->isCancelled()) return;

                PlayerWarpManager::createPlayerWarp($warp);
                $s->sendMessage(MsgMgr::getMsg("pwarp-created", ["{warp}" => $warpName], true));

                break;

            case "del":
            case "remove":
                if (!isset($args[0])) {
                    $s->sendMessage($this->usageMessage);
                    return;
                }
                $warpName = implode(" ", $args);

                if (!PlayerWarpManager::exists($warpName)) {
                    $s->sendMessage(MsgMgr::getMsg("pwarp-doesnt-exist", ["{warp}" => $warpName], true));
                    return;
                }

                $warp = PlayerWarpManager::getPlayerWarp($warpName);
                if (strtolower($warp->getOwnerName()) !== strtolower($s->getName())) {
                    $s->sendMessage(MsgMgr::getMsg("pwarp-not-owner", ["{warp}" => $warpName], true));
                    return;
                }

                $ev = new PlayerWarpRemoveEvent($warp);
                $ev->call();
                if ($ev->isCancelled()) return;

                PlayerWarpManager::removePlayerWarp($warpName);
                $s->sendMessage(MsgMgr::getMsg("pwarp-removed", ["{warp}" => $warpName], true));

                break;

            case "teleport":
            case "tp":
                if (!isset($args[0])) {
                    $s->sendMessage($this->usageMessage);
                    return;
                }
                $warpName = implode(" ", $args);
                $warp = PlayerWarpManager::getPlayerWarp($warpName);

                if (!$warp instanceof PlayerWarp) {
                    $s->sendMessage(MsgMgr::getMsg("pwarp-doesnt-exist", ["{warp}" => $warpName], true));
                    return;
                }

                $loc = $warp->getLocation();
                $posMsg = $loc->getFloorX() . ' | ' . $loc->getFloorY() . ' | ' . $loc->getFloorZ();
                $worldName = $loc->getWorld()->getFolderName();
                if (!$pl->isPositionSafe($loc)) {
                    $s->sendMessage(MsgMgr::getMsg("position-not-safe", null, true));
                    return;
                }

                $ev = new PlayerWarpTeleportEvent($warp, $s);
                $ev->call();
                if ($ev->isCancelled()) return;

                $s->teleport($warp->getLocation());
                $s->sendMessage(MsgMgr::getMsg("pwarp-teleported", ["{warp}" => $warpName, "{owner}" => $warp->getOwnerName(), "{location}" => $posMsg, "{world}" => $worldName], true));

                break;

            case "list":
            case "ls":
                $s->sendMessage(MsgMgr::getMsg("pwarp-list", ["{list}" => implode(", ", PlayerWarpManager::getConfig()->getAll(true))], true));
                break;

            default:
                $s->sendMessage($this->usageMessage);
                break;
        }
    }
}