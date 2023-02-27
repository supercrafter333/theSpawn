<?php

namespace supercrafter333\theSpawn\commands\home;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\DoorBumpSound;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\events\position\SetHomeEvent;
use supercrafter333\theSpawn\form\HomeForms;
use supercrafter333\theSpawn\home\Home;
use supercrafter333\theSpawn\home\HomeManager;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use function count;

/**
 * Class SethomeCommand
 * @package supercrafter333\theSpawn\commands
 */
class SethomeCommand extends theSpawnOwnedCommand
{



    /**
     * SethomeCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        parent::__construct("sethome", "theSpawn.sethome.cmd", "Set a new home!", "§4Use: §r/sethome <name>", ["addhome"]);
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

        if (count($args) < 1) {
            if ($this->useForms())
                $s->sendForm((new HomeForms())->openSetHome($s));
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

        if (!self::testPermissionX($s, "theSpawn.sethome.cmd", "sethome")) return;

        $homes = count(HomeManager::getHomesOfPlayer($s), COUNT_RECURSIVE);
        if (($maxHomes = HomeManager::getMaxHomesOfPlayer($s)) <= $homes) {
            $s->sendMessage(MsgMgr::getMsg("highest-home-count-reached", ["{max-homes}" => $maxHomes]));
            $s->broadcastSound(new AnvilFallSound(), [$s]);
            return;
        }

        $ev = new SetHomeEvent($s->getLocation(), $args[0], $s);
        $ev->call();
        if ($ev->isCancelled()) return;

        if (!HomeManager::createHome(new Home($s, $args[0], $s->getLocation())))
            $s->sendMessage($prefix . MsgMgr::getMsg("home-already-exists", ["{home}" => $args[0]]));
        else {
            $s->sendMessage($prefix . MsgMgr::getMsg("home-set", ["{home}" => $args[0]]));
            $s->broadcastSound(new DoorBumpSound(), [$s]);
        }
        return;
    }
}