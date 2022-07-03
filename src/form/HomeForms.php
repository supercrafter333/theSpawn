<?php

namespace supercrafter333\theSpawn\form;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\entity\Location;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\sound\XpLevelUpSound;
use supercrafter333\theSpawn\commands\DelhomeCommand;
use supercrafter333\theSpawn\commands\HomeCommand;
use supercrafter333\theSpawn\commands\SethomeCommand;
use supercrafter333\theSpawn\home\HomeInfo;
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
        foreach (theSpawn::getInstance()->getHomeCfg($this->playerName)->getAll() as $home => $homeN) {
            $homeName = $homeN["homeName"];
            $form->addButton(str_replace(["{home}", "{line}"], [$homeName, "\n"], MsgMgr::getMsg("form-home-menu-homeButton")), -1, "", $homeName);
        }
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
        foreach (theSpawn::getInstance()->getHomeCfg($this->playerName)->getAll() as $home => $homeN) {
            $homeName = $homeN["homeName"];
            $form->addButton(str_replace(["{home}", "{line}"], [$homeName, "\n"], MsgMgr::getMsg("form-rmHome-menu-homeButton")), -1, "", $homeName);
        }
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

            if (($home = theSpawn::getInstance()->getHomeInfo($player, $result)) === null) return;

            $this->openEditHome($player, $home);
        });
        $form->setTitle(MsgMgr::getMsg("form-chooseEditHome-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-chooseEditHome-menu-content"));
        foreach (theSpawn::getInstance()->getHomeCfg($this->playerName)->getAll() as $home => $homeN) {
            $homeName = $homeN["homeName"];
            $form->addButton(str_replace(["{home}", "{line}"], [$homeName, "\n"], MsgMgr::getMsg("form-chooseEditHome-menu-homeButton")), -1, "", $homeName);
        }
        $form->sendToPlayer($player);
        return $form;
    }

    public function openEditHome(Player $player, HomeInfo $home): SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) use ($home) {
            $result = $data;
            if ($result === null) return;

            $editHome = function (string $homeName, Location|Position $pos) use ($player, $home) {
                $pl = theSpawn::getInstance();
                $pl->rmHome($player, $home->getName());
                $pl->setHome(
                    $player,
                    $homeName,
                    $pos->getX(),
                    $pos->getY(),
                    $pos->getZ(),
                    $pos->getWorld(),
                    ($pos instanceof Location ? $pos->getYaw() : null),
                    ($pos instanceof Location ? $pos->getPitch() : null)
                );
            };

            if ($result == "editName") {
                $this->openEditHomeName($player, $home);
                return;
            }

            if ($result == "editPosition") {
                $editHome($home->getName(), $player->getLocation());
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditHome($player, theSpawn::getInstance()->getHomeInfo($player, $home->getName()));
                return;
            }
        });
        $form->setTitle(MsgMgr::getMsg("form-editHome-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-editHome-menu-content", [
            "{home}" => $home->getName(),
            "{world}" => $home->getLevelName(),
            "{X}" => $home->getX(),
            "{Y}" => $home->getY(),
            "{Z}" => $home->getZ(),
            "{yaw}" => ($home->getYaw() !== null ? $home->getYaw() : "---"),
            "{pitch}" => ($home->getPitch() !== null ? $home->getPitch() : "---")
        ]));
        $form->addButton(MsgMgr::getMsg("form-editHome-menu-editNameButton"), -1, "", "editName");
        $form->addButton(MsgMgr::getMsg("form-editHome-menu-editPositionButton"), -1, "", "editPosition");
        $form->sendToPlayer($player);
        return $form;
    }

    public function openEditHomeName(Player $player, HomeInfo $home): CustomForm
    {
        $form = new CustomForm(function (Player $player, array $data = null) use ($home) {
            if ($data === null) return;

            $pl = theSpawn::getInstance();

            $editHome = function (string $homeName, Location|Position $pos) use ($player, $home, $pl) {
                $pl->rmHome($player, $home->getName());
                $pl->setHome(
                    $player,
                    $homeName,
                    $pos->getX(),
                    $pos->getY(),
                    $pos->getZ(),
                    $pos->getWorld(),
                    ($pos instanceof Location ? $pos->getYaw() : null),
                    ($pos instanceof Location ? $pos->getPitch() : null)
                );
            };

            if (isset($data["homeName"]) && mb_strlen($data["homeName"]) >= 1) {
                $editHome($data["homeName"], $pl->getHomePos($player, $home->getName()));
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $this->openEditHome($player, $pl->getHomeInfo($player, $data["homeName"]));
                return;
            }
        });
        $form->setTitle(MsgMgr::getMsg("form-editHome-editName-title"));
        $form->addLabel(MsgMgr::getMsg("form-editHome-editName-content", ["{home}" => $home->getName()]));
        $form->addInput(MsgMgr::getMsg("form-editHome-editName-inputNameDescription"), "", $home->getName(), "homeName");
        $form->sendToPlayer($player);
        return $form;
    }
}