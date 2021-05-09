<?php

namespace supercrafter333\theSpawn;

use pocketmine\utils\Config;

/**
 * Class MsgMgr
 * @package supercrafter333\theSpawn
 */
class MsgMgr
{

    /**
     * @var MsgMgr
     */
    private static $Me;

    /**
     * MsgMgr constructor.
     */
    public function __construct()
    {
        self::$Me = $this;
    }

    /**
     * @return Config
     */
    public static function getMsgs(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
    }

    /**
     * @param string $version
     * @return bool
     */
    public function checkMsgCfgVersionX(string $version): bool
    {
        if ($this->getMsgsX()->exists("version")) {
            if ($this->getMsgsX()->get("version") == $version) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     */
    public function updateMsgCfgX()
    {
        unlink(theSpawn::getInstance()->getDataFolder() . "messages.yml");
        theSpawn::getInstance()->saveResource("messages.yml");
    }

    /**
     * @param string $version
     * @return bool
     */
    public static function checkMsgCfgVersion(string $version): bool
    {
        if (self::getMsgs()->exists("version")) {
            if (self::getMsgs()->get("version") == $version) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public static function updateMsgCfg()
    {
        unlink(theSpawn::getInstance()->getDataFolder() . "messages.yml");
        return theSpawn::getInstance()->saveResource("messages.yml");
    }

    /**
     * @param string $message
     * @return string
     */
    public static function getMsg(string $message): string
    {
        if (self::getMsgs()->exists($message)) {
            return self::getMsgs()->get($message);
        } else {
            return "ERROR";
        }
    }

    /**
     * @return string
     */
    public static function getNoPermMsg(): string
    {
        return self::getMsgs()->get("no-perms");
    }

    /**
     * @return string
     */
    public static function getOnlyIGMsg(): string
    {
        return self::getMsgs()->get("only-In-Game");
    }

    /**
     * @return string
     */
    public static function getErrorMsg(): string
    {
        return self::getMsg("something-went-wrong");
    }

    /**
     * @return string
     */
    public static function getPrefix(): string
    {
        return self::getMsg("prefix");
    }

    /**
     * @return static
     */
    public static function getMe()
    {
        return self::$Me;
    }

    /**
     * @return Config
     */
    public function getMsgsX(): Config
    {
        return new Config(theSpawn::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
    }

    /**
     * @return string
     */
    public function getErrorMgsX(): string
    {
        return $this->getMsgsX()->get("something-went-wrong");
    }

    /**
     * @param string $message
     * @return bool|mixed
     */
    public function getMsgX(string $message)
    {
        return self::getMsgs()->get($message);
    }
}