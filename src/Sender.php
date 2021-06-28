<?php


namespace Xenon\LaravelBDSms;


use Exception;
use Xenon\LaravelBDSms\Handler\XenonException;

class Sender
{
    private $provider;
    private $message;
    private $mobile;
    private $config;
    private static $instance = null;

    /**
     * The Singleton's constructor should always be private to prevent direct
     * construction calls with the `new` operator.
     */
    protected function __construct()
    {
    }

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
        $cls = static::class;
        if (!isset(self::$instance)) {
            self::$instance = new Sender();
        }

        return self::$instance;
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
     */
    public function setConfig($config): Sender
    {
        try {
            if (!is_array($config)) {
                throw  new XenonException('config must be an array');
            }
            $this->config = $config;
        } catch (XenonException $e) {
            $e->showException();
        }

        return $this;
    }


    /**
     * Send Message Finally
     */
    public function send()
    {
        try {
            $this->provider->errorException();
            return $this->provider->sendRequest();
        } catch (XenonException $exception) {
            $exception->showException();
        }
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     * @return Sender
     */
    public function setMobile($mobile): Sender
    {
        $this->mobile = $mobile;
        return self::getInstance();
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     * @return Sender
     */
    public function setMessage($message = ''): Sender
    {

        $this->message = $message;
        return self::getInstance();
    }

    /**
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Return this class object
     * @param $ProviderClass
     * @return Sender
     */
    public function selectProvider($ProviderClass): Sender
    {
        try {
            if (!class_exists($ProviderClass)) {
                throw new XenonException('Provider ' . $ProviderClass . ' not found');
            }
        } catch (XenonException $exception) {

            $exception->showException($ProviderClass);
        }

        $this->provider = new $ProviderClass($this);
        return $this;
    }

}
