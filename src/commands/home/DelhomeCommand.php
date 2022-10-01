<?php

namespace supercrafter333\theSpawn\commands\home;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\sound\GhastShootSound;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\events\other\RemoveHomeEvent;
use supercrafter333\theSpawn\form\HomeForms;
use supercrafter333\theSpawn\home\Home;
use supercrafter333\theSpawn\home\HomeManager;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use function count;
use function is_string;

/**
 * Class DelhomeCommand
 * @package supercrafter333\theSpawn\commands
 */
class DelhomeCommand extends theSpawnOwnedCommand
{

    /**
     * DelhomeCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->setPermission("theSpawn.delhome.cmd");
        parent::__construct("delhome", "Delete a home!", "§4Use: §r/delhome <name>", ["deletehome", "rmhome"]);
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
            if (is_string($pl->listHomes($s)) && $pl->useForms())
                (new HomeForms($s->getName()))->openRmHome($s);
            elseif (!$pl->useForms() && is_string($pl->listHomes($s)))
                $s->sendMessage($this->usageMessage);
            elseif (!$pl->useForms() && !is_string($pl->listHomes($s)))
                $s->sendMessage($prefix . MsgMgr::getMsg("no-homes-set"));
            return;
        }

        if (!($home = HomeManager::getHome($args[0], $s)) instanceof Home) {
            $s->sendMessage($prefix . str_replace(["{home}"], [$args[0]], MsgMgr::getMsg("home-not-exists")));
            return;
        }

        HomeManager::removeHome($home, $s);
        $s->sendMessage($prefix . str_replace(["{home}"], [$args[0]], MsgMgr::getMsg("home-deleted")));
        $s->broadcastSound(new GhastShootSound(), [$s]);
        return;
    }

    public static function simpleExecute(Player $s, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();

        if (!self::testPermissionX($s, "theSpawn.delhome.cmd", "delhome")) return;

        if (!($home = HomeManager::getHome($args[0], $s)) instanceof Home)
            $s->sendMessage($prefix . str_replace(["{home}"], [$args[0]], MsgMgr::getMsg("home-not-exists", ["{home}" => $args[0]])));
        else {
            $ev = new RemoveHomeEvent($args[0], $s);
            $ev->call();
            if ($ev->isCancelled()) return;

            $s->sendMessage($prefix . MsgMgr::getMsg("home-deleted", ["{home}" => $args[0]]));
            $s->broadcastSound(new GhastShootSound(), [$s]);
        }
        return;
    }
}