<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\sound\GhastShootSound;
use pocketmine\Player;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class DelhomeCommand
 * @package supercrafter333\theSpawn\Commands
 */
class DelhomeCommand extends Command
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
        parent::__construct("delhome", "Delete a home!", "§4Use: §r/delhome <name>", ["deletehome", "rmhome"]);
    }

    /**
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): bool
    {
        $prefix = theSpawn::PREFIX;
        $pl = theSpawn::getInstance();
        if (!$s->hasPermission("theSpawn.delhome.cmd")) {
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
        if ($pl->rmHome($s, $args[0]) == false) {
            $s->sendMessage($prefix . str_replace(["{home}"], [$args[0]], MsgMgr::getMsg("home-not-exists")));
            return true;
        } else {
            $s->sendMessage($prefix . str_replace(["{home}"], [$args[0]], MsgMgr::getMsg("home-deleted")));
            $s->getLevel()->addSound(new GhastShootSound($s));
            return true;
        }
    }
}