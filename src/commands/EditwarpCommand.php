<?php

namespace supercrafter333\theSpawn\commands;

use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use supercrafter333\theSpawn\Forms\WarpForms;
use supercrafter333\theSpawn\MsgMgr;
use function implode;

class EditwarpCommand extends theSpawnOwnedCommand
{

    public function __construct(string $name, Translatable|string $description = "Edit a warp.", Translatable|string|null $usageMessage = "§4Usage: §r/editwarp [warpname]", array $aliases = [])
    {
        $this->setPermission("theSpawn.editwarp.cmd");
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        $pl = $this->plugin;
        $prefix = $this->prefix;

        if (!$this->canUse($s)) return;

        if (!isset($args[0])) {
            (new WarpForms())->openChooseEditWarp($s);
            return;
        }

        if (($warp = $pl->getWarpInfo(implode(" ", $args))) === null) {
            $s->sendMessage($prefix . str_replace(["{warpname}"], [(string)$args[0]], MsgMgr::getMsg("warp-not-exists")));
            return;
        }

        (new WarpForms())->openEditWarp($s, $warp);
    }
}