<?php

namespace supercrafter333\theSpawn\commands\alias;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use supercrafter333\theSpawn\commands\theSpawnOwnedCommand;
use supercrafter333\theSpawn\form\AliasForms;
use supercrafter333\theSpawn\MsgMgr;
use function implode;

class AliasesCommand extends theSpawnOwnedCommand
{

    public function __construct(string $name, string $description = "", string $usageMessage = null, array $aliases = [])
    {
        $this->setPermission("theSpawn.aliases.cmd");
        parent::__construct("aliases", "Prints a list of all aliases or open a menu to edit aliases.", "§4Use: §r/aliases", ["editaliases"]);
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $s, string $commandLabel, array $args): void
    {
        if (!$this->canUse($s)) return;

        if ($this->useForms())
            $s->sendForm(AliasForms::menu());
        else
            $s->sendMessage(MsgMgr::getMsg("aliases-list", ["{aliases}" => implode(", ", AliasManager::getAliasConfig()->getAll(true))]));
    }
}