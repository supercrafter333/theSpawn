<?php

namespace supercrafter333\theSpawn\commands\warp;

use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\form\WarpForms;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\warp\WarpManager;
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
            $s->sendForm((new WarpForms())->openChooseEditWarp($s));
            return;
        }

        if (($warp = WarpManager::getWarp(implode(" ", $args))) === null)
            $s->sendMessage($prefix . MsgMgr::getMsg("warp-not-exists", ["{warpname}" => (string)$args[0]]));
        else
            $s->sendForm((new WarpForms())->openEditWarp($s, $warp));
    }
}