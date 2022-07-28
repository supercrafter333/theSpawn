<?php

namespace supercrafter333\theSpawn\form;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use pocketmine\world\sound\XpLevelUpSound;
use supercrafter333\theSpawn\commands\home\{DelhomeCommand, HomeCommand, SethomeCommand};
use supercrafter333\theSpawn\events\other\EditHomeEvent;
use supercrafter333\theSpawn\home\Home;
use supercrafter333\theSpawn\home\HomeManager;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;
use function mt_rand;

/**
 *
 */
class HomeForms
{

    /**
     * @param string $playerName
     */
    public function __construct(private string $playerName) {}

    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function open(Player $player): SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) return;

            HomeCommand::simpleExecute($player, [$result]);
        });
        $form->setTitle(MsgMgr::getMsg("form-home-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-home-menu-content"));
        foreach (HomeManager::getHomesOfPlayer($player) as $home)
            $form->addButton(MsgMgr::getMsg("form-home-menu-homeButton", ["{home}" => $home, "{line}" => "\n"]), -1, "", $home);
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function openRmHome(Player $player): ?SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) return;

            DelhomeCommand::simpleExecute($player, [$result]);
        });
        $form->setTitle(MsgMgr::getMsg("form-rmHome-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-rmHome-menu-content"));
        foreach (HomeManager::getHomesOfPlayer($player) as $home)
            $form->addButton(MsgMgr::getMsg("form-rmHome-menu-homeButton", ["{home}" => $home, "{line}" => "\n"]), -1, "", $home);
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @param Player $player
     * @return CustomForm
     */
    public function openSetHome(Player $player): ?CustomForm
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data === null) return;

            if (isset($data["homeName"])) {
                SethomeCommand::simpleExecute($player, [$data["homeName"]]);
                return;
            }
        });
        $form->setTitle(MsgMgr::getMsg("form-setHome-menu-title"));
        $form->addLabel(MsgMgr::getMsg("form-setHome-menu-content"));
        $form->addInput(MsgMgr::getMsg("form-setHome-menu-inputDescription"), "", null, "homeName");
        $form->sendToPlayer($player);
        return $form;
    }

    public function openChooseEditHome(Player $player): SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) return;

            if (!($home = HomeManager::getHome($result, $player)) instanceof Home) return;

            $this->openEditHome($player, $home);
        });
        $form->setTitle(MsgMgr::getMsg("form-chooseEditHome-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-chooseEditHome-menu-content"));
        foreach (HomeManager::getHomesOfPlayer($player) as $home) {
            $form->addButton(MsgMgr::getMsg("form-chooseEditHome-menu-homeButton", ["{home}" => $home, "{line}" => "\n"]), -1, "", $home);
        }
        $form->sendToPlayer($player);
        return $form;
    }

    public function openEditHome(Player $player, Home $home): SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) use ($home) {
            $result = $data;
            if ($result === null) return;

            if ($result == "editName") {
                $this->openEditHomeName($player, $home);
                return;
            }

            if ($result == "editPosition") {
                $ev = new EditHomeEvent($home);
                $ev->call();
                if ($ev->isCancelled()) return;

                $home->setLocation($player->getLocation());
                $home->save();
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditHome($player, HomeManager::getHome($home->getName(), $home->getPlayer()));
                return;
            }
        });
        $form->setTitle(MsgMgr::getMsg("form-editHome-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-editHome-menu-content", [
            "{home}" => $home->getName(),
            "{world}" => $home->getLocation()->getWorld()->getFolderName(),
            "{X}" => $home->getLocation()->getX(),
            "{Y}" => $home->getLocation()->getY(),
            "{Z}" => $home->getLocation()->getZ(),
            "{yaw}" => $home->getLocation()->getYaw(),
            "{pitch}" => $home->getLocation()->getPitch()
        ]));
        $form->addButton(MsgMgr::getMsg("form-editHome-menu-editNameButton"), -1, "", "editName");
        $form->addButton(MsgMgr::getMsg("form-editHome-menu-editPositionButton"), -1, "", "editPosition");
        $form->sendToPlayer($player);
        return $form;
    }

    public function openEditHomeName(Player $player, Home $home): CustomForm
    {
        $form = new CustomForm(function (Player $player, array $data = null) use ($home) {
            if ($data === null) return;

            $pl = theSpawn::getInstance();


            if (isset($data["homeName"]) && mb_strlen($data["homeName"]) >= 1) {
                $ev = new EditHomeEvent($home);
                $ev->call();
                if ($ev->isCancelled()) return;

                HomeManager::removeHome($home);
                HomeManager::createHome(new Home($player, $data["homeName"], $home->getLocation()));
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditHome($player, HomeManager::getHome($data["homeName"], $player));
                return;
            }
        });
        $form->setTitle(MsgMgr::getMsg("form-editHome-editName-title"));
        $form->addLabel(MsgMgr::getMsg("form-editHome-editName-content", ["{home}" => $home->getName()]));
        $form->addInput(MsgMgr::getMsg("form-editHome-editName-inputNameDescription"), "", $home->getName(), "homeName");
        $form->sendToPlayer($player);
        return $form;
    }

    private function canEditHome(Home $home): bool
    {
        $ev = new EditHomeEvent($home);
        $ev->call();

        return !$ev->isCancelled();
    }
}