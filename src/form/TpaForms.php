<?php

namespace supercrafter333\theSpawn\form;

use EasyUI\element\Input;
use EasyUI\element\Toggle;
use EasyUI\utils\FormResponse;
use EasyUI\variant\CustomForm;
use EasyUI\variant\ModalForm;
use pocketmine\player\Player;
use supercrafter333\theSpawn\events\tpa\TpaAnswerEvent;
use supercrafter333\theSpawn\MsgMgr;
use supercrafter333\theSpawn\task\TpaTask;
use supercrafter333\theSpawn\theSpawn;
use supercrafter333\theSpawn\tpa\Tpa;
use supercrafter333\theSpawn\tpa\TpaManager;
use function array_keys;

class TpaForms
{

    public static function menu(Player $player): ModalForm|CustomForm
    {
        $tpas = TpaManager::getTPAsOf($player->getName());
        if ($tpas === null)
            return self::sendTpa();

        $tpaInfo = $tpas[array_keys($tpas)[0]];
        $tpa = (isset($tpaInfo["task"]) && $tpaInfo["task"] instanceof TpaTask) ? $tpaInfo["task"]->getTpa() : null;
        if (!$tpa instanceof Tpa) return self::sendTpa();

        if (!$tpa->isTpaHere()) {
            $form = new ModalForm(MsgMgr::getMsg("form-tpa-answerTpa-title"), MsgMgr::getMsg("form-tpa-answerTpa-content", ["{source}" => $tpa->getSource()]));
            $form->setAcceptText(MsgMgr::getMsg("form-tpa-answerTpa-acceptText"));
            $form->setDenyText(MsgMgr::getMsg("form-tpa-answerTpa-denyText"));
        } else {
            $form = new ModalForm(MsgMgr::getMsg("form-tpa-answerTpaHere-title"), MsgMgr::getMsg("form-tpa-answerTpaHere-content", ["{source}" => $tpa->getSource()]));
            $form->setAcceptText(MsgMgr::getMsg("form-tpa-answerTpaHere-acceptText"));
            $form->setDenyText(MsgMgr::getMsg("form-tpa-answerTpaHere-denyText"));
        }

        $form->setAcceptListener(
            function (Player $player) use ($tpa): void {
                $source = $tpa->getSource();
                $sourcePlayer = $tpa->getSourceAsPlayer();
                if (!$sourcePlayer instanceof Player) {
                    $player->sendMessage(str_replace("{target}", $source, theSpawn::$prefix . MsgMgr::getMsg("player-not-found")));
                    return;
                }
                $tpa->complete();
                $sourcePlayer->sendMessage(str_replace("{target}", $player->getName(), theSpawn::$prefix . MsgMgr::getMsg("tpa-accepted-source")));
                $replace = ["{target}" => $player->getName()];
                $sourcePlayer->sendToastNotification(MsgMgr::getMsg("tn-tpa-accepted-target-title", $replace), MsgMgr::getMsg("tn-tpa-accepted-target-body", $replace));
                $player->sendMessage(str_replace("{source}", $source, theSpawn::$prefix . MsgMgr::getMsg("tpa-accepted-target")));
            }
        );

        $form->setDenyListener(
            function (Player $player) use ($tpa): void {
                $ev = new TpaAnswerEvent($tpa, false);
                $ev->call();
                if ($ev->isCancelled()) return;

                $source = $tpa->getSource();
                $sourcePlayer = $tpa->getSourceAsPlayer();
                
                if (!$sourcePlayer instanceof Player) {
                    $player->sendMessage(str_replace("{target}", $source, theSpawn::$prefix . MsgMgr::getMsg("player-not-found")));
                    return;
                }
        
                $ev = new TpaAnswerEvent($tpa, false);
                $ev->call();
                if ($ev->isCancelled()) return;

                $tpa->cancel();
                $sourcePlayer->sendMessage(str_replace("{target}", $player->getName(), theSpawn::$prefix . MsgMgr::getMsg("tpa-declined-source")));
                $replace = ["{target}" => $player->getName()];
                $sourcePlayer->sendToastNotification(MsgMgr::getMsg("tn-tpa-declined-target-title", $replace), MsgMgr::getMsg("tn-tpa-declined-target-body", $replace));
                $player->sendMessage(str_replace("{source}", $source, theSpawn::$prefix . MsgMgr::getMsg("tpa-declined-target")));
            }
        );

        return $form;
    }

    public static function sendTpa(): CustomForm
    {
        $form = new CustomForm(MsgMgr::getMsg("form-tpa-sendTpa-title"),
        function (Player $player, FormResponse $response): void {
            if (($name = $response->getInputSubmittedText("name")) !== null && $name !== "") {
                if ($response->getToggleSubmittedChoice("tpaHere"))
                    theSpawn::getInstance()->getCommand("tpahere")?->execute($player, "tpahere", [$name]);
                else
                    theSpawn::getInstance()->getCommand("tpa")?->execute($player, "tpa", [$name]);
            }
            else
                $player->sendForm(self::sendTpa());
        });

        $form->addElement("name", new Input(MsgMgr::getMsg("form-tpa-sendTpa-input")));
        $form->addElement("tpaHere", new Toggle(MsgMgr::getMsg("form-tpa-sendTpa-tpaHereToggle")));

        return $form;
    }
}