<?php namespace Xenon\LaravelBDSms;

use Exception;

class SMS
{
    /** @var Sender */
    private static $sender;

    /**
     * @param Sender $sender
     * @version v1.0.32
     * @since v1.0.31
     */
    public function __construct(Sender $sender)
    {
        self::$sender = $sender;
    }

    /**
     * @throws Handler\RenderException
     * @throws Exception
     * @since v1.0.31
     * @version v1.0.32
     **/
    public static function via($provider): SMS
    {
        self::$sender->setProvider($provider);
        self::$sender->setConfig(config('sms.providers')[$provider]);
        return self::class;
    }

    /**
     * @throws Handler\ParameterException
     * @throws Handler\ValidationException
     * @throws Exception
     * @version v1.0.32
     * @since v1.0.31
     */
    public static function shoot(string $number, string $text)
    {
        self::$sender->setMobile($number);
        self::$sender->setMessage($text);
        return self::$sender->send();
    }
}
