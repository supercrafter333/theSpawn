<?php

namespace supercrafter333\theSpawn;

use pocketmine\utils\Config;

class MsgMgr
{

    private static $msgs;
    private static $Me;

    public function __construct()
    {
        self::$msgs = new Config(theSpawn::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
        self::$Me = $this;
    }

    public static function getMsgs(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
    }

    public static function getMsg(string $message): string
    {
        if (self::getMsgs()->exists($message)) {
            return self::$msgs->get($message);
        } else {
            return "ERROR";
        }
    }

    public static function getNoPermMsg(): string
    {
        return self::$msgs->get("no-perms");
    }

    public static function getOnlyIGMsg(): string
    {
        return self::$msgs->get("only-In-Game");
    }

    public static function getMe(): self
    {
        return self::$Me;
    }

    public function getMsgs(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
    }

    public function getMsg(string $message)
    {
        return self::$msgs->get($message);
    }
}