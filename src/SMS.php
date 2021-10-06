<?php namespace Xenon\LaravelBDSms;
use Exception;
class SMS
{
    /** @var Sender */
    private $sender;

    public function __construct(Sender $sender)
    {
        $this->sender = $sender;
    }

    /**
	@@ -29,17 +28,10 @@ public function via($provider): SMS
     * @throws Handler\ValidationException
     * @throws Exception
     */
    public function shoot(string $number, string $text)
    {
        $this->sender->setMobile($number);
        $this->sender->setMessage($text);
        return $this->sender->send();
    }
}