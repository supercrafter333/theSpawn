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

class TpaCommand extends theSpawnOwnedCommand
{

    private theSpawn $pl;

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->pl = theSpawn::getInstance();
        $this->setPermission("theSpawn.tpa.cmd");
        parent::__construct($name, "Start a TPA.", "§4Usage: §r/tpa <player>", $aliases);
    }


    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param string[] $args
     * @return mixed
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $pl = $this->pl;

        if (!$this->canUse($s)) return;

        if (count($args) < 1) {
            $s->sendMessage($this->usageMessage);
            return;
        }
        $target = $pl->getServer()->getPlayerByPrefix($args[0]);
        if (!$target instanceof Player) {
            $s->sendMessage(str_replace("{name}", (string)$args[0], theSpawn::$prefix . MsgMgr::getMsg("player-not-found")));
            return;
        }
        $name = $target->getName();
        if (strtolower($name) === strtolower($s->getName())) {
            $s->sendMessage(theSpawn::$prefix . MsgMgr::getMsg("no-self-tpa"));
            return;
        }
        if (!theSpawn::getInstance()->addTpa($s->getName(), $name)) {
            $s->sendMessage(theSpawn::$prefix . theSpawn::$prefix . MsgMgr::getMsg("pending-tpa-error"));
            return;
        }
        $tpa = new TpaInfo($s->getName());
        $tpa->runTask($pl->getConfig()->get("tpa-time"));
        $s->sendMessage(str_replace("{target}", $name, theSpawn::$prefix . MsgMgr::getMsg("tpa-send")));
        $target->sendMessage(str_replace("{source}", $s->getName(), theSpawn::$prefix . MsgMgr::getMsg("new-tpa")));
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->pl;
    }
}