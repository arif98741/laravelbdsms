<?php namespace Xenon\LaravelBDSms;

use Exception;
use Xenon\LaravelBDSms\Helper\Helper;

class SMS
{
    /**
     * Queue settings a non-queued send falls back to. They are re-applied on every
     * shoot() so settings left behind by an earlier shootWithQueue() cannot leak in.
     */
    private const DEFAULT_QUEUE_NAME = 'default';
    private const DEFAULT_TRIES = 3;
    private const DEFAULT_BACKOFF = 60;

    /** @var Sender */
    private $sender;

    /**
     * Provider this instance sends through. Re-applied to the sender before every
     * send, because the sender is shared and may have been pointed elsewhere.
     * @var string|null
     */
    private $providerClass;

    /**
     * Configuration belonging to $providerClass
     * @var mixed
     */
    private $providerConfig;

    /**
     * @param Sender $sender
     * @version v1.0.32
     * @since v1.0.31
     */
    public function __construct(Sender $sender)
    {
        $this->sender = $sender;

        $provider = $sender->getProvider();
        if ($provider !== null) {
            $this->providerClass = get_class($provider);
            $this->providerConfig = $sender->getConfig();
        }
    }

    /**
     * Select a provider for the returned instance. A clone is returned so that
     * SMS::via() never repoints the facade instance used by a bare SMS::shoot().
     *
     * @throws Handler\RenderException
     * @throws Exception
     * @version v1.0.32
     * @since v1.0.31
     */
    public function via($provider): SMS
    {
        $providerClass = Helper::ensurePrefix($provider);

        $instance = clone $this;
        $instance->providerClass = $providerClass;
        $instance->providerConfig = config('sms.providers')[$providerClass] ?? null;
        $instance->applyProvider();

        return $instance;
    }

    /**
     * @throws Handler\ParameterException
     * @throws Exception
     * @version v1.0.32
     * @since v1.0.31
     */
    public function shoot(string $number, string $text)
    {
        $this->applyProvider();

        $this->sender->setQueue(false);
        $this->sender->setQueueName(self::DEFAULT_QUEUE_NAME);
        $this->sender->setTries(self::DEFAULT_TRIES);
        $this->sender->setBackoff(self::DEFAULT_BACKOFF);

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
        $this->applyProvider();

        $this->sender->setQueue(true);
        $this->sender->setQueueName($queueName);
        $this->sender->setTries($tries);
        $this->sender->setBackoff($backoff);

        $this->sender->setMobile($number);
        $this->sender->setMessage($text);

        return $this->sender->send();
    }

    /**
     * Point the shared sender back at this instance's own provider.
     *
     * @return void
     * @throws Handler\RenderException
     * @throws Exception
     */
    private function applyProvider(): void
    {
        if ($this->providerClass === null) {
            //sender was configured by the caller; leave it as it is
            return;
        }

        $this->sender->setProvider($this->providerClass);
        $this->sender->setConfig($this->providerConfig);
    }
}
