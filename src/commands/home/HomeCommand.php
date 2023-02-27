<?php

namespace supercrafter333\theSpawn\commands\home;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\XpCollectSound;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\events\teleport\HomeTeleportEvent;
use supercrafter333\theSpawn\form\HomeForms;
use supercrafter333\theSpawn\home\Home;
use supercrafter333\theSpawn\home\HomeManager;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class HomeCommand
 * @package supercrafter333\theSpawn\commands
 */
class HomeCommand extends theSpawnOwnedCommand
{

    
    /**
     * HomeCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct("home", "theSpawn.home.cmd", "Teleport you to a home or see your homes!", "§4Use: §r/home [name]", $aliases);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();

        if (!$this->canUse($s)) return;

        if (!isset($args[0])) {
            if ($pl->listHomes($s) !== null)
                if ($this->useForms())
                    $s->sendForm((new HomeForms())->open($s));
                else {
                    $s->sendMessage($prefix . MsgMgr::getMsg("homelist", ["{homelist}" => $pl->listHomes($s)]));
                    $s->broadcastSound(new XpCollectSound(), [$s]);
                }
            else
                $s->sendMessage($prefix . MsgMgr::getMsg("no-homes-set"));
            return;
        }

        self::simpleExecute($s, $args);
    }

    public static function simpleExecute(Player $s, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();

        if (!self::testPermissionX($s, "theSpawn.home.cmd", "home")) return;

        if (!($home = HomeManager::getHome($args[0], $s)) instanceof Home) {
            $s->sendMessage($prefix . MsgMgr::getMsg("home-not-exists", ["{home}" => $args[0]]));
            return;
        }

        if (!$pl->isPositionSafe($home->getLocation())) {
            $s->sendMessage($prefix . MsgMgr::getMsg("position-not-safe"));
            return;
        }

        $ev = new HomeTeleportEvent($s, $s->getLocation(), $home->getLocation(), $home->getName());
        $ev->call();
        if ($ev->isCancelled()) return;
        $s->teleport($ev->getTarget());
        $s->sendMessage($prefix . MsgMgr::getMsg("home-teleport", ["{home}" => $args[0]]));
        $s->broadcastSound(new PopSound(), [$s]);
    }
}