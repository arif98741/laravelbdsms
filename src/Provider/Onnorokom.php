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

namespace Xenon\LaravelBDSms\Provider;


use SoapClient;
use Xenon\LaravelBDSms\Handler\RenderException;
use Xenon\LaravelBDSms\Sender;

/**
 * Class Onnorokom
 * @package Xenon\LaravelBDSmsLog\Provider
 */
class Onnorokom extends AbstractProvider
{
    private string $apiEndpoint = "https://api2.onnorokomsms.com/sendsms.asmx?wsdl";

    /**
     * Onnorokom constructor.
     * @param Sender $sender
     */
    public function __construct(Sender $sender)
    {
        $this->senderObject = $sender;
    }

    /**
     * Send Request To Server
     * @throws RenderException|\SoapFault
     */
    public function sendRequest()
    {
        $data = [
            'number' => $this->senderObject->getMobile(),
            'message' => $this->senderObject->getMessage()
        ];

        if (!extension_loaded('soap')) { //check if soap extension is enabled for onnorokom provider
            throw new RenderException("Soap extension is not enabled in your server. Please install/enable it before using onnorokom sms client");
        }

        $soapClient = new SoapClient($this->apiEndpoint);
        $config = $this->senderObject->getConfig();
        $mobile = $this->senderObject->getMobile();
        $message = $this->senderObject->getMessage();
        $paramArray = array(
            'userName' => $config['userName'],
            'userPassword' => $config['userPassword'],
            'type' => $config['type'],
            'maskName' => $config['maskName'],
            'campaignName' => $config['campaignName'],
            'mobileNumber' => $mobile,
            'smsText' => $message,
        );
        $smsResult = $soapClient->__call("OneToOne", array($paramArray));

        return $this->generateReport($smsResult, $data);
    }

    /**
     * @throws RenderException
     */
    public function errorException()
    {
        if (!extension_loaded('soap')) {
            throw new RenderException('Soap client is not installed or loaded');
        }

        if (!array_key_exists('userName', $this->senderObject->getConfig())) {
            throw new RenderException('userName key is absent in configuration');
        }

        if (!array_key_exists('userPassword', $this->senderObject->getConfig())) {
            throw new RenderException('userPassword key is absent in configuration');
        }

        if (!array_key_exists('type', $this->senderObject->getConfig())) {
            throw new RenderException('type key is absent in configuration');
        }

        if (!array_key_exists('maskName', $this->senderObject->getConfig())) {
            throw new RenderException('maskName key is absent in configuration');
        }

        if (!array_key_exists('campaignName', $this->senderObject->getConfig())) {
            throw new RenderException('campaignName key is absent in configuration');
        }

    }

}
