<?php

namespace supercrafter333\theSpawn\form;

use EasyUI\element\Button;
use EasyUI\element\Input;
use EasyUI\element\Label;
use EasyUI\utils\FormResponse;
use EasyUI\variant\CustomForm;
use EasyUI\variant\SimpleForm;
use pocketmine\player\Player;
use pocketmine\world\sound\XpLevelUpSound;
use supercrafter333\theSpawn\commands\home\{DelhomeCommand, HomeCommand, SethomeCommand};
use supercrafter333\theSpawn\events\other\EditHomeEvent;
use supercrafter333\theSpawn\home\Home;
use supercrafter333\theSpawn\home\HomeManager;
use supercrafter333\theSpawn\MsgMgr;
use function mt_rand;

class HomeForms
{

    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function open(Player $player): SimpleForm
    {
        $form = new SimpleForm(MsgMgr::getMsg("form-home-menu-title"));
        $form->setHeaderText(MsgMgr::getMsg("form-home-menu-content"));

        foreach (HomeManager::getHomesOfPlayer($player) as $home)
            $form->addButton(new Button(MsgMgr::getMsg("form-home-menu-homeButton", ["{home}" => $home, "{line}" => "\n"]), null,
            function (Player $player) use ($home): void {
                HomeCommand::simpleExecute($player, [$home]);
            }));

        return $form;
    }

    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function openRmHome(Player $player): SimpleForm
    {
        $form = new SimpleForm(MsgMgr::getMsg("form-rmHome-menu-title"));
        $form->setHeaderText(MsgMgr::getMsg("form-rmHome-menu-content"));

        $homes = HomeManager::getHomesOfPlayer($player);
        sort($homes);
        foreach ($homes as $home)
            $form->addButton(new Button(MsgMgr::getMsg("form-rmHome-menu-homeButton", ["{home}" => $home, "{line}" => "\n"]), null,
            function (Player $player) use ($home): void {
                DelhomeCommand::simpleExecute($player, [$home]);
            }));

        return $form;
    }

    /**
     * @param Player $player
     * @return CustomForm
     */
    public function openSetHome(Player $player): CustomForm
    {
        $form = new CustomForm(MsgMgr::getMsg("form-setHome-menu-title"),
            function (Player $player, FormResponse $response) {
            if (($home = $response->getInputSubmittedText("home")) !== null && $home !== "") {
                SethomeCommand::simpleExecute($player, [$home]);
            }
        });

        $form->addElement("label", new Label(MsgMgr::getMsg("form-setHome-menu-content")));
        $form->addElement("home", new Input(MsgMgr::getMsg("form-setHome-menu-inputDescription")));

        return $form;
    }

    public function openChooseEditHome(Player $player): SimpleForm
    {
        $form = new SimpleForm(MsgMgr::getMsg("form-chooseEditHome-menu-title"));
        $form->setHeaderText(MsgMgr::getMsg("form-chooseEditHome-menu-content"));

        $homes = HomeManager::getHomesOfPlayer($player);
        sort($homes);
        foreach ($homes as $home) {
            $form->addButton(new Button(
                MsgMgr::getMsg("form-chooseEditHome-menu-homeButton", ["{home}" => $home, "{line}" => "\n"]), null,
            function (Player $player) use ($home): void {
                    if (($home = HomeManager::getHome($home, $player)) instanceof Home)
                        $player->sendForm($this->openEditHome($player, $home));
            }));
        }

        return $form;
    }

    public function openEditHome(Player $player, Home $home): SimpleForm
    {
        $form = new SimpleForm(MsgMgr::getMsg("form-editHome-menu-title"));
        $form->setHeaderText(MsgMgr::getMsg("form-editHome-menu-content", [
            "{home}" => $home->getName(),
            "{world}" => $home->getLocation()->getWorld()->getFolderName(),
            "{X}" => $home->getLocation()->getFloorX(),
            "{Y}" => $home->getLocation()->getFloorY(),
            "{Z}" => $home->getLocation()->getFloorZ(),
            "{yaw}" => $home->getLocation()->getYaw(),
            "{pitch}" => $home->getLocation()->getPitch()
        ]));

        $form->addButton(new Button(MsgMgr::getMsg("form-editHome-menu-editNameButton"), null,
        function (Player $player) use ($home): void {
            $player->sendForm($this->openEditHomeName($player, $home));
        }));

        $form->addButton(new Button(MsgMgr::getMsg("form-editHome-menu-editPositionButton"), null,
        function (Player $player) use ($home): void {
            $ev = new EditHomeEvent($home);
            $ev->call();
            if ($ev->isCancelled()) return;

            $home->setLocation($player->getLocation());
            $home->save();
            $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
            $player->sendForm($this->openEditHome($player, HomeManager::getHome($home->getName(), $home->getPlayer())));
        }));

        return $form;
    }

    public function openEditHomeName(Player $player, Home $home): CustomForm
    {
        $form = new CustomForm(MsgMgr::getMsg("form-editHome-editName-title"),
            function (Player $player, FormResponse $response) use ($home) {
            $newHome = $response->getInputSubmittedText("home");
            if ($newHome !== null && $newHome !== "" && mb_strlen($newHome) >= 1) {
                $ev = new EditHomeEvent($home);
                $ev->call();
                if ($ev->isCancelled()) return;

                HomeManager::removeHome($home);
                HomeManager::createHome(new Home($player, $newHome, $home->getLocation()));
                $player->broadcastSound(new XpLevelUpSound(mt_rand()), [$player]);
                $player->sendForm($this->openEditHome($player, HomeManager::getHome($newHome, $player)));
            }
        });
        $form->addElement("label", new Label(MsgMgr::getMsg("form-editHome-editName-content", ["{home}" => $home->getName()])));
        $form->addElement("home", new Input(MsgMgr::getMsg("form-editHome-editName-inputNameDescription"), $home->getName()));

        return $form;
    }
}