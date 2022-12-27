<?php

namespace supercrafter333\theSpawn;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\PopSound;

/**
 * Class EventListener.php
 * @package supercrafter333\theSpawn
 */
class EventListener implements Listener
{

    /**
     * @param PlayerMoveEvent $event
     */
    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        if (SpawnDelayManager::hasSpawnDelay($player) && !SpawnDelayManager::getSpawnDelayTaskOf($player)->getStartPosition()->equals($player->getPosition())) {
            SpawnDelayManager::stopSpawnDelay($player);
            $player->sendMessage(theSpawn::$prefix . MsgMgr::getMsg("delay-stopped-by-move"));
            $player->broadcastSound(new AnvilFallSound(), [$player]);
        }
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        if (SpawnDelayManager::hasSpawnDelay($player))
            SpawnDelayManager::stopSpawnDelay($player);
    }

    /**
     * @param PlayerRespawnEvent $event
     */
    public function onPlayerRespawn(PlayerRespawnEvent $event): void
    {
        $pl = theSpawn::getInstance();
        $hubMgr = HubManager::getInstance();
        $s = $event->getPlayer();
        $world = $s->getWorld();

        if (ConfigManager::getInstance()->useHubTeleportOnDeath() && $hubMgr->getHub() instanceof Position) {
            $event->setRespawnPosition($hubMgr->getHub());
            return;
        }

        if ($world === null) {
            if ($hubMgr->getHub() instanceof Position) $event->setRespawnPosition($hubMgr->getHub());
            else $event->setRespawnPosition($pl->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
        }

        if ($pl->getSpawn($world) instanceof Position) {
            $event->setRespawnPosition($pl->getSpawn($world));
            $s->broadcastSound(new PopSound(), [$s]);
        } elseif ($hubMgr->getHub() instanceof Position) {
            $event->setRespawnPosition($hubMgr->getHub());
            $s->broadcastSound(new PopSound(), [$s]);
        } else {
            if ($world->getSafeSpawn() === null) $event->setRespawnPosition($this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
            else $event->setRespawnPosition($world->getSafeSpawn());
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onPlayerLogin(PlayerJoinEvent $event)
    {
        if (ConfigManager::getInstance()->useHubTeleportOnJoin()) {
            if (($hub = HubManager::getInstance()->getHub()) instanceof Position)
                $event->getPlayer()->teleport($hub);
            elseif ($this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn() !== null)
                $event->getPlayer()->teleport($this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $ev): void
    {
        $player = $ev->getPlayer();
        if (ConfigManager::getInstance()->useBackCommand()) LastDeathPositionManager::setLastDeathPosition($player, $player->getLocation());
    }

    /**
     * @return Server
     */
    private function getServer(): Server
    {
        return theSpawn::getInstance()->getServer();
    }
}