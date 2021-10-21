<?php

namespace supercrafter333\theSpawn\Forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

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
    public function open(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            theSpawn::getInstance()->getServer()->dispatchCommand($player, "home $result");
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
}