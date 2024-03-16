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
use Xenon\LaravelBDSms\Facades\Logger;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
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

    public $tries=3;

    public $backoff=60;

    /**
     * @var bool
     */
    private bool $queue = false;


    /**
     * @var Sender|null
     */
    private static $instance = null;


    /**
     * @var string
     */
    private $queueName='default';


    /**
     * This is the static method that controls the access to the singleton
     * instance. On the first run, it creates a singleton object and places it
     * into the static field. On subsequent runs, it returns the client existing
     * object stored in the static field.
     *
     * This implementation lets you subclass the Singleton class while keeping
     * just one instance of each subclass around.
     */
    public static function getInstance(): Sender
    {
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
        return self::$instance;
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
     * @param int $tries
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
     * @return Sender
     * @since v1.0.55.0-beta
     */
    public function setHeaders(array $headers,bool $contentTypeJson = true): Sender
    {
        $this->headers = $headers;
        $this->contentTypeJson = $contentTypeJson;
        return self::getInstance();
    }

    /**
     * Send Message Finally
     * @throws ParameterException
     * @throws \JsonException
     * @since v1.0.5
     */
    public function send()
    {

        if (!is_array($this->getConfig())) {
            throw  new ParameterException('config must be an array');
        }

        if(!$this->provider instanceof CustomGateway){ //empty check for all providers mobile and message
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
     * @since v1.0.0
     */
    public function setMobile($mobile): Sender
    {
        $this->mobile = $mobile;
        return self::getInstance();
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
     * @since v1.0.0
     */
    public function setMessage($message = ''): Sender
    {

        $this->message = $message;
        return self::getInstance();
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
        return self::getInstance();
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

            if ($providerClass === null) {
                throw new RenderException("Provider is empty. Be sure to run 'php artisan vendor:publish --provider=Xenon\LaravelBDSms\LaravelBDSmsServiceProvider' and also set provider using setProvider() method. Set default provider from config/sms.php if you use Xenon\LaravelBDSms\Facades\SMS::shoot() facade");
            }

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
     * @throws \JsonException
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

            Logger::createLog([
                'provider' => get_class($this->provider),
                'request_json' => json_encode([
                    'config' => $config['providers'][get_class($this->provider)],
                    'mobile' => $this->getMobile(),
                    'message' => $this->getMessage()
                ], JSON_THROW_ON_ERROR),
                'response_json' => json_encode($providerResponse, JSON_THROW_ON_ERROR)
            ]);
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
