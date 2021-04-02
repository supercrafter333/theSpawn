<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Class WarpCommand
 * @package supercrafter333\theSpawn\Commands
 */
class WarpCommand extends Command implements PluginIdentifiableCommand
{

    /**
     * @var theSpawn
     */
    private $plugin;

    /**
     * WarpCommand constructor.
     * @param string $name
     * @param string $description
     * @param string|null $usageMessage
     * @param array $aliases
     */
    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->plugin = theSpawn::getInstance();
        parent::__construct("warp", "Teleport you to a warp!", "§4Use: §r/warp [name]", $aliases);
    }

    /**
     * @param CommandSender $s
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $prefix = theSpawn::PREFIX;
        $pl = theSpawn::getInstance();
        if (!$s instanceof Player) {
            $s->sendMessage(MsgMgr::getOnlyIGMsg());
            return;
        }
        if (count($args) < 1) {
            if ($pl->listWarps() !== null) {
                $s->sendMessage($prefix . str_replace(["{warplist}"], [$pl->listWarps()], MsgMgr::getMsg("warplist")));
                $s->getLevel()->broadcastLevelEvent($s, LevelEventPacket::EVENT_SOUND_ORB, (int)mt_rand());
                return;
            } else {
                $s->sendMessage($prefix . MsgMgr::getMsg("no-warps-set"));
                return;
            }
        }
        if (!$s->hasPermission("theSpawn.warp.cmd")) {
            $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
            return;
        }
        if ($pl->useWarps() == false) {
            $s->sendMessage($prefix . MsgMgr::getMsg("warps-deactivated"));
            return;
        }
        if (!$pl->existsWarp($args[0])) {
            $s->sendMessage($prefix . str_replace(["{warpname}"], [$args[0]], MsgMgr::getMsg("warp-not-exists")));
            return;
        }
        $warpPos = $pl->getWarpPosition($args[0]);
        if ($warpPos == false) {
            $s->sendMessage($prefix . str_replace(["{warpname}"], [$args[0]], MsgMgr::getMsg("warp-not-exists")));
            return;
        }
        $warpInfo = $pl->getWarpInfo($args[0]);
        $posMsg = $warpInfo->getX() . $warpInfo->getY() . $warpInfo->getZ();
        $worldName = $warpInfo->getLevelName();
        $s->teleport($warpPos);
        $s->sendMessage($prefix . str_replace(["{warpname}"], [$args[0]], str_replace(["{world}"], [$worldName], str_replace(["{position}"], [$posMsg], MsgMgr::getMsg("warp-teleport")))));
    }

    /**
     * @return Plugin
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}