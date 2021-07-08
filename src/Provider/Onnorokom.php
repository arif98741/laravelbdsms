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
use Xenon\Sender;

class Onnorokom extends AbstractProvider
{
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
     */
    public function sendRequest()
    {
        $data = [
            'number' => $this->senderObject->getMobile(),
            'message' => $this->senderObject->getMessage()
        ];

        $soapClient = new SoapClient("https://api2.onnorokomsms.com/sendsms.asmx?wsdl");
        $config = $this->senderObject->getConfig();
        $mobile = $this->senderObject->getMobile();
        $message = $this->senderObject->getMessage();
        $paramArray = array(
            'userName' => $config['userName'],
            'userPassword' => $config['userPassword'],
            'mobileNumber' => $mobile,
            'smsText' => $message,
            'type' => $config['type'],
            'maskName' => $config['maskName'],
            'campaignName' => $config['campaignName']
        );
        $smsResult = $soapClient->__call("OneToOne", array($paramArray));
        return $this->generateReport($smsResult, $data);
    }

    /**
     * @throws RenderException
     */
    public function errorException()
    {
        if (!extension_loaded('soap'))
            throw new RenderException('Soap client is not installed or loaded');

        if (!is_array($this->senderObject->getConfig()))
            throw new RenderException('Configuration is not provided. Use setConfig() in method chain');

        if (!array_key_exists('userName', $this->senderObject->getConfig()))
            throw new RenderException('userName key is absent in configuration');

        if (!array_key_exists('userPassword', $this->senderObject->getConfig()))
            throw new RenderException('userPassword key is absent in configuration');

        if (!array_key_exists('type', $this->senderObject->getConfig()))
            throw new RenderException('type key is absent in configuration');

        if (!array_key_exists('maskName', $this->senderObject->getConfig()))
            throw new RenderException('maskName key is absent in configuration');

        if (!array_key_exists('campaignName', $this->senderObject->getConfig()))
            throw new RenderException('campaignName key is absent in configuration');


        if (strlen($this->senderObject->getMobile()) > 11 || strlen($this->senderObject->getMobile()) < 11) {
            throw new RenderException('Invalid mobile number. It should be 11 digit');
        }
        if (empty($this->senderObject->getMessage())) {
            throw new RenderException('Message should not be empty');
        }
    }

    /**
     * @param $result
     * @param $data
     * @return mixed
     */
    public function generateReport($result, $data): array
    {
        return [
            'status' => 'response',
            'response' => $result,
            'provider' => self::class,
            'send_time' => date('Y-m-d H:i:s'),
            'mobile' => $data['number'],
            'message' => $data['message']
        ];
    }
}
