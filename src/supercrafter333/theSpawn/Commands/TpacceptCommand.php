<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\Others\TpaInfo;
use supercrafter333\theSpawn\theSpawn;

/**
 *
 */
class TpacceptCommand extends Command implements PluginIdentifiableCommand
{

    /**
     * @var theSpawn
     */
    private theSpawn $pl;

    /**
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "Accept a tpa.", string $usageMessage = "§4Usage: §r/tpaccept <player>", array $aliases = [])
    {
        $this->pl = theSpawn::getInstance();
        $this->setPermission("theSpawn.tpaccept.cmd");
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $s, string $commandLabel, array $args)
    {
        $pl = $this->pl;
        if (!$s->hasPermission($this->getPermission())) {
            $s->sendMessage(MsgMgr::getNoPermMsg());
            return;
        }
        if (!$s instanceof Player) {
            $s->sendMessage(MsgMgr::getOnlyIGMsg());
            return;
        }
        if (count($args) < 1) {
            $s->sendMessage($this->usageMessage);
            return;
        }
        if (!$pl->hasTpaOf($args[0], $s->getName())) {
            $s->sendMessage(str_replace("{target}", (string)$args[0], MsgMgr::getMsg("no-pending-tpa")));
            return;
        }
        $tpaInfo = new TpaInfo($s->getName());
        if (!$tpaInfo->getTargetAsPlayer() instanceof Player) {
            $s->sendMessage(str_replace("{target}", (string)$args[0], theSpawn::$prefix . MsgMgr::getMsg("player-not-online")));
            return;
        }
        $target = $tpaInfo->getTargetAsPlayer();
        $name = $target->getName();
        $tpaInfo->complete();
        $target->sendMessage(str_replace("{target}", $name, theSpawn::$prefix . MsgMgr::getMsg("tpa-accepted-source")));
        $s->sendMessage(str_replace("{source}", $s->getName(), theSpawn::$prefix . MsgMgr::getMsg("tpa-accepted-target")));
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->pl;
    }
}