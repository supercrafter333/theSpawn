<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use supercrafter333\theSpawn\Commands\theSpawnOwnedCommand;
use pocketmine\command\CommandSender;
use pocketmine\world\sound\DoorBumpSound;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class SethomeCommand
 * @package supercrafter333\theSpawn\Commands
 */
class SethomeCommand extends theSpawnOwnedCommand
{

    /**
     * @var theSpawn
     */
    private theSpawn $plugin;

    /**
     * DelhomeCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        $this->setPermission("theSpawn.sethome.cmd");
        parent::__construct("sethome", "Set a new home!", "§4Use: §r/sethome <name>", ["addhome"]);
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
        if (!$s->hasPermission("theSpawn.sethome.cmd")) {
            $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
            return true;
        }
        if (!$s instanceof Player) {
            $s->sendMessage($prefix . MsgMgr::getOnlyIGMsg());
            return true;
        }
        if (!count($args) >= 1) {
            $s->sendMessage($this->usageMessage);
            return true;
        }
        $x = $s->getPosition()->getX();
        $y = $s->getPosition()->getY();
        $z = $s->getPosition()->getZ();
        $level = $s->getWorld();
        if ($pl->setHome($s, $args[0], $x, $y, $z, $level) == false) {
            $s->sendMessage($prefix . str_replace(["{home}"], [$args[0]], MsgMgr::getMsg("home-already-exists")));
        } else {
            $s->sendMessage($prefix . str_replace(["{home}"], [$args[0]], MsgMgr::getMsg("home-set")));
            $s->getWorld()->addSound($s->getPosition(), new DoorBumpSound());
        }
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