<?php

namespace supercrafter333\theSpawn;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;
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
        $config->set("#1", "Gebe bei use-hub-server eine 1 für Nein und eine 2 für Ja ein");
        $config->save();
    }

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
                    $slevelname = $s->getLevel()->getName();
                    if (!$spawn->exists($slevelname)) {
                        $coords = ["X" => $x, "Y" => $y, "Z" => $z, "level" => $slevelname];
                        $spawn->set($slevelname, $coords);
                        $spawn->save();
                        $s->sendMessage($prefix . "§aDu hast den Spawn dieser Welt gesetzt!");
                        $s->getLevel()->addSound(new DoorBumpSound($s));
                        return true;
                    } else {
                        $x = $s->getX();
                        $y = $s->getY();
                        $z = $s->getZ();
                        $slevelname = $s->getLevel()->getName();
                        $coords = ["X" => $x, "Y" => $y, "Z" => $z, "level" => $slevelname];
                        $spawn->set($slevelname, $coords);
                        $spawn->save();
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
                    $slevelname = $s->getLevel()->getName();
                    if ($spawn->exists($slevelname)) {
                        $spawn->remove($slevelname);
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
                $slevelname = $s->getLevel()->getName();
                if ($spawn->exists($slevelname)) {
                    $X = $spawn->get($slevelname)["X"];
                    $Y = $spawn->get($slevelname)["Y"];
                    $Z = $spawn->get($slevelname)["Z"];
                    $levelname = $spawn->get($slevelname)["level"];
                    $level = $this->getServer()->getLevelByName($levelname);
                    $s->teleport(new Position($X, $Y, $Z, $level));
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
                    $slevelname = $s->getLevel()->getName();
                    if ($config->get("use-hub-server") == "false") {
                        if (!$hub->exists("hub")) {
                            $hubcoords = ["hub", "X" => $x, "Y" => $y, "Z" => $z, "level" => $slevelname];
                            $hub->set("hub", $hubcoords);
                            $hub->save();
                            $s->sendMessage($prefix . "§aDu hast den Hub dieses Servers gesetzt!");
                            $s->getLevel()->addSound(new DoorBumpSound($s));
                            return true;
                        } else {
                            $hubcoords = ["hub", "X" => $x, "Y" => $y, "Z" => $z, "level" => $slevelname];
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
                        $hubcoords2 = new Position($hX, $hY, $hZ, $hublevel);
                        $s->teleport($hubcoords2);
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

    public function onPlayerRespawn(PlayerRespawnEvent $event)
    {
        $s = $event->getPlayer();
        $spawn = new Config($this->getDataFolder() . "theSpawns.yml", Config::YAML);
        $slevelname = $s->getLevel()->getName();
        if ($spawn->exists($slevelname)) {
            $X = $spawn->get($slevelname)["X"];
            $Y = $spawn->get($slevelname)["Y"];
            $Z = $spawn->get($slevelname)["Z"];
            $levelname = $spawn->get($slevelname)["level"];
            $level = $this->getServer()->getLevelByName($levelname);
            $event->setRespawnPosition(new Position($X, $Y, $Z, $level));
            $s->getLevel()->addSound(new PopSound($s));
        }
    }
}
