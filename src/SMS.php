<?php namespace Xenon\LaravelBDSms;

use Exception;

class SMS
{
    /** @var Sender */
    private $sender;


    /**
     * @param Sender $sender
     * @version v1.0.32
     * @since v1.0.31
     */
    public function __construct(Sender $sender)
    {
        $this->sender = $sender;
    }

    /**
     * @throws Handler\RenderException
     * @throws Exception
     * @version v1.0.32
     * @since v1.0.31
     */
    public function via($provider): SMS
    {
        $this->sender->setProvider($provider);
        $this->sender->setConfig(config('sms.providers')[$provider]);
        return $this;
    }

    /**
     * @throws Handler\ParameterException
     * @throws Exception
     * @version v1.0.32
     * @since v1.0.31
     */
    public function shoot(string $number, string $text)
    {
        $this->sender->setMobile($number);
        $this->sender->setMessage($text);
        return $this->sender->send();
    }

    /**
     * @throws Handler\ParameterException
     * @throws Exception
     * @version v1.0.46-dev
     * @since v1.0.46-dev
     */
    public function shootWithQueue(string $number, string $text, string $queueName = 'default', int $tries = 3, int $backoff = 60)
    {
        $this->sender->setMobile($number);
        $this->sender->setMessage($text);
        $this->sender->setQueue(true);
        $this->sender->setQueueName($queueName);

        if (isset($tries)) {
            $this->sender->setTries($tries);
        }
        if (isset($backoff)) {
            $this->sender->setBackoff($backoff);
        }
        return $this->sender->send();
    }
}
