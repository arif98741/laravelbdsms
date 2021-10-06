<?php namespace Xenon\LaravelBDSms;

use Exception;

class SMS
{
    /** @var Sender */
    private static $sender;


    public function __construct(Sender $sender)
    {
        self::$sender = $sender;
    }

    /**
     * @throws Handler\RenderException
     * @throws \Exception
     */
    public function via($provider): SMS
    {
        $this->sender->setProvider($provider);
        $this->sender->setConfig(config('sms.providers')[$provider]);
        return $this;
    }

    /**
     * @throws Handler\ParameterException
     * @throws Handler\ValidationException
     * @throws Exception
     */
    public static function shoot(string $number, string $text)
    {
        /*$config = Config::get('sms');


        self::$sender = new Sender();
        self::$sender->setMobile($number);
        self::$sender->setMessage($text);
        return self::$sender->send();*/
        self::$sender->setMobile($number);
        self::$sender->setMessage($text);
        return self::$sender->send();
    }
}
