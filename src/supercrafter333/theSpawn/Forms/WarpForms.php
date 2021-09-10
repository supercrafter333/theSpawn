<?php

namespace supercrafter333\theSpawn\Forms;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

class WarpForms
{

    public function open(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            theSpawn::getInstance()->getServer()->dispatchCommand($player, "warp $result");
        });
        $form->setTitle(MsgMgr::getMsg("form-warp-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-warp-menu-content"));
        foreach (theSpawn::getInstance()->getWarpCfg()->getAll() as $warp => $warpN) {
            $warpName = $warpN["warpName"];
            $form->addButton(str_replace(["{warp}", "{line}"], [$warpName, "\n"], MsgMgr::getMsg("form-warp-menu-homeButton")), -1, "", $warpName);
        }
        $form->sendToPlayer($player);
        return $form;
    }
}