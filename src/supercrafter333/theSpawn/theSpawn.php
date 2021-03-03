<?php

namespace supercrafter333\theSpawn;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\sound\DoorBumpSound;
use pocketmine\level\sound\GhastShootSound;
use pocketmine\level\sound\PopSound;
use pocketmine\network\mcpe\protocol\ScriptCustomEventPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Binary;
use pocketmine\utils\Config;

/**
 * Class theSpawn
 * @package supercrafter333\theSpawn
 */
class theSpawn extends PluginBase implements Listener
{

    public static $instance;
    public $config;
    public $aliasCfg;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getConfig();
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        self::$instance = $this;
        $this->aliasCfg = new Config($this->getDataFolder() . "aliaslist.yml", Config::YAML);
        $aliasCfg = new Config($this->getDataFolder() . "aliaslist.yml", Config::YAML);
        if ($this->useAliases() == true) {
            $this->reactivateAliases();
        }
    }

    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * @param CommandSender $s
     * @param Command $cmd
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $s, Command $cmd, string $label, array $args): bool
    {
        $prefix = "§f[§7the§eSpawn§f] §8»§r ";
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        $msgs = MsgMgr::getMsgs();
        $this->getConfig();
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $config->save();
        if ($cmd->getName() == "setthespawn") {
            if ($s instanceof Player) {
                if ($s->hasPermission("theSpawn.setthespawn.cmd")) {
                    $levelname = $s->getLevel()->getName();
                    $level = $s->getLevel();
                    if (!$spawn->exists($levelname)) {
                        $this->setSpawn($s, $level);
                        $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("spawn-set")));
                        $s->getLevel()->addSound(new DoorBumpSound($s));
                        return true;
                    } else {
                        $this->setSpawn($s, $level);
                        $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("spawn-changed")));
                        $s->getLevel()->addSound(new DoorBumpSound($s));
                        return true;
                    }
                } else {
                    $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
                    return true;
                }
            } else {
                $s->sendMessage(MsgMgr::getOnlyIGMsg());
                return true;
            }
        }
        if ($cmd->getName() == "delthespawn") {
            if ($s instanceof Player) {
                if ($s->hasPermission("theSpawn.delthespawn.cmd")) {
                    $levelname = $s->getLevel()->getName();
                    $level = $this->getServer()->getLevelByName($levelname);
                    if ($spawn->exists($levelname)) {
                        $this->removeSpawn($level);
                        $s->sendMessage($prefix . MsgMgr::getMsg("spawn-removed"));
                        $s->getLevel()->addSound(new GhastShootSound($s));
                        return true;
                    } else {
                        $s->sendMessage($prefix . MsgMgr::getMsg("no-spawn-set-in-this-world"));
                        return true;
                    }
                } else {
                    $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
                    return true;
                }
            } else {
                $s->sendMessage(MsgMgr::getOnlyIGMsg());
                return true;
            }
        }
        if ($cmd->getName() == "spawn") {
            if ($s instanceof Player) {
                $levelname = $s->getLevel()->getName();
                $level = $s->getLevel();
                if ($spawn->exists($levelname)) {
                    $s->teleport($this->getSpawn($level));
                    $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("spawn-tp")));
                    $s->getLevel()->addSound(new PopSound($s));
                    return true;
                } else {
                    $s->sendMessage($prefix . MsgMgr::getMsg("no-spawn-set"));
                    return true;
                }
            } else {
                $s->sendMessage(MsgMgr::getOnlyIGMsg());
                return true;
            }
        }
        if ($cmd->getName() == "setthehub") {
            if ($s instanceof Player) {
                if ($s->hasPermission("theSpawn.setthehub.cmd")) {
                    $x = $s->getX();
                    $y = $s->getY();
                    $z = $s->getZ();
                    $levelname = $s->getLevel()->getName();
                    $level = $this->getServer()->getLevelByName($levelname);
                    if ($this->getUseHubServer() == false) {
                        if (!$hub->exists("hub")) {
                            $this->setHub($x, $y, $z, $level);
                            $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("hub-set")));
                            $s->getLevel()->addSound(new DoorBumpSound($s));
                            return true;
                        } else {
                            $this->setHub($x, $y, $z, $level);
                            $s->sendMessage($prefix . str_replace(["{world}"], [$levelname], MsgMgr::getMsg("hub-changed")));
                            $s->getLevel()->addSound(new DoorBumpSound($s));
                            return true;
                        }
                    } elseif ($this->getUseHubServer() == true) {
                        $s->sendMessage($prefix . MsgMgr::getMsg("hub-server-is-enabled"));
                        return true;
                    } else {
                        $s->sendMessage($prefix . MsgMgr::getMsg("false-config-setting"));
                    }
                } else {
                    $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
                    return true;
                }
            } else {
                $s->sendMessage(MsgMgr::getOnlyIGMsg());
                return true;
            }
        }
        if ($cmd->getName() == "delthehub") {
            if ($s instanceof Player) {
                if ($s->hasPermission("theSpawn.delthehub.cmd")) {
                    if ($hub->exists("hub")) {
                        $this->removeHub();
                        $s->sendMessage($prefix . MsgMgr::getMsg("hub-removed"));
                        $s->getLevel()->addSound(new GhastShootSound($s));
                        return true;
                    } else {
                        $s->sendMessage($prefix . MsgMgr::getMsg("no-hub-set"));
                    }
                } else {
                    $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
                }
            } else {
                $s->sendMessage(MsgMgr::getOnlyIGMsg());
                return true;
            }
        }
        if ($cmd->getName() == "hub") {
            if ($s instanceof Player) {
                if ($this->getUseHubServer() == false) {
                    if ($hub->exists("hub")) {
                        $hublevel = $this->getHubLevel();
                        $hublevelxd = $this->getServer()->getLevelByName($hublevel);
                        if ($this->getServer()->isLevelLoaded($hublevel) == true && !$hublevelxd == null) {
                            $s->teleport($this->getHub());
                            $s->sendMessage($prefix . str_replace(["{world}"], [$hublevelxd->getName()], MsgMgr::getMsg("hub-tp")));
                            $s->getLevel()->addSound(new PopSound($s));
                        } elseif ($hublevelxd == null) {
                            $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found-hub"));
                        } elseif (!$this->getServer()->isLevelLoaded($hublevel)) {
                            $this->getServer()->loadLevel($hublevel);
                            $s->teleport($this->getHub());
                            $s->sendMessage($prefix . str_replace(["{world}"], [$hublevelxd->getName()], MsgMgr::getMsg("hub-tp")));
                            $s->getLevel()->addSound(new PopSound($s));
                        }
                        return true;
                    } else {
                        $s->sendMessage($prefix . MsgMgr::getMsg("no-hub-set"));
                        return true;
                    }
                } elseif ($this->getUseHubServer() == true && $this->getUseWaterdogTransfer() == false) {
                    $this->teleportToHubServer($s);
                    return true;
                } elseif ($this->getUseHubServer() == true && $this->getUseWaterdogTransfer() == true) {
                    $this->teleportToHubServerWithWaterdog($s, $config->get("waterdog-servername"));
                    return true;
                } else {
                    $s->sendMessage($prefix . MsgMgr::getMsg("false-config-setting"));
                    return true;
                }
            } else {
                $s->sendMessage(MsgMgr::getOnlyIGMsg());
                return true;
            }
        }
        if ($cmd->getName() == "setalias") {
            if ($s instanceof Player) {
                if (!count($args) >= 2) {
                    $s->sendMessage("§4Use: §r/setalias <alias> <worldname>");
                    return true;
                }
                if (!$s->hasPermission("theSpawn.setalias.cmd")) {
                    $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
                    return true;
                }
                if (!is_string($args[0]) || !is_string($args[1])) {
                    $s->sendMessage("§4Use: §r/setalias <alias> <worldname>");
                    return true;
                }
                if ($this->existsLevel($args[1]) == false) {
                    $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found"));
                    return true;
                }
                if ($this->aliasCfg->get("use-aliases") == "false") {
                    $s->sendMessage($prefix . MsgMgr::getMsg("aliases-deactivated"));
                    return true;
                }
                $this->addAlias($args[0], $args[1]);
                $s->sendMessage($prefix . str_replace(["{alias}"], [$args[0]], str_replace(["{world}"], [$args[1]], MsgMgr::getMsg("alias-set"))));
                return true;
            }
        }
        if ($cmd->getName() == "removealias") {
            if (!$s instanceof Player) {
                $s->sendMessage(MsgMgr::getOnlyIGMsg());
                return true;
            }
            if (!$s->hasPermission("theSpawn.removealias.cmd")) {
                $s->sendMessage($prefix . MsgMgr::getNoPermMsg());
                return true;
            }
            if (!count($args) >= 1) {
                $s->sendMessage("§4Use: §r/removealias <alias>");
            }
            if ($this->existsAlias($args[0]) == false) {
                $s->sendMessage($prefix . MsgMgr::getMsg("alias-not-found"));
                return true;
            }
            $this->rmAlias($args[0]);
            $s->sendMessage($prefix . str_replace(["{alias}"], [$args[0]], MsgMgr::getMsg("alias-removed")));
            return true;
        }
        return true;
    }

    /**
     * @param PlayerRespawnEvent $event
     */
    public function onPlayerRespawn(PlayerRespawnEvent $event)
    {
        $prefix = "§f[§7the§eSpawn§f] §8»§r ";
        $s = $event->getPlayer();
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $levelname = $s->getLevel()->getName();
        if ($spawn->exists("hub")) {
            $X = $spawn->get("hub")["X"];
            $Y = $spawn->get("hub")["Y"];
            $Z = $spawn->get("hub")["Z"];
            $levelname = $spawn->get("hub")["level"];
            $level = $this->getServer()->getLevelByName($levelname);
            if ($this->getServer()->isLevelLoaded($levelname) == true && !$level == null) {
                $event->setRespawnPosition(new Position($X, $Y, $Z, $level));
                $s->getLevel()->addSound(new PopSound($s));
            } elseif ($level == null) {
                $s->sendMessage($prefix . MsgMgr::getMsg("world-not-found"));
                $s->teleport($this->getHub());
                $s->kick(MsgMgr::getMsg("no-spawn-found-kick"));
            } elseif (!$this->getServer()->isLevelLoaded($levelname)) {
                $this->getServer()->loadLevel($levelname);
                $event->setRespawnPosition(new Position($X, $Y, $Z, $level));
                $s->getLevel()->addSound(new PopSound($s));
            }
        }
    }

    /**
     * @param $x
     * @param $y
     * @param $z
     * @param Level $level
     * @return bool
     */
    public function setHub($x, $y , $z , Level $level): bool
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        $hubcoords = ["hub", "X" => $x, "Y" => $y, "Z" => $z, "level" => $level];
        $hub->set("hub", $hubcoords);
        return $hub->save();
    }

    /**
     * @return false|Position
     */
    public function getHub()
    {
        $prefix = "§f[§7the§eSpawn§f] §8»§r ";
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        if ($hub->exists("hub")) {
            $X = $hub->get("hub")["X"];
            $Y = $hub->get("hub")["Y"];
            $Z = $hub->get("hub")["Z"];
            $levelname = $hub->get("hub")["level"];
            $level = $this->getServer()->getLevelByName($levelname);
            $coords = new Position($X, $Y, $Z, $level);
            return $coords;
        } else {
            $this->getLogger()->error("!!Please set a Hub!!");
            return false;
        }
    }

    /**
     * @param Level $level
     * @return false|Position
     * @return false|Position
     */
    public function getSpawn(Level $level)
    {
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $spawn->get($level->getName());
        if ($spawn->exists($level->getName())) {
            $X = $spawn->get($level->getName())["X"];
            $Y = $spawn->get($level->getName())["Y"];
            $Z = $spawn->get($level->getName())["Z"];
            return new Position($X, $Y, $Z, $level);
        } else {
            return false;
        }
    }

    /**
     * @param Player $s
     * @param Level $level
     * @return bool
     */
    public function setSpawn(Player $s, Level $level): bool
    {
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $x = $s->getX();
        $y = $s->getY();
        $z = $s->getZ();
        $coords = ["X" => $x, "Y" => $y, "Z" => $z, "level" => $level->getName()];
        $spawn->set($level->getName(), $coords);
        return $spawn->save();
    }

    /**
     * @return false|mixed
     */
    public function getHubLevel(): bool
    {
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        if ($hub->exists("hub")) {
            $levelname = $hub->get("hub")["level"];
            return $levelname;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function removeHub(): bool
    {
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        if ($hub->exists("hub")) {
            $hub->remove("hub");
            return $hub->save();
        } else {
            return false;
        }
    }

    /**
     * @param Level $level
     * @return bool
     */
    public function removeSpawn(Level $level): bool
    {
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        if ($spawn->exists($level->getName())) {
            $spawn->remove($level->getName());
            return $spawn->save();
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function getUseHubServer(): bool
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($config->get("use-hub-server") === "true") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function getUseWaterdogTransfer(): bool
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($config->get("waterdog-hub-teleport") === "true") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Player $s
     * @return bool
     */
    public function teleportToHubServer(Player $s): bool
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        if ($this->getUseHubServer() == true) {
            return $s->transfer($config->get("hub-server-ip"), $config->get("hub-server-port"));
        } else {
            return false;
        }
    }

    public function teleportToHubServerWithWaterdog(Player $player, string $server) //Thanks to FlxiBoy
    {
        $pk = new ScriptCustomEventPacket();
        $pk->eventName = "bungeecord:main";
        $pk->eventData = Binary::writeShort(strlen("Connect"))."Connect".Binary::writeShort(strlen($server)).$server;
        $player->sendDataPacket($pk);
    }

    public function useAliases(): bool
    {
        if ($this->config->get("use-aliases") == "true") {
            return true;
        }
        return false;
    }

    public function getWorldOfAlias(string $alias): string
    {
        return $this->aliasCfg->get($alias);
    }

    public function existsLevel(string $levelName): bool
    {
        if ($this->getServer()->isLevelGenerated($levelName)) {
            return true;
        } else {
            return false;
        }
    }

    public function existsAlias(string $alias): bool
    {
        if ($this->aliasCfg->exists($alias)) {
            return true;
        } else {
            return false;
        }
    }

    public function rmAlias(string $alias): bool
    {
        if ($this->existsAlias($alias) == true) {
            $cmd = $this->getServer()->getCommandMap()->getCommand($alias);
            $this->getServer()->getCommandMap()->unregister($cmd);
            $this->aliasCfg->remove($alias);
            $this->aliasCfg->save();
            return true;
        } else {
            return false;
        }
    }

    public function addAlias(string $alias, string $levelName): bool
    {
        $level = $this->getServer()->getLevelByName($levelName);
        if ($this->getSpawn($level) == false) {
            return false;
        }
        $this->aliasCfg->set($alias, $levelName);
        $this->aliasCfg->save();
        $this->getServer()->getCommandMap()->register($alias, new Aliases($this, $alias, str_replace(["{alias}"], [$alias], str_replace(["{world}"], [$levelName], MsgMgr::getMsg("alias-command-description")))));
        return true;
    }

    public function reactivateAliases()
    {
        foreach ($this->aliasCfg->getAll() as $cmd => $worldName) {
            $this->getServer()->getCommandMap()->register($cmd, new Aliases($this, $cmd, str_replace(["{alias}"], [$cmd], str_replace(["{world}"], [$worldName], MsgMgr::getMsg("alias-command-description")))));
        }
    }
}