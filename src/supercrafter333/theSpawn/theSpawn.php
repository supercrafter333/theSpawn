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
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class theSpawn extends PluginBase implements Listener
{

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getConfig();
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $config = new Config($this->getDataFolder()."config.yml", Config::YAML);
        $config->save();
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
        $this->getConfig();
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $config = new Config($this->getDataFolder()."config.yml", Config::YAML);
        $config->save();
        if ($cmd->getName() == "setthespawn") {
            if ($s instanceof Player) {
                if ($s->hasPermission("setthespawn.cmd")) {
                    $x = $s->getX();
                    $y = $s->getY();
                    $z = $s->getZ();
                    $levelname = $s->getLevel()->getName();
                    $level = $s->getLevel();
                    if (!$spawn->exists($levelname)) {
                        $this->setSpawn($s, $level);
                        $s->sendMessage($prefix . "§aDu hast den Spawn dieser Welt gesetzt!");
                        $s->getLevel()->addSound(new DoorBumpSound($s));
                        return true;
                    } else {
                        $this->setSpawn($s, $level);
                        $s->sendMessage($prefix . "§aDer Spawn wurde umgesetzt!");
                        $s->getLevel()->addSound(new DoorBumpSound($s));
                        return true;
                    }
                } else {
                    $s->sendMessage($prefix . "§cDu bist dazu nicht berechtigt!");
                    return true;
                }
            } else {
                $s->sendMessage("Nur In-Game vefügbar!");
                return true;
            }
        }
        if ($cmd->getName() == "delthespawn") {
            if ($s instanceof Player) {
                if ($s->hasPermission("delthespawn.cmd")) {
                    $x = $s->getX();
                    $y = $s->getY();
                    $z = $s->getZ();
                    $levelname = $s->getLevel()->getName();
                    if ($spawn->exists($levelname)) {
                        $spawn->remove($levelname);
                        $spawn->save();
                        $s->sendMessage($prefix . "§aDer Spawn dieser Welt wurde entfernt!");
                        $s->getLevel()->addSound(new GhastShootSound($s));
                        return true;
                    } else {
                        $s->sendMessage($prefix . "§cIn dieser Welt existiert noch kein Spawn!");
                        return true;
                    }
                } else {
                    $s->sendMessage($prefix . "§cDu bist dazu nicht berechtigt!");
                    return true;
                }
            } else {
                $s->sendMessage("Nur In-Game vefügbar!");
                return true;
            }
        }
        if ($cmd->getName() == "spawn") {
            if ($s instanceof Player) {
                $levelname = $s->getLevel()->getName();
                $level = $s->getLevel();
                if ($spawn->exists($levelname)) {
                    $s->teleport($this->getSpawn($level));
                    $s->sendMessage($prefix . "§aDu wurdest zum Spawn dieser Welt Teleportiert!");
                    $s->getLevel()->addSound(new PopSound($s));
                    return true;
                } else {
                    $s->sendMessage($prefix . "§4ERROR! --> §cIn dieser Welt existiert noch kein Spawn!");
                    return true;
                }
            } else {
                $s->sendMessage("Nur In-Game verfügbar!");
                return true;
            }
        }
        if ($cmd->getName() == "setthehub") {
            if ($s instanceof Player) {
                if ($s->hasPermission("setthehub.cmd")) {
                    $x = $s->getX();
                    $y = $s->getY();
                    $z = $s->getZ();
                    $levelname = $s->getLevel()->getName();
                    if ($config->get("use-hub-server") == "false") {
                        if (!$hub->exists("hub")) {
                            $hubcoords = ["hub", "X" => $x, "Y" => $y, "Z" => $z, "level" => "hub"];
                            $hub->set("hub", $hubcoords);
                            $hub->save();
                            $s->sendMessage($prefix . "§aDu hast den Hub dieses Servers gesetzt!");
                            $s->getLevel()->addSound(new DoorBumpSound($s));
                            return true;
                        } else {
                            $hubcoords = ["hub", "X" => $x, "Y" => $y, "Z" => $z, "level" => "hub"];
                            $hub->set("hub", $hubcoords);
                            $hub->save();
                            $s->sendMessage($prefix . "§aDu hast den Hub dieses Servers umgesetzt!");
                            $s->getLevel()->addSound(new DoorBumpSound($s));
                            return true;
                        }
                    } elseif ($config->get("use-hub-server") == "true") {
                        $s->sendMessage($prefix . "§7'use-hub-server' §cist auf §7'true' §cweswegen du keine Lobby setzen kannst!");
                        return true;
                    } else {
                        $s->sendMessage("§l§4FATALER FEHLER --> §eFalsche einstellung in der Config! §r§7(§buse-hub-server: <true|false>§7)");
                    }
                } else {
                    $s->sendMessage($prefix . "§cDu bist dazu nicht berechtigt!");
                    return true;
                }
            } else {
                $s->sendMessage("Nur In-Game verfügbar!");
                return true;
            }
        }
        if ($cmd->getName() == "delthehub") {
            if ($s instanceof Player) {
                if ($s->hasPermission("delthehub.cmd")) {
                    if ($hub->exists("hub")) {
                        $hub->remove("hub");
                        $hub->save();
                        $s->sendMessage($prefix . "§aDu hast den Hub Spawnpunkt entfernt!");
                        $s->getLevel()->addSound(new GhastShootSound($s));
                        return true;
                    } else {
                        $s->sendMessage($prefix."§cEs wurde noch keine Lobby gesetzt!");
                    }
                } else {
                    $s->sendMessage($prefix . "§cDu bist dazu nicht berechtigt!");
                }
            } else {
                $s->sendMessage("Nur In-Game verfügbar!");
                return true;
            }
        }
        if ($cmd->getName() == "hub") {
            if ($s instanceof Player) {
                if ($config->get("use-hub-server") == "false") {
                    if ($hub->exists("hub")) {
                        $hX = $hub->get("hub")["X"];
                        $hY = $hub->get("hub")["Y"];
                        $hZ = $hub->get("hub")["Z"];
                        $hublevel = $hub->get("hub")["level"];
                        $hublevelxd = $this->getServer()->getLevelByName($hublevel);
                        $hubcoords2 = new Position($hX, $hY, $hZ, $hublevelxd);
                        if ($this->getServer()->isLevelLoaded($hublevel) == true && !$hublevelxd == null) {
                            $s->teleport($hubcoords2);
                            $s->sendMessage($prefix."§aDu wurdest zum Spawn der Lobby Teleportiert!");
                            $s->getLevel()->addSound(new PopSound($s));
                        } elseif ($hublevelxd == null) {
                            $s->sendMessage($prefix . "§4Welt konnte nicht gefunden werden!");
                        } elseif (!$this->getServer()->isLevelLoaded($hublevel)) {
                            $this->getServer()->loadLevel($hublevel);
                            $s->teleport($hubcoords2);
                            $s->sendMessage($prefix."§aDu wurdest zum Spawn der Lobby Teleportiert!");
                            $s->getLevel()->addSound(new PopSound($s));
                        }
                        return true;
                    } else {
                        $s->sendMessage($prefix . "§4ERROR! --> §cEs wurde noch keine Lobby festgelegt!");
                        return true;
                    }
                } elseif ($config->get("use-hub-server") == "true") {
                    $hubserver = new TransferPacket();
                    $hubserver->address = $config->get("hub-server-ip");
                    $hubserver->port = $config->get("hub-server-port");
                    $s->dataPacket($hubserver);
                    return true;
                } else {
                    $s->sendMessage($prefix . "§l§4FATALER FEHLER --> §eFalsche einstellung in der Config! §r§7(§buse-hub-server: <true|false>§7)");
                    return true;
                }
            } else {
                $s->sendMessage("Nur In-Game verfügbar!");
                return true;
            }
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
                $s->sendMessage($prefix . "§4Welt konnte nicht gefunden werden!");
                $s->teleport($this->getHub());
                $s->kick("§cDie Welt konnte nicht gefunden werden!\n§rBitte rejoine. Sollte das Problem weiterhin bestehen,\nKontaktiere bitte den Support!");
            } elseif (!$this->getServer()->isLevelLoaded($levelname)) {
                $this->getServer()->loadLevel($levelname);
                $event->setRespawnPosition(new Position($X, $Y, $Z, $level));
                $s->getLevel()->addSound(new PopSound($s));
            }
        }
    }

    /**
     * @param float|int $x
     * @param float|int $y
     * @param float|int $z
     * @param string $levelname
     * @return bool
     */
    public function setHub(float $x, float $y, float $z, string $levelname)
    {
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $hub = new Config($this->getDataFolder() . "theHub.yml", Config::YAML);
        $hubcoords = ["hub", "X" => $x, "Y" => $y, "Z" => $z, "level" => "hub"];
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
            $this->getLogger()->error("!!Es wurde noch keine Lobby/Hub gesetzt! Bitte setze dringend eine Lobby/Hub!!");
            return false;
        }
    }

    /**
     * @param Level $level
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

    public function setSpawn(Player $s, Level $level)
    {
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $x = $s->getX();
        $y = $s->getY();
        $z = $s->getZ();
        $coords = ["X" => $x, "Y" => $y, "Z" => $z, "level" => $level->getName()];
        $spawn->set($level->getName(), $coords);
        return $spawn->save();
    }
}