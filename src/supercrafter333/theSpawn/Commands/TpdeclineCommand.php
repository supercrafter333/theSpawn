<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use supercrafter333\theSpawn\Commands\theSpawnOwnedCommand;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\Others\TpaInfo;
use supercrafter333\theSpawn\theSpawn;

class TpdeclineCommand extends theSpawnOwnedCommand
{

    private theSpawn $pl;

    public function __construct(string $name, string $description = "Decline a tpa.", string $usageMessage = "§4Usage: §r/tpdecline <player>", array $aliases = [])
    {
        $this->pl = theSpawn::getInstance();
        $this->setPermission("theSpawn.tpdecline.cmd");
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $pl = $this->pl;

        if (!$this->canUse($s)) return;

        if (count($args) < 1) {
            $s->sendMessage($this->usageMessage);
            return;
        }
        if (!$pl->hasTpaOf($args[0], $s->getName())) {
            $s->sendMessage(str_replace("{target}", (string)$args[0], theSpawn::$prefix . MsgMgr::getMsg("no-pending-tpa")));
            return;
        }
        $tpaInfo = new TpaInfo($s->getName());
        if (!$tpaInfo->getTargetAsPlayer() instanceof Player) {
            $s->sendMessage(str_replace("{target}", (string)$args[0], theSpawn::$prefix . MsgMgr::getMsg("player-not-online")));
            return;
        }
        $target = $tpaInfo->getTargetAsPlayer();
        $name = $target->getName();
        $tpaInfo->cancel();
        $target->sendMessage(str_replace("{target}", $name, theSpawn::$prefix . MsgMgr::getMsg("tpa-declined-source")));
        $s->sendMessage(str_replace("{source}", $s->getName(), theSpawn::$prefix . MsgMgr::getMsg("tpa-declined-target")));
    }

    public function getPlugin(): Plugin
    {
        return $this->pl;
    }
}