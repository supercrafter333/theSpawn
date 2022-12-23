<?php

namespace supercrafter333\theSpawn\Commands;

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
        $source = $pl->getServer()->getPlayerByPrefix(implode(" ", $args)) instanceof Player
        ? $pl->getServer()->getPlayerByPrefix(implode(" ", $args))->getName()
        : implode(" ", $args);

        if (!$pl->hasTpaOf($s->getName(), $source)) {
            $s->sendMessage(str_replace("{target}", $source, MsgMgr::getMsg("no-pending-tpa")));
            return;
        }
        $tpaInfo = new TpaInfo($source);
        if (!$tpaInfo->getSourceAsPlayer() instanceof Player) {
            $s->sendMessage(str_replace("{target}", $source, theSpawn::$prefix . MsgMgr::getMsg("player-not-found")));
            return;
        }
        $sourcePlayer = $tpaInfo->getSourceAsPlayer();
        $tpaInfo->cancel();
        $sourcePlayer->sendMessage(str_replace("{target}", $s->getName(), theSpawn::$prefix . MsgMgr::getMsg("tpa-declined-source")));
        $s->sendMessage(str_replace("{source}", $source, theSpawn::$prefix . MsgMgr::getMsg("tpa-declined-target")));
    }

    public function getPlugin(): Plugin
    {
        return $this->pl;
    }
}