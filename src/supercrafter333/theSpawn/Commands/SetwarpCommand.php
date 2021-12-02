<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\world\sound\DoorBumpSound;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class SetwarpCommand
 * @package supercrafter333\theSpawn\Commands
 */
class SetwarpCommand extends Command
{

    /**
     * @var theSpawn
     */
    private theSpawn $plugin;

    /**
     * SetwarpCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        $this->setPermission("theSpawn.setwarp.cmd");
        parent::__construct("setwarp", "Set a new warp!", "§4Use: §r/setwarp <warpname> [permission]", $aliases);
    }

    /**
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $prefix = theSpawn::$prefix;
        $pl = theSpawn::getInstance();
        if (!$s instanceof Player) {
            $s->sendMessage(MsgMgr::getOnlyIGMsg());
            return;
        }
        if (count($args) < 1) {
            $s->sendMessage($this->usageMessage);
            return;
        }
        if (!$s->hasPermission("theSpawn.setwarp.cmd")) {
            $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
            return;
        }
        if ($pl->useWarps() == false) {
            $s->sendMessage($prefix . MsgMgr::getMsg("warps-deactivated"));
            return;
        }
        if ($pl->existsWarp($args[0]) == false) {
            $pl->addWarp($s->getPosition()->getX(), $s->getPosition()->getY(), $s->getPosition()->getZ(), $s->getPosition()->getWorld(), $args[0], isset($args[1]) ? $args[1] : null);
            $posMsg = (string)$s->getPosition()->getX() . $s->getPosition()->getY() . $s->getPosition()->getZ();
            $s->sendMessage($prefix . str_replace(["{warpname}"], [$args[0]], str_replace(["{position}"], [$posMsg], str_replace(["{world}"], [$s->getWorld()->getDisplayName()], MsgMgr::getMsg("warp-set")))));
        } else {
            $s->sendMessage($prefix . str_replace(["{warpname}"], [$args[0]], MsgMgr::getMsg("warp-already-set")));
        }
        $s->getWorld()->addSound($s->getPosition(), new DoorBumpSound());
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}