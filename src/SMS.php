<?php namespace Xenon\LaravelBDSms;

class SMS
{
    /** @var Sender */
    private $sender;

    public function __construct(Sender $sender)
    {
        $this->sender = $sender;
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
     */
    public function shoot(string $number, string $text)
    {
        $this->sender->setMobile($number);
        $this->sender->setMessage($text);
        return $this->sender->send();
    }
}
