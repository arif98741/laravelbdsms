<?php


namespace Xenon\LaravelBDSms\Provider;


use SoapClient;
use Xenon\Handler\XenonException;
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

    /**
     * @throws XenonException
     */
    public function errorException()
    {
        if (!extension_loaded('soap'))
            throw new XenonException('Soap client is not installed or loaded');

        if (!is_array($this->senderObject->getConfig()))
            throw new XenonException('Configuration is not provided. Use setConfig() in method chain');

        if (!array_key_exists('userName', $this->senderObject->getConfig()))
            throw new XenonException('userName key is absent in configuration');

        if (!array_key_exists('userPassword', $this->senderObject->getConfig()))
            throw new XenonException('userPassword key is absent in configuration');

        if (!array_key_exists('type', $this->senderObject->getConfig()))
            throw new XenonException('type key is absent in configuration');

        if (!array_key_exists('maskName', $this->senderObject->getConfig()))
            throw new XenonException('maskName key is absent in configuration');

        if (!array_key_exists('campaignName', $this->senderObject->getConfig()))
            throw new XenonException('campaignName key is absent in configuration');


        if (strlen($this->senderObject->getMobile()) > 11 || strlen($this->senderObject->getMobile()) < 11) {
            throw new XenonException('Invalid mobile number. It should be 11 digit');
        }
        if (empty($this->senderObject->getMessage())) {
            throw new XenonException('Message should not be empty');
        }
    }
}
