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
use Xenon\LaravelBDSms\Facades\Logger;
use Xenon\LaravelBDSms\Handler\ParameterException;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Handler\ValidationException;
use Xenon\LaravelBDSms\Helper\Helper;
use Xenon\LaravelBDSms\Provider\AbstractProvider;

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
     * @var
     */
    private $method;

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
    public function setMethod($method): void
    {
        $this->method = $method;
    }

    /**
     * @var null
     */
    private static $instance = null;


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
     * @since v1.0.0
     */
    public function setConfig($config): Sender
    {
        $this->config = $config;
        return $this;
    }


    /**
     * Send Message Finally
     * @throws ParameterException
     * @throws ValidationException
     * @since v1.0.5
     */
    public function send()
    {
        if (!is_array($this->getConfig())) {
            throw  new ParameterException('config must be an array');
        }

        if (Helper::numberValidation($this->getMobile()) == false) {
            throw new ValidationException('Invalid Mobile Number');
        }
        if (strlen($this->getMobile()) > 11 || strlen($this->getMobile()) < 11) {
            throw new ParameterException('Invalid mobile number. It should be 11 digit');
        }
        if (empty($this->getMessage()))
            throw new ParameterException('Message should not be empty');

        $this->provider->errorException();
        return $this->provider->sendRequest();
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
     * @return mixed
     * @since v1.0.0
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Return this class object
     * @param $ProviderClass
     * @return Sender
     * @throws RenderException
     * @since v1.0.0
     */
    public function setProvider($ProviderClass): Sender
    {
        try {
            if (!class_exists($ProviderClass)) {
                throw new RenderException("Provider '$ProviderClass' not found");
            }

            if (!is_subclass_of($ProviderClass, AbstractProvider::class)) {
                throw new RenderException("Provider '$ProviderClass' is not a " . AbstractProvider::class);
            }
        } catch (XenonException $exception) {

            $exception->showException($ProviderClass);
        }

        $this->provider = new $ProviderClass($this);
        return $this;
    }

}
