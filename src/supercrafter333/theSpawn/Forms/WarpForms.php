<?php

namespace supercrafter333\theSpawn\Forms;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\player\Player;
use supercrafter333\theSpawn\Commands\DelwarpCommand;
use supercrafter333\theSpawn\Commands\SetwarpCommand;
use supercrafter333\theSpawn\Commands\WarpCommand;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\theSpawn;

/**
 *
 */
class WarpForms
{

    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function open(Player $player): ?SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) return;

            WarpCommand::simpleExecute($player, [$result]);
        });
        $form->setTitle(MsgMgr::getMsg("form-warp-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-warp-menu-content"));
        foreach (theSpawn::getInstance()->getWarpCfg()->getAll() as $warp => $warpN) {
            $warpName = $warpN["warpName"];
            $form->addButton(str_replace(["{warp}", "{line}"], [$warpName, "\n"], MsgMgr::getMsg("form-warp-menu-warpButton")), -1, "", $warpName);
        }
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function openRmWarp(Player $player): ?SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $result = $data;
            if ($result === null) return;

            DelwarpCommand::simpleExecute($player, [$result]);
            return;
        });
        $form->setTitle(MsgMgr::getMsg("form-rmWarp-menu-title"));
        $form->setContent(MsgMgr::getMsg("form-rmWarp-menu-content"));
        foreach (theSpawn::getInstance()->getWarpCfg()->getAll() as $warp => $warpN) {
            $warpName = $warpN["warpName"];
            $form->addButton(str_replace(["{warp}", "{line}"], [$warpName, "\n"], MsgMgr::getMsg("form-rmWarp-menu-warpButton")), -1, "", $warpName);
        }
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @param Player $player
     * @return CustomForm
     */
    public function openSetWarp(Player $player): ?CustomForm
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data === null) return;

            if (isset($data["warpName"])) {
                if (isset($data["warpPermission"])) {
                    SetwarpCommand::simpleExecute($player, [$data["warpName"], $data["warpPermission"]]);
                    return;
                }

                SetwarpCommand::simpleExecute($player, [$data["warpName"]]);
                return;
            }
        });
        $form->setTitle(MsgMgr::getMsg("form-setWarp-menu-title"));
        $form->addLabel(MsgMgr::getMsg("form-setWarp-menu-content"));
        $form->addInput(MsgMgr::getMsg("form-setWarp-menu-inputNameDescription"), "", null, "warpName");
        $form->addInput(MsgMgr::getMsg("form-setWarp-menu-inputPermDescription"), "", null, "warpPermission");
        $form->sendToPlayer($player);
        return $form;
    }
}