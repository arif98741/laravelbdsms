<?php
/*
 *  Last Modified: 6/28/21, 11:18 PM
 *  Copyright (c) 2021
 *  -created by Ariful Islam
 *  -All Rights Preserved By
 *  -If you have any query then knock me at
 *  arif98741@gmail.com
 *  See my profile @ https://github.com/arif98741
 */

namespace Xenon\LaravelBDSms;


use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;use JsonException;use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Helper\Helper;
use Xenon\LaravelBDSms\Log\LogDispatcher;
use Xenon\LaravelBDSms\Provider\AbstractProvider;
use Xenon\LaravelBDSms\Provider\CustomGateway;

class Sender
{
    /**
     * @var AbstractProvider
     */
    private $provider;
    /**
     * @var
     */
    private $message;
    /**
     * @var
     */
    private $mobile;
    /**
     * @var
     */
    private $config;

    /**
     * @var string
     */
    public string $url;
    /**
     * @var
     */
    public $method;

    public $tries = 3;

    public $backoff = 60;

    /**
     * @var bool
     */
    private bool $queue = false;

    /**
     * Verdict of the most recent send: true accepted, false rejected, null when
     * the provider cannot report one. Reset by every send(), because this class
     * is a singleton and a stale verdict would be read as the new send's.
     *
     * @var bool|null
     */
    private ?bool $acceptance = null;


    /**
     * @var Sender|null
     */
    private static $instance = null;


    /**
     * @var string
     */
    private $queueName = 'default';


    /*
    |------------------------------------------------------------------------------------------
    | Instance of Sender class
    |------------------------------------------------------------------------------------------
    | This is the static method that controls the access to the singleton instance. On the first run,
    | it creates a singleton object and places it into the static field.
    | On subsequent runs, it returns the client existing object stored in the static field. This implementation
    | lets you subclass the Singleton class while keeping just one instance of each subclass around.
    */
    private array $headers = [];
    private bool $contentTypeJson = false;

