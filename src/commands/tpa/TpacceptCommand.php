<?php

namespace supercrafter333\theSpawn\commands\tpa;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\ConfigManager;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use supercrafter333\theSpawn\tpa\Tpa;
use supercrafter333\theSpawn\tpa\TpaManager;
use function implode;

/**
 *
 */
class TpacceptCommand extends theSpawnOwnedCommand
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
        parent::__construct($name, "theSpawn.tpaccept.cmd", $description, $usageMessage, $aliases);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
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
        $sourcePlayer = $tpaInfo->getSourceAsPlayer();
        $tpaInfo->complete();
        $sourcePlayer->sendMessage(str_replace("{target}", $s->getName(), theSpawn::$prefix . MsgMgr::getMsg("tpa-accepted-source")));
        $s->sendMessage(str_replace("{source}", $source, theSpawn::$prefix . MsgMgr::getMsg("tpa-accepted-target")));
        if (ConfigManager::getInstance()->useToastNotifications() && $sourcePlayer instanceof Player && $sourcePlayer->isOnline()) {
            $replace = ["{target}" => $s->getName()];
            $sourcePlayer->sendToastNotification(MsgMgr::getMsg("tn-tpa-accepted-target-title", $replace), MsgMgr::getMsg("tn-tpa-accepted-target-body", $replace));
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