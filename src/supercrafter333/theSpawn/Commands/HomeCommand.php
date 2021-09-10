<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\world\sound\PopSound;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\world\sound\XpCollectSound;
use supercrafter333\theSpawn\Forms\HomeForms;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class HomeCommand
 * @package supercrafter333\theSpawn\Commands
 */
class HomeCommand extends Command
{

    /**
     * @var theSpawn
     */
    private theSpawn $plugin;

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
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): bool
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();
        if (!$s->hasPermission("theSpawn.home.cmd")) {
            $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
            return true;
        }
        if (!$s instanceof Player) {
            $s->sendMessage($prefix . MsgMgr::getOnlyIGMsg());
            return true;
        }
        if (!isset($args[0])) {
            if ($pl->listHomes($s) !== null) {
                if ($pl->useForms()) {
                    $homeForms = new HomeForms($s->getName());
                    $homeForms->open($s);
                } else {
                    $s->sendMessage($prefix . str_replace(["{homelist}"], [$pl->listHomes($s)], MsgMgr::getMsg("homelist")));
                }
                $s->getLevel()->broadcastLevelEvent($s, LevelEventPacket::EVENT_SOUND_ORB, mt_rand());
            } else {
                $s->sendMessage($prefix . MsgMgr::getMsg("no-homes-set"));
            }
            return true;
        }
        $lvlName = $pl->getHomeInfo($s, $args[0])->getLevelName();
        if ($pl->getServer()->getWorldManager()->isWorldGenerated($lvlName) == false) {
            $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found"));
            return true;
        }
        if ($pl->getHomeInfo($s, $args[0])->existsHome() == false) {
            $s->sendMessage($prefix . str_replace(["{home}"], [(string)$args[0]], MsgMgr::getMsg("home-not-exists")));
            return true;
        }
        $pl->teleportToHome($s, $args[0]);
        $s->sendMessage($prefix . str_replace(["{home}"], [(string)$args[0]], MsgMgr::getMsg("home-teleport")));
        $s->getWorld()->addSound($s->getPosition(), new PopSound());
        return true;
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}