    /**
     * @throws RenderException
     */
    public static function getInstance(): Sender
    {
        if (!File::exists(config_path('sms.php'))) {
            throw new RenderException("missing config/sms.php. Be sure to run
            'php artisan vendor:publish --provider=Xenon\LaravelBDSms\LaravelBDSmsServiceProvider'
             and also set provider using setProvider() method. Set default provider from config/sms.php if
             you use Xenon\LaravelBDSms\Facades\SMS::shoot() facade. You can also clear your cache");
        }

        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return int
     */
    public function getTries()
    {
        return $this->tries;
    }

    /**
     * @param int $tries
     * @return $this
     */
    public function setTries(int $tries)
    {
        $this->tries = $tries;
        return $this;
    }

    /**
     * @return int
     */
    public function getBackoff()
    {
        return $this->backoff;
    }

    /**
     * @param int $backoff
     * @return $this
     */
    public function setBackoff(int $backoff)
    {
        $this->backoff = $backoff;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     * @return Sender
     * @throws Exception
     * @since v1.0.0
     */
    public function setConfig($config): Sender
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param bool $queue
     * @return Sender
     * @since v1.0.41.6-dev
     */
    public function setQueue(bool $queue): Sender
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @param string $queueName
     * @return $this
     */
    public function setQueueName(string $queueName): Sender
    {
        $this->queueName = $queueName;
        return $this;
    }

    /**
     * @return bool
     * @since v1.0.41.6-dev
     */
    public function getQueue()
    {
        return $this->queue;

    }

    /**
     * @param array $headers
     * @param bool $contentTypeJson
     * @return Sender
     * @throws RenderException
     * @since v1.0.55.0-beta
     */
    public function setHeaders(array $headers, bool $contentTypeJson = true): Sender
    {
        $this->headers = self::normalizeHeaders($headers);
        $this->contentTypeJson = $contentTypeJson;
        return $this;
    }

    /**
     * Guzzle needs name => value pairs. Accept the curl style 'Name: value'
     * lines as well, since they are what the readme has always shown.
     *
     * @param array $headers
     * @return array
     */
    private static function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            if (is_int($name) && is_string($value) && str_contains($value, ':')) {
                [$name, $value] = explode(':', $value, 2);
            }
            $normalized[trim((string)$name)] = is_string($value) ? trim($value) : $value;
        }

        return $normalized;
    }

    /**
     * @return array
     * @since v1.0.55.0-beta
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return bool
     * @since v1.0.55.0-beta
     */
    public function isContentTypeJson(): bool
    {
        return $this->contentTypeJson;
    }

    /**
     * Send Message Finally
     * @throws ParameterException
     * @since v1.0.5
     */
    public function send()
    {
        //first thing, before any guard below can throw: this class is a singleton,
        //so a verdict left over from the previous send would otherwise be read as
        //this one's by a caller that checks getAcceptance() after catching
        $this->acceptance = null;

        if (!is_array($this->getConfig())) {
            throw  new ParameterException('config must be an array');
        }

        if (!$this->provider instanceof CustomGateway) { //empty check for all providers mobile and message
            if (empty($this->getMobile())) {
                throw new ParameterException('Mobile number should not be empty');
            }

            if (empty($this->getMessage())) {
                throw new ParameterException('Message text should not be empty');
            }
        }

        $this->provider->errorException();

        $config = Config::get('sms');

        $response = $this->provider->sendRequest();
        if (!$this->getQueue()) {
            $this->logGenerate($config, $response);
        }

        return $response;
    }

    /**
     * Whether the gateway accepted the message just sent.
     *
     * Null means no verdict is available — the provider does not implement
     * AbstractProvider::accepted(), or the send was queued and there is no
     * response yet. Treat null as 'unknown' and not as failure.
     *
     * @return bool|null
     * @since v2.0.1.0-beta
     */
    public function getAcceptance(): ?bool
    {
        return $this->acceptance;
    }

    /**
     * Recorded by AbstractProvider::respond() as it reads the response body.
     *
     * @param bool|null $acceptance
     * @return void
     * @since v2.0.1.0-beta
     */
    public function setAcceptance(?bool $acceptance): void
    {
        $this->acceptance = $acceptance;
    }

    /**
     * @return mixed
     * @since v1.0.0
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     * @return Sender
     * @throws RenderException
     * @since v1.0.0
     */
    public function setMobile($mobile): Sender
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return mixed
     * @since v1.0.0
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     * @return Sender
     * @throws RenderException
     * @since v1.0.0
     */
    public function setMessage($message = ''): Sender
    {

        $this->message = $message;
        return $this;
    }

    /**
     * @param string $url
     * @return $this
     * @throws RenderException
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     * @since v1.0.0
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Return this class object
     * @param $providerClass
     * @return Sender
     * @throws RenderException
     * @since v1.0.0
     */
    public function setProvider($providerClass): Sender
    {
        try {

            $providerClass = Helper::ensurePrefix($providerClass);

            if (!class_exists($providerClass)) {
                throw new RenderException("Sms Gateway Provider '$providerClass' not found. ");
            }

            if (!is_subclass_of($providerClass, AbstractProvider::class)) {
                throw new RenderException("Provider '$providerClass' is not a " . AbstractProvider::class);
            }
        } catch (RenderException $exception) {

            throw new RenderException($exception->getMessage());
        }

        $this->provider = new $providerClass($this);
        return $this;
    }

    /**
     * @param $config
     * @param $response
     * @return void
     * @throws JsonException
     * @throws RenderException
     */
    private function logGenerate($config, $response): void
    {

        if ($config['sms_log']) {

            if (is_object($response)) {
                $object = json_decode($response->getContent());
            } else {
                $object = json_decode($response);
            }

            $providerResponse = $object->response;

            $providerClass = get_class($this->provider);
            $requestData = [
                'config' => $config['providers'][$providerClass],
                'mobile' => $this->getMobile(),
                'message' => $this->getMessage()
            ];

            $logData = [
                'provider' => $providerClass,
                'request_json' => json_encode($requestData, JSON_THROW_ON_ERROR),
                'response_json' => json_encode($providerResponse, JSON_THROW_ON_ERROR)
            ];

            $fileData = $logData;
            $fileData['request_json'] = $requestData;
            $fileData['response_json'] = $providerResponse;

            LogDispatcher::dispatch($logData, $fileData);

        }
    }

    /**
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

}
