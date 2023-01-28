<?php

namespace supercrafter333\theSpawn\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\permission\Permission;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use supercrafter333\theSpawn\ConfigManager;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 * Custom Command class of theSpawn to add PluginOwned smarter.
 */
abstract class theSpawnOwnedCommand extends Command implements PluginOwned
{

    protected readonly theSpawn $plugin;

    protected readonly string $prefix;

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = [], Permission|string|null $permission = null)
    {
        $this->plugin = theSpawn::getInstance();
        $this->prefix = theSpawn::$prefix;
        $this->setPermission($permission);
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    /**
     * @return theSpawn
     */
    public function getOwningPlugin(): Plugin
    {
        return theSpawn::getInstance();
    }

    /**
     * @param CommandSender|Player $s
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    abstract public function execute(CommandSender $s, string $commandLabel, array $args): void;

    /**
     * @param CommandSender|Player $sender
     * @param bool $checkIsPlayer
     * @return bool
     */
    public function canUse(CommandSender|Player $sender, bool $checkIsPlayer = true): bool
    {
        $pl = $this->getOwningPlugin();

        if ($this->getPermission() !== null && !$this->testPermission($sender, $this->getPermission())) return false;

        if ($checkIsPlayer && $this->isPlayer($sender)) return true;

        if ($checkIsPlayer) $sender->sendMessage(MsgMgr::getOnlyIGMsg());

        return !$checkIsPlayer;
    }

    /**
     * @param CommandSender|Player|string $sender
     * @param bool $byPrefix
     * @return bool
     */
    public function isPlayer(CommandSender|Player|string $sender, bool $byPrefix = false): bool
    {
        if (!$byPrefix && ($player = $this->getOwningPlugin()->getServer()->getPlayerExact(!is_string($sender) ? $sender->getName() : $sender)) && $player->isOnline()) return true;

        if (($player = $this->getPlayerByPrefix(!is_string($sender) ? $sender->getName() : $sender)) && $player->isOnline()) return true;

        return false;
    }

    /**
     * @param Player $player
     * @param string $permission
     * @param string $commandName
     * @return bool
     */
    public static function testPermissionX(Player $player, string $permission, string $commandName): bool
    {
        if (!$player->hasPermission($permission)) {
            $player->sendMessage(KnownTranslationFactory::pocketmine_command_error_permission($commandName)->prefix(TextFormat::RED));
            return false;
        }

        return true;
    }

    public function useForms(): bool
    {
        return ConfigManager::getInstance()->useForms();
    }

    /**
     * @internal Replaces the Server::getPlayerByPrefix() method that will be removed soon.
     *
	 * Returns an online player whose name begins with or equals the given string (case insensitive).
	 * The closest match will be returned, or null if there are no online matches.
     * This function is used as a replacement of the deprecated PocketMine-MP method Server::getPlayerByPrefix()
	 *
	 * @see Server::getPlayerExact()
	 */
	public function getPlayerByPrefix(string $name) : ?Player
    {
        $found = null;
        $name = strtolower($name);
        $delta = PHP_INT_MAX;
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (stripos($player->getName(), $name) === 0) {
                $curDelta = strlen($player->getName()) - strlen($name);
                if ($curDelta < $delta) {
                    $found = $player;
                    $delta = $curDelta;
                }
                if ($curDelta === 0) {
                    break;
                }
            }
        }

        return $found;
    }
}