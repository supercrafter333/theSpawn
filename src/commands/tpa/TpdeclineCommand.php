<?php

namespace supercrafter333\theSpawn\commands\tpa;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\ConfigManager;
use supercrafter333\theSpawn\events\tpa\TpaAnswerEvent;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use supercrafter333\theSpawn\tpa\Tpa;
use supercrafter333\theSpawn\tpa\TpaManager;

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
        $source = $this->getPlayerByPrefix(implode(" ", $args)) instanceof Player
        ? $this->getPlayerByPrefix(implode(" ", $args))->getName()
        : implode(" ", $args);

        if (!TpaManager::hasTpaOf($s->getName(), $source)) {
            $s->sendMessage(str_replace("{target}", $source, MsgMgr::getMsg("no-pending-tpa")));
            return;
        }
        $tpaInfo = new Tpa($source);
        if (!$tpaInfo->getSourceAsPlayer() instanceof Player) {
            $s->sendMessage(str_replace("{target}", $source, theSpawn::$prefix . MsgMgr::getMsg("player-not-found")));
            return;
        }

        $ev = new TpaAnswerEvent($tpaInfo, false);
        $ev->call();
        if ($ev->isCancelled()) return;

        $sourcePlayer = $tpaInfo->getSourceAsPlayer();
        $tpaInfo->cancel();
        $sourcePlayer->sendMessage(str_replace("{target}", $s->getName(), theSpawn::$prefix . MsgMgr::getMsg("tpa-declined-source")));
        $s->sendMessage(str_replace("{source}", $source, theSpawn::$prefix . MsgMgr::getMsg("tpa-declined-target")));
        if (ConfigManager::getInstance()->useToastNotifications() && $sourcePlayer instanceof Player && $sourcePlayer->isOnline()) {
            $replace = ["{target}" => $s->getName()];
            $sourcePlayer->sendToastNotification(MsgMgr::getMsg("tn-tpa-declined-target-title", $replace), MsgMgr::getMsg("tn-tpa-declined-target-body", $replace));
        }
    }

    public function getPlugin(): Plugin
    {
        return $this->pl;
    }
}