<?php

namespace supercrafter333\theSpawn\commands\tpa;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\ConfigManager;
use supercrafter333\theSpawn\form\TpaForms;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use supercrafter333\theSpawn\tpa\Tpa;
use supercrafter333\theSpawn\tpa\TpaManager;

class TpaCommand extends theSpawnOwnedCommand
{

    private theSpawn $pl;

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->pl = theSpawn::getInstance();
        parent::__construct($name, "theSpawn.tpa.cmd", "Start a TPA.", "§4Usage: §r/tpa <player>", $aliases);
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
            if ($this->useForms())
                $s->sendForm(TpaForms::menu($s));
            else
                $s->sendMessage($this->usageMessage);
            return;
        }
        $target = $this->getPlayerByPrefix($args[0]);
        if (!$target instanceof Player) {
            $s->sendMessage(str_replace("{name}", (string)$args[0], theSpawn::$prefix . MsgMgr::getMsg("player-not-found")));
            return;
        }
        $name = $target->getName();
        if (strtolower($name) === strtolower($s->getName())) {
            $s->sendMessage(theSpawn::$prefix . MsgMgr::getMsg("no-self-tpa"));
            return;
        }
        if (!TpaManager::addTpa($s->getName(), $name, false)) {
            $s->sendMessage(theSpawn::$prefix . theSpawn::$prefix . MsgMgr::getMsg("pending-tpa-error"));
            return;
        }
        $tpa = new Tpa($s->getName());
        $tpa->runTask($pl->getConfig()->get("tpa-time"));
        $s->sendMessage(str_replace("{target}", $name, theSpawn::$prefix . MsgMgr::getMsg("tpa-send")));
        $target->sendMessage(str_replace("{source}", $s->getName(), theSpawn::$prefix . MsgMgr::getMsg("new-tpa")));
        if (ConfigManager::getInstance()->useToastNotifications() && $target->isOnline()) {
            $replace = ["{source}" => $s->getName()];
            $target->sendToastNotification(MsgMgr::getMsg("tn-new-tpa-title", $replace), MsgMgr::getMsg("tn-new-tpa-body", $replace));
        }
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->pl;
    }
}