<?php

namespace supercrafter333\theSpawn\Commands;

use supercrafter333\theSpawn\Commands\theSpawnOwnedCommand;
use pocketmine\command\CommandSender;
use pocketmine\world\sound\PopSound;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\world\sound\XpCollectSound;
use supercrafter333\theSpawn\Forms\HomeForms;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use function implode;

/**
 * Class HomeCommand
 * @package supercrafter333\theSpawn\Commands
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
        $this->plugin = theSpawn::getInstance();
        $this->setPermission("theSpawn.home.cmd");
        parent::__construct("home", "Teleport you to a home or see your homes!", "§4Use: §r/home [name]", $aliases);
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
            if ($pl->listHomes($s) !== null) {
                if ($pl->useForms()) {
                    $homeForms = new HomeForms($s->getName());
                    $homeForms->open($s);
                } else {
                    $s->sendMessage($prefix . str_replace(["{homelist}"], [$pl->listHomes($s)], MsgMgr::getMsg("homelist")));
                }
                $s->getWorld()->addSound($s->getPosition(), new XpCollectSound());
            } else {
                $s->sendMessage($prefix . MsgMgr::getMsg("no-homes-set"));
            }
            return;
        }

        self::simpleExecute($s, $args);
    }

    public static function simpleExecute(Player $s, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();

        if (!self::testPermissionX($s, "theSpawn.home.cmd", "home")) return;

        if (($homeInfo = $pl->getHomeInfo($s, implode(" ", $args))) === null) {
            $s->sendMessage($prefix . MsgMgr::getMsg("home-not-exists", ["{home}" => implode(" ", $args)]));
            return;
        }

        $worldName = $homeInfo->getLevelName();
        if ($pl->getServer()->getWorldManager()->isWorldGenerated($worldName) == false) {
            $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found"));
            return;
        }
        if ($homeInfo->existsHome() == false) {
            $s->sendMessage($prefix . str_replace(["{home}"], [(string)$args[0]], MsgMgr::getMsg("home-not-exists")));
            return;
        }

        $homePos = $pl->getHomePos($s, (string)$args[0]);
        if (!$pl->isPositionSafe($homePos)) {
            $s->sendMessage($prefix . MsgMgr::getMsg("position-not-safe"));
            return;
        }

        $s->teleport($homePos);
        $s->sendMessage($prefix . str_replace(["{home}"], [(string)$args[0]], MsgMgr::getMsg("home-teleport")));
        $s->broadcastSound(new PopSound(), [$s]);
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}