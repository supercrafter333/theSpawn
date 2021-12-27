<?php

namespace supercrafter333\theSpawn\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Custom Command class of theSpawn to add PluginOwned smarter.
 */
abstract class theSpawnOwnedCommand extends Command implements PluginOwned
{

    /**
     * @return theSpawn
     */
    public function getOwningPlugin(): Plugin
    {
        return theSpawn::getInstance();
    }

    abstract public function execute(CommandSender|Player $s, string $commandLabel, array $args): void;

    public function canUse(CommandSender|Player $sender, bool $checkIsPlayer = true): bool
    {
        $pl = $this->getOwningPlugin();

        if ($this->getPermission() !== null && !$this->testPermission($sender, $this->getPermission())) return false;

        if ($checkIsPlayer && $this->isPlayer($sender)) return true;

        if ($checkIsPlayer) {
            $sender->sendMessage(MsgMgr::getOnlyIGMsg());
        }

        return !$checkIsPlayer;
    }

    public function isPlayer(CommandSender|Player|string $sender, bool $byPrefix = false): bool
    {
        if (!$byPrefix && ($player = $this->getOwningPlugin()->getServer()->getPlayerExact(!is_string($sender) ? $sender->getName() : $sender)) && $player->isOnline()) return true;

        if (($player = $this->getOwningPlugin()->getServer()->getPlayerByPrefix(!is_string($sender) ? $sender->getName() : $sender)) && $player->isOnline()) return true;

        return false;
    }

    public static function testPermissionX(Player $player, string $permission, string $commandName): bool
    {
        if (!$player->hasPermission($permission)) {
            $player->sendMessage(KnownTranslationFactory::pocketmine_command_error_permission($commandName)->prefix(TextFormat::RED));
            return false;
        }

        return true;
    }
}