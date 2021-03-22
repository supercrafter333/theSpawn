<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

class SetwarpCommand extends Command implements PluginIdentifiableCommand
{

    private $plugin;

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        parent::__construct("setwarp", "Set a new warp!", "/setwarp <warpname>", $aliases);
    }

    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $prefix = theSpawn::PREFIX;
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
        if (!is_string($args[0])) {
            $s->sendMessage($this->usageMessage);
            return;
        }
        if ($pl->useWarps() == true) {
            $s->sendMessage($prefix . MsgMgr::getMsg("warps-deactivated"));
            return;
        }
        if ($pl->existsWarp($args[0]) == false) {
            $pl->addWarp($s->getX(), $s->getY(), $s->getZ(), $s->getLevel(), $args[0]);
            $posMsg = $s->getX() . $s->getY() . $s->getZ();
            $s->sendMessage($prefix . str_replace(["{warp}"], [$args[0]], str_replace(["{position}"], [$posMsg], MsgMgr::getMsg("warp-set"))));
            return;
        }
    }
